<?php
	$root = $_SERVER['DOCUMENT_ROOT'];
	include $root."/internal/commons.php";
	$activePage = "search";
	date_default_timezone_set('America/Chicago');

	$id = ifnset("id", 0);

	if($id === 0){
		header("Location: /");
		exit();
	}

	$whereClause = DBQueryPart::buildWhereClauseFromQuery([
		["c.id = ?", "id", function($v){ return $v; }, "i"],
	]);

	$db = new DBAccess();

	$query = new DBQuery($db);

	// fetch basic info
	$query->group = new DBGroup([
		new DBSelect(["c.id", "c.firstname", "c.lastname", "IFNULL(i.description, \"No biography.\")", "c.gender", "c.placeOfBirth", "c.dateOfBirth", "c.dateOfDeath"]),
		new DBFrom(["Celebrity c LEFT JOIN CelebrityInfo i ON c.id = i.celebrityId"]),
		$whereClause
	]);

	$basicInfo = $query->query();

	if(!$basicInfo || count($basicInfo) < 1){
		exit("Invalid query.");
	}
	$basicInfo = $basicInfo[0];	// select first row
	$basicInfo[6] = strtotime($basicInfo[6]);	// convert date to unix timestamp
	$basicInfo[7] = strtotime($basicInfo[7]);

	// fetch occupations
	$query->group = new DBGroup([
		new DBSelectDistinct(["t.description"]),
		new DBFrom(["Involving i JOIN InvolvingType t ON i.involvingTypeId = t.id"]),
		new DBWhere(["i.celebrityId = ?"], [[$id, "i"]])
	]);
	$occupations = $query->query();
	
	// fetch involved cinematographies
	$query->group = new DBGroup([
		new DBSelect(["c.title", "c.productionYear", "t.description"]),
		new DBFrom(["Involving i JOIN Cinematography c ON i.cinematographyId = c.id JOIN InvolvingType t ON t.id = i.involvingTypeId"]),
		new DBWhere(["i.celebrityId = ?"], [[$id, "i"]]),
		new DBOrder(["c.productionYear DESC"])
	]);
	$cinematographies = $query->query();
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

		<link rel="stylesheet" href="styles.css">
		<script src="main.js"></script>
	</head>
	<body class="body container-fluid">
		<?php include($root."/templates/commons/header.php") ?>
		<div id="wrapper" class="row" ng-app="infodb" ng-controller="main">
			<div id="content" class="col-md-8">
				<h1><?php echo $basicInfo[1]." ".$basicInfo[2] ?></h1>
				<md-chips>
				<?php foreach($occupations as $occupation){ ?>
					<md-chip><?php echo $occupation[0] ?></md-chip>
				<?php }; ?>
				</md-chips>
				
				<!-- Date of birth/death -->
				<md-content class="info-paragraph">
				<?php if($basicInfo[6]){ ?>
					Born <?php echo date("F j, Y", $basicInfo[6]); ?>
					<?php if($basicInfo[5]){ echo "in ".$basicInfo[5]; } ?>
				<?php }; ?>
				</md-content>

				<!-- Biography -->
				<div class="title">Biography</div>
				<md-content md-ink-ripple ng-click="displayBio()" class="info-paragraph"><p><?php
					$bio = $basicInfo[3];
					$bio = strlen($bio) > 300 ? trim(substr($bio, 0, 300))."..." : $bio;
					// replace movie qv's with <em>'s
					$bio = preg_replace("/_([^_]*)_ \(qv\)/", "<em>$1</em>", $bio);
					// replace person qv's with <em>'s
					$bio = preg_replace("/'([^']*)' \(qv\)/", "<em>$1</em>", $bio);
					echo $bio;
				?></p></md-content>

				<!-- Involved Cinematographies -->
				<div class="title">Cinematographies</div>
				<md-list flex>
				<?php foreach($cinematographies as $cinematography){ ?>
					<?php if(preg_match("/\(.*\)/", $cinematography[0])){ continue; } ?>
					<md-list-item class="md-2-line">
						<div class="md-list-item-text">
							<h3><?php echo $cinematography[0] ?>
							<p><?php echo $cinematography[1]." - ".$cinematography[2] ?>
						</div>
					</md-list-item>
					<md-divider></md-divider>
				<?php }; ?>
				</md-list>
			</div>
			<?php include($root."/templates/commons/sidebar.php") ?>
		</div>
	</body>
</html>
