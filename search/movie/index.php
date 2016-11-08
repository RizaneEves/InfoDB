<?php
	$root = $_SERVER['DOCUMENT_ROOT'];
	include $root."/internal/commons.php";
	$activePage = "search";

	$page = ifnset("page", 1);
	$pagelen = ifnset("len", 5);

	if($page < 0){
		exit("Invalid parameters");
	}

	$productionYearComp = "=";
	switch($_GET["productionyearcomp"]){
		case "before": $productionYearComp = "<"; break;
		case "after" : $productionYearComp = ">"; break;
		default      : $productionYearComp = "="; break;
	}

	$whereClause = DBQueryPart::buildWhereClauseFromQuery([
		["c.title LIKE ?", "title", function($v){ return "%".$v."%"; }, "s"],
		["c.productionYear ".$productionYearComp." ?", "productionyear", function($v){ return $v; }, "i"],
		["c.type = ?", "type", function($v){ return $v; }, "s"]
	]);

	$db = new DBAccess();

	/*
		select c.title, i.description 
		FROM Cinematography c LEFT JOIN CinematographyInfo i ON c.id = i.cinematographyId 
		WHERE *conditions* 
		ORDER BY *order conditions*
		LIMIT ?,?
	*/
	$query = new DBQuery($db);
	$query->group = new DBGroup([
		new DBSelect(["c.id", "c.title", "c.productionYear", "i.description", "c.type"]),
		new DBFrom(["Cinematography c LEFT JOIN CinematographyInfo i ON c.id = i.cinematographyId"]),
		$whereClause,
		new DBOrder(["case when i.description IS NULL then 1 else 0 end", "ABS(YEAR(NOW()) - CAST(c.productionYear AS SIGNED)) ASC", "c.title ASC"])
	]);

	$starttime = microtime(true);
	$result = $query->queryRange($pagelen, $page);
	$endtime = microtime(true);
	$timeused = $endtime - $starttime;
	$resultLen = $db->getLastQueryCount();

	if(!$result){
		exit("Invalid query.");
	}

	$maxPage = ceil($resultLen / $pagelen);

	parse_str($_SERVER['QUERY_STRING'], $params);
	$nextparams = $prevparams = $firstparams = $lastparams = $params;
	$nextparams["page"] = $page + 1;
	$prevparams["page"] = $page - 1;
	$firstparams["page"] = 1;
	$lastparams["page"] = $maxPage;
	$nexturl = "?".http_build_query($nextparams);
	$prevurl = "?".http_build_query($prevparams);
	$firsturl = "?".http_build_query($firstparams);
	$lasturl = "?".http_build_query($lastparams);

?>
<!doctype html>
<html>
	<head>
		<title>InfoDB</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

		<link href="https://fonts.googleapis.com/css?family=Playfair+Display|Raleway:300" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.css">
		<link rel="stylesheet" href="/resources/styles.css">

		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.8/angular.min.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-animate.min.js"></script>
 		<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-aria.min.js"></script>
 		<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-messages.min.js"></script>

		<script src="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.js"></script>
		<script src="main.js"></script>
	</head>
	<body class="body container-fluid">
		<?php include($root."/templates/commons/header.php") ?>
		<div id="wrapper" class="row" ng-app="infodb" ng-controller="main">
			<div id="content" class="col-md-8">
				<div class="title">Are you talking about...</div>
				<small>Query took <?php echo round($timeused,2) ?> seconds.</small>
				<md-list>
					<?php foreach($result as $row){ ?>
					<md-list-item class="md-long-text" ng-click="displayMovie(<?php echo $row[0] ?>)"> <!-- Dangerous but no one cares -->
						<div class="md-list-item-text">
							<h3><?php echo $row[1] . " (" . $row[2].", ".$row[4] . ")" ?></h3>
							<p><?php 
								$bio = $row[3];
								$bio = strlen($bio) > 300 ? trim(substr($bio, 0, 300))."..." : $bio;
								// replace movie qv's with <em>'s
								$bio = preg_replace("/_([^_]*)_ \(qv\)/", "<em>$1</em>", $bio);
								// replace person qv's with <em>'s
								$bio = preg_replace("/'([^']*)' \(qv\)/", "<em>$1</em>", $bio);
								echo $bio ?: "No description";
							?></p>
						</div>
					</md-list-item>
					<md-divider></md-divider>
					<?php }; ?>
				</md-list>
				<nav>
					<ul class="pager">
						<li><a href="<?php echo $firsturl ?>">First</a></li>
						<?php if($page > 1){ ?>
							<li><a href="<?php echo $prevurl ?>">Previous</a></li>
						<?php }; ?>
						<li>Page <?php echo $page . " of " . $maxPage ?></li>
						<?php if($page < $maxPage){ ?>
							<li><a href="<?php echo $nexturl ?>">Next</a></li>
						<?php }; ?>
						<li><a href="<?php echo $lasturl ?>">Last</a></li>
					</ul>
				</nav>
			</div>
			<?php include($root."/templates/commons/sidebar.php") ?>
		</div>
	</body>
</html>
