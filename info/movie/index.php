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
		new DBSelect(["c.id", "c.title", "IFNULL(i.description, \"No description.\")", "c.productionYear", "c.type"]),
		new DBFrom(["Cinematography c LEFT JOIN CinematographyInfo i ON c.id = i.cinematographyId"]),
		$whereClause
	]);

	$basicInfo = $query->query();

	if(!$basicInfo || count($basicInfo) < 1){
		exit("Invalid query.");
	}
	$basicInfo = $basicInfo[0];	// select first row

	// fetch involved celebrities
	$query->group = new DBGroup([
		new DBSelect(["c.id", "c.firstname", "c.lastname", "t.description", "r.characterName"]),
		new DBFrom(["Involving i JOIN Celebrity c ON i.celebrityId = c.id JOIN InvolvingType t ON t.id = i.involvingTypeId LEFT JOIN PersonRoleType r ON i.characterNameId = r.id"]),
		new DBWhere(["i.cinematographyId = ?"], [[$id, "i"]]),
		new DBOrder(["c.firstname ASC", "c.lastname ASC"])
	]);
	$celebrities = $query->query();
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
				<h1><?php echo $basicInfo[1] ?></h1>
				
				<!-- Type -->
				<md-content class="info-paragraph text-capitalize">
					<?php echo $basicInfo[4] ?>
					<?php if($basicInfo[3]){ echo " - Produced during ".$basicInfo[3]; } ?>
				</md-content>

				<!-- Description -->
				<div class="title">Description</div>
				<md-content md-ink-ripple ng-click="displayBio()" class="info-paragraph"><p><?php
					$bio = $basicInfo[2];
					$bio = strlen($bio) > 300 ? trim(substr($bio, 0, 300))."..." : $bio;
					// replace movie qv's with <em>'s
					$bio = preg_replace("/_([^_]*)_ \(qv\)/", "<em>$1</em>", $bio);
					// replace person qv's with <em>'s
					$bio = preg_replace("/'([^']*)' \(qv\)/", "<em>$1</em>", $bio);
					echo $bio;
				?></p></md-content>

				<!-- Involved Celebrities -->
				<div class="title">Cast</div>
				<md-list flex>
				<?php foreach($celebrities as $celebrity){ ?>
					<md-list-item class="md-2-line" ng-click="displayPerson(<?php echo $celebrity[0] ?>)"> <!-- Not safe but no one cares -->
						<div class="md-list-item-text">
							<!-- Person Name -->
							<h3><?php echo $celebrity[1]." ".$celebrity[2] ?>
							<!-- Role Name [as Character Name] -->
							<p><?php echo $celebrity[3] ?> <?php if($celebrity[4]){ echo "as ".$celebrity[4]; } ?></p>
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
