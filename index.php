<?php
$activePage = "home";
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
		<?php include("templates/commons/header.php") ?>
		<div id="wrapper" class="row" ng-app="infodb" ng-controller="main">
			<div id="content" class="col-md-8">
				<div class="title">Start Your Search</div>
				<md-tabs md-dynamic-height md-border-bottom>
					<md-tab label="Person">
						<?php include("templates/home/person_search.html") ?>
					</md-tab>
					<md-tab label="Movie">
						<?php include("templates/home/movie_search.html") ?>
					</md-tab>
					<md-tab label="Degrees of Separation Finder">
						<?php include("templates/home/dos.html") ?>
					</md-tab>
					<md-tab label="Network Explorer">
						<?php include("templates/home/network.html") ?>
					</md-tab>
				</md-tabs>
			</div>
			<?php include("templates/commons/sidebar.php") ?>
		</div>
	</body>
</html>
