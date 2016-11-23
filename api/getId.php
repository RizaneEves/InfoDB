<?php
	$root = $_SERVER['DOCUMENT_ROOT'];
	include $root."/internal/commons.php";

	$type = ifnset("type", "");
	$name = ifnset("name", "");

	if(!$type || !$name){
		returnStatus(401);
	}

	$db = new DBAccess();
	$query = new DBQuery($db);

	switch($type){
		case "person":
			$query->group = new DBGroup([
				new DBSelect(["id", "concat(c.firstname, ' ', c.lastname) name"]),
				new DBFrom(["Celebrity c"]),
				new DBWhere(["concat(c.firstname, ' ', c.lastname) like ?"] , [[ "%".$name."%", "s" ]]),
				new DBOrder(["c.ranking desc"]),
				new DBLimit(5)
			]);
			break;
		default:
			returnStatus(401);
	}

	$result = $query->query();
	$json = [];
	foreach($result as $row){
		$json[] = [
			"id" => $row[0],
			"name" => $row[1]
		];
	}

	header('Content-Type: application/json');
	echo json_encode($json);
?>
