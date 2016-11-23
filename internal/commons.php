<?php

/** Database wrapper */
class DBAccess {
	public $db;

	function __construct(){
		$this->db = new mysqli("127.0.0.1", "root", "cs411fa2016", "all_imdb_data");

		if(mysqli_connect_errno()){
			http_response_code(INTERNAL_SERVER_ERROR);
			echo "Cannot connect to MySQL: " . mysqli_connect_error() . PHP_EOL;
			exit;
		}
	}

	function __destruct(){
		$this->db->close();
	}

	/* Dynamically execute a prepared statement */
	function query($stmt_str, $params){
		// prepare statement
		$stmt = $this->db->prepare($stmt_str);

		if(!$stmt)	return false;

		// bind params
		if(count($params) > 0){
			$bind_types = "";
			$bind_param_refs = [];
			foreach($params as $valtype){
				// build types string
				$bind_types .= $valtype[1];
				// store $value (key) into an array
				$bind_param_refs[] = $valtype[0];
			}
			$bind_params = [$bind_types];
			for($i = 0; $i < count($bind_param_refs); $i++){
				// store reference to values
				$bind_params[] = &$bind_param_refs[$i];
			}
			call_user_func_array([$stmt, "bind_param"], $bind_params);
		}

		// execute statement
		$starttime = microtime(true);
		$stmt->execute();
		$endtime = microtime(true);
		$this->queryTime = $endtime - $starttime;

		// bind results
		$bind_result_refs = [];
		$bind_results = [];
		$meta = $stmt->result_metadata();

		if(!$meta){
			// no result
			return false;
		}

		while($field = $meta->fetch_field()){
			// fill with random stuff
			$bind_result_refs[] = $field->name;
		}
		for($i = 0; $i < count($bind_result_refs); $i++){
			// store reference to values
			$bind_results[] = &$bind_result_refs[$i];
		}
		call_user_func_array([$stmt, "bind_result"], $bind_results);
	
		// fetch output
		$output = [];
		while($stmt->fetch()){
			$row = [];
			for($i = 0; $i < count($bind_result_refs); $i++){
				$row[] = $bind_result_refs[$i];
			}
			$output[] = $row;
		}

		$stmt->close();

		return $output;
	}

	function getLastQueryCount(){
		return $this->query("select found_rows()", [])[0][0];
	}

	function getLastQueryTime(){
		return $this->queryTime;
	}

};

/** Query object for DBAccess that simplifies the process of making queries. */
class DBQuery{
	public $group;

	function __construct($db){
		$this->db = $db;
		$this->group = new DBGroup();
	}

	/** Execute the query. */
	function query(){
		$parts = $this->group->getPart();
		$query_str = $parts[0];
		$query_vars = $parts[1];

		$results = $this->db->query($query_str, $query_vars);

		return $results;
	}
	
	/** Execute the query with a range constraint. */
	function queryRange($len, $page = 1){
		$oldParts = $this->group->parts;
		$this->group->parts[] = new DBLimit($len, ($page - 1) * $len);
		
		$result = $this->query();
		$this->group->parts = $oldParts;

		return $result;
	}

};

/** Query part for DBQuery. */
class DBQueryPart{
	function __construct($prefix, $separator, $content, $vars = []){
		$this->prefix = $prefix;
		$this->separator = $separator;
		$this->content = $content;
		$this->vars = $vars;
	}
	
	/** Recursively build query string and fetch vars in order. @ symbols are treated as placeholder for DBGroup's. */
	function getPart(){
		$query_strs = [];
		$query_vars = [];

		$i = -1;
		preg_replace_callback("/[\?]/", function($match) use(&$i, &$query_vars){
			$i++;
			if($match[0] == "?"){
				$query_vars[] = $this->vars[$i];
				return "?";
			}
		}, $this->prefix);
		foreach($this->content as $content){
			$query_strs[] = preg_replace_callback("/[@\?]/", function($match) use(&$i, &$query_vars){
				$i++;
				switch($match[0]){
					// DBGroup
					case "@":
						$parts = $this->vars[$i]->getPart();
						$query_vars = array_merge($query_vars, $parts[1]);

						return "(".$parts[0].")";
					case "?":
						$query_vars[] = $this->vars[$i];
						return "?";
				}
			}, $content);
		}

		return [$this->prefix . " " . join($this->separator, $query_strs), $query_vars];
	}

	/** Fetch inputs from $_GET and build a DBWhere. Ignores empty/not set values. */
	public static function buildWhereClauseFromQuery($params){
		$queries = [];
		$vars = [];
		foreach($params as $paramDesc){
			$queryString = $paramDesc[0];
			$getName = $paramDesc[1];
			$computeValue = $paramDesc[2];
			$valueType = $paramDesc[3];

			if(!isset($_GET[$getName]) || empty($_GET[$getName])){ continue; }

			$queries[] = $queryString;
			$vars[] = [$computeValue($_GET[$getName]), $valueType];
		}
		return new DBWhere($queries, $vars);
	}
};

/* Represents a statement. */
class DBGroup extends DBQueryPart{
	function __construct($parts = [], $name = ""){
		$this->parts = $parts;
		$this->name = $name;
	}
	function getPart(){
		$query_str = "";
		$query_vars = [];
		foreach($this->parts as $part){
			$part = $part->getPart();
			$query_str .= " " . $part[0];
			$query_vars = array_merge($query_vars, $part[1]);
		}
		return [trim($query_str).$this->name, $query_vars];
	}
}

/* Represents a SELECT clause. */
class DBSelect extends DBQueryPart{
	function __construct($columns, $vars = []){
		parent::__construct("select SQL_CALC_FOUND_ROWS", ",", $columns, $vars);
	}
}

/* Represents a SELECT DISTINCT clause. */
class DBSelectDistinct extends DBQueryPart{
	function __construct($columns, $vars = []){
		parent::__construct("select SQL_CALC_FOUND_ROWS distinct", ",", $columns, $vars);
	}
}

/* Represents a FROM clause. */
class DBFrom extends DBQueryPart{
	function __construct($tables, $vars = []){
		parent::__construct("from", ",", $tables, $vars);
	}
}

/* Represents a WHERE clause. See DBQueryPart::buildWhereClauseFromQuery. */
class DBWhere extends DBQueryPart{
	function __construct($conditions, $vars = []){
		parent::__construct("where", " and ", $conditions, $vars);
	}
}

/* Represents an ORDER BY clause. */
class DBOrder extends DBQueryPart{
	function __construct($orders, $vars = []){
		parent::__construct("order by", ",", $orders, $vars);
	}
}

/* Represents a LIMIT clause. */
class DBLimit extends DBQueryPart{
	function __construct($len, $offset = 0){
		parent::__construct("limit", ",", ["?", "?"], [[$offset, "i"], [$len, "i"]]);
	}
}

/* Represents a INSERT INTO clause. */
class DBInsertValues extends DBQueryPart{
	function __construct($tableName, $columns, $values){
		parent::__construct("INSERT INTO $tableName(" . join(",", $columns) . ") VALUES(" . join(",", array_fill(0, count($values), "?")) . ")", "",
			[], $values);
	}
}


class QueryCache{
	public static function storeEntry($type, $data, $dataType){
		$typeIdLookUp = [
			"celebrity" => 1,
			"cinematography" => 2,
		];

		$db = new DBAccess();
		$query = new DBQuery($db);
		$query->group = new DBGroup([
			new DBInsertValues("QueryCache", ["CategoryId", "info"], [[$typeIdLookUp[$type], "i"], [$data, $dataType]])
		]);
		$query->query();
	}
}


/** Convenient function: Returns $defaultValue if $_GET[$getName] is not set, otherwise returns $_GET[$getName]. */
function ifnset($getName, $defaultValue){
	return isset($_GET[$getName]) ? trim($_GET[$getName]) : $defaultValue;
}

function returnStatus($s){
	header("Location: /error/?code=".$s);
	exit();
}

?>
