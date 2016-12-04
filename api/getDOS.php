<?php
	$root = $_SERVER['DOCUMENT_ROOT'];
	include $root."/internal/commons.php";
	
	function degreesOfSeparation($id1, $id2, $degree) {
		$db  = new DBAccess();
		$sql = "CALL degsep($id1, $id2, $degree)";
		$found = "";
		$result = $db->query($sql, null);
	
		$sql = "SELECT * FROM CelebOutputs";
		
		$celebrities = $db->query($sql, null);

		$sql = "SELECT * FROM MovieOutputs";

		$cinematographies = $db->query($sql, null);

		
		if(!$celebrities)
		{
			$found = false;
		}
		else
		{
			$found = true;
		}
		
	
		$deg = ["found" => $found, "celebrities" => [], "cinematographies" => []];
		foreach($celebrities as $row) {
			$deg["celebrities"][] = ["id" => $row[0], "firstName"=>$row[1], "lastName"=>$row[2]];
		}
		foreach($cinematographies as $row) {
			$deg["cinematographies"][] = ["id" => $row[0], "title" => $row[1], "productionYear" => $row[2], "type" => $row[3]];
		}

		return json_encode($deg);

	}

	// Return HTTP 400 if parameter not set
	if(!isset($_GET["id1"]) || !isset($_GET["id2"])){
		returnStatus(400);
	}

	function shutdownHandler(){
		$error = error_get_last();
		if($error['type'] === E_ERROR) { 
			http_response_code(500);
		}
	}
	register_shutdown_function("shutdownHandler");		

	// Return result in JSON
	$degree = 2;
	$json = degreesOfSeparation((int) $_GET["id1"], (int) $_GET["id2"], 2);
	header('Content-Type: application/json');
	echo $json;
?>

