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
			#idleMessage{
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}
			#idleMessage-body{
				text-align: center;
			}
			.sidebar{
				width: 350px;
				height: 100%;
				background: white;
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

			@media (max-width: 991px){
				#search-box{
					top: 12px;
					left: 12px;
					right: 12px;
					width: auto;
				}
				.sidebar{
					width: 300px;
				}
			}
		</style>
	</head>
	<body class="body container-fluid" ng-app="explorer" ng-controller="top">
		<md-content id="idleMessage" layout="column" layout-align="center center" ng-if="currentState.state == 'idle'">
			<h2>Welcome to NetworkExplorer.</h2>
			<p id="idleMessage-body">
				Search for a celebrity to explore different celebrities that they both have participated in the same movies.<br>
				The more movies they are in together, the larger and closer the nodes will be.
			</p>
		</md-content>
		<div id="stage" ng-show="currentState.state == 'person'"></div>
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
		<md-fab-speed-dial md-direction="up" md-open="menu.opened" class="md-scale md-fab-bottom-left">
			<md-fab-trigger>
				<md-button aria-label="menu" class="md-fab">
					<md-icon md-svg-src="/resources/images/menu.svg"></md-icon>
				</md-button>
			</md-fab-trigger>
			<md-fab-actions>
				<div ng-repeat="item in menu.items">
					<md-button aria-label="{{item.label}}" class="md-fab md-raised md-mini" ng-click="item.click()">
						<md-tooltip md-direction="right" md-visible="menu.showTooltips" md-autohide="false">
							{{item.label}}
						</md-tooltip>
						<md-icon md-svg-src="{{item.icon}}" aria-label="{{item.label}}"></md-icon>
					</md-button>
				</div>
			</md-fab-actions>
		</md-fab-speed-dial>
		<span id="hiddenView" ng-view></span>
	</body>
</html>
