<?php
$activePage = "home";
?>

<!doctype html>
<html>
	<head>
		<title>Network Explorer - InfoDB</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

		<link href="https://fonts.googleapis.com/css?family=Playfair+Display|Raleway:300" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.css">
		<link rel="stylesheet" href="/resources/styles.css">

		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.8/angular.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-animate.min.js"></script>
 		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-aria.min.js"></script>
 		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-messages.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-route.min.js"></script>

		<script src="http://ajax.googleapis.com/ajax/libs/angular_material/1.1.0/angular-material.min.js"></script>
		<script src="https://rawgit.com/cytoscape/cytoscape.js/master/dist/cytoscape.js"></script>
		<script src="main.js"></script>
		
		<style>
			html, body, #stage{
				height: 100%;
				margin: 0 !important;
				padding: 0 !important;
			}
			body{
				position: relative;
			}
			#hiddenView{
				position: absolute;
				top: 0;
				right: 0;
				height: 100%;
			}
			#search-box{
				position: absolute;
				top: 20px;
				left: 20px;
				width: 500px;
			}
			#loading-screen{
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}
			.sidebar{
				width: 350px;
				height: 100%;
				background: white;
				box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
			}
			.sidebar.ng-enter, .sidebar.ng-leave{	
				-webkit-transition: -webkit-transform 0.3s ease;
			}
			.sidebar.ng-enter, .sidebar.ng-leave.ng-leave-active{
				-webkit-transform: translateX(100%);
			}
			.sidebar.ng-leave, .sidebar.ng-enter.ng-enter-active{
				-webkit-transform: translateX(0%);
			}
		</style>
	</head>
	<body class="body container-fluid" ng-app="explorer" ng-controller="top">
		<div id="stage"></div>
		<md-autocomplete
			id="search-box"
			md-search-text="query.searchText"
			md-selected-item-change="searchPerson(item.id)"
			md-items="item in findPersonId(query.searchText)"
			md-item-text="item.name"
			md-delay="500"
			placeholder="Search for celebrities"
			md-autofocus="true"
		>
			<md-item-template>
				<span>{{item.name}}</span>
			</md-item-template>
		</md-autocomplete>
		<span id="hiddenView" ng-view></span>
		<div id="loading-screen" layout="column" layout-align="center center" ng-if="isLoading">
			<md-progress-circular md-mode="indeterminate"></md-progress-circular>
		</div>
	</body>
</html>
