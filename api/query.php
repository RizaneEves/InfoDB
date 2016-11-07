<?php
	const BAD_REQUEST = 400;
	const INTERNAL_SERVER_ERROR = 500;

	$mysqli = new mysqli("127.0.0.1", "root", "cs411fa2016", "all_imdb_data");

	if(mysqli_connect_errno()){
		http_response_code(INTERNAL_SERVER_ERROR);
		echo "Cannot connect to MySQL: " . mysqli_connect_error() . PHP_EOL;
		exit;
	}

	$mode = $_SERVER['REQUEST_METHOD'];

	function fetchData($mysqli, $statement){
		// prepare SQL statement (unsafe but who cares)
		$execTime = microtime(true);
		$result = $mysqli->query($statement);
		$execTime = microtime(true) - $execTime;
		
		$rtn = array("data" => array(), "time" => $execTime);
		$data =& $rtn["data"];

		if($result){
			while($row = $result->fetch_row()){
				$data[] = $row;
			}
			$result->close();

			return $rtn;
		}else{
			return null;
		}
	}

	function noQuery(){
		$status = "Enter query:";
	}

	function getQuery($mysqli){
		$query = $_GET["query"];
		$result = fetchData($mysqli, $_GET["query"]);
		$data = $result["data"];
		$output = "";
		if($result){
			$output = array_map(function($line){
				return implode(", ", $line);
			}, $data);
			$output = implode("\n", $output);
		}else{
			$output = "Error executing query.";
		}

		return array("query" => $query, "output" => $output, "time" => $result["time"]);
	}

	function postQuery($mysqli){
		$postData = json_decode(file_get_contents("php://input"), true);
		$query = $postData["query"];
		$result;
		if(isset($query)){
			$result = fetchData($mysqli, $query);
		}	

		$json_obj = array("status" => "Failed",
				  "data" => null,
				  "query" => $query,
				  "time" => 0);
		if($result){
			$json_obj["status"] = "OK";
			$json_obj["data"] = $result["data"];
			$json_obj["time"] = $result["time"];
		}
		
		return $json_obj;
	}

	$result;

	switch($mode){
		case "GET":
			$result = getQuery($mysqli);
			break;
		case "POST":
			$result = @postQuery($mysqli);
			if($result["status"] !== "OK"){
				http_response_code(400);
			}
			header('Content-Type: application/json');
			echo json_encode($result);
			exit;
		default:
			$result = array("query" => "Enter query...",
					"output" => "",
					"time" => 0);
			break;
	}

	$query = $result["query"];
	$output = $result["output"];
	$time = $result["time"];

	$mysqli->close(); 
?>
<html>
	<head>
		<style>
			textarea{
				width: 1000px;
				height: 100px;
			}
		</style>
	</head>
	<body>
		<form method="post" action="query.php">
			<textarea name="query"><?php echo $query ?></textarea>
			<br>
			<input type="submit">
		</form>
		<span><?php printf("Executed in %.2f seconds", $time) ?></span>
		<pre><?php echo $output ?></pre>
	</body>
</html>
