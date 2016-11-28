angular.module("explorer", ["ngRoute", "ngAnimate", "ngMessages", "ngMaterial"])

.config(function($mdThemingProvider, $routeProvider) {
	$mdThemingProvider.theme("default")
		.primaryPalette("blue")
		.accentPalette("indigo");

	$routeProvider
		.when("/", {
			templateUrl: "hiddenView.html",
			controller: "idle"
		})
		.when("/person/:id", {
			templateUrl: "person.html",
			controller: "person"
		})
		.otherwise("/");
})

.value("BASE_TITLE", "Network Explorer - InfoDB")

.controller("top", function($scope, $q, $http, $location, $timeout, $mdDialog){
	// Initialize stage for Cytoscape
	$scope.stageContainer = document.getElementById("stage");
	var stylesheet = $http.get("cyStyles.css").then(function(res){
		return res.data;
	});
	$scope.cy = cytoscape({
		container: $scope.stageContainer,
		layout: {
			name: "cose",
			randomize: true
		},
		style: stylesheet,
	});
	$scope.cy.on("tapstart", function(){
		document.activeElement.blur();
	});
	window.cy = $scope.cy;

	// Initialize query related functions
	$scope.query = {
		searchText: ""
	};
	$scope.searchPerson = function(id){
		if(id){
			$location.path("/person/" + id);
			document.activeElement.blur();
		}
	};
	$scope.findPersonId = function(name){
		return $http.get("/api/getId.php", {
			params: {type: "person", name: name}
		})
		.then(function(res){
			return res.data;
		});
	};
	
	// Initialize route state
	$scope.currentState = {state: "idle"};

	// Initialize loading dialog
	$scope.loadingDialog = {message: ""};
	$scope.loads = function(promise){
		$mdDialog.show({
			controller: function(){},
			templateUrl: "loadingDialog.html",
			escapeToClose: false,
			clickOutsideToClose: false,
			scope: $scope.$new()
		});

		return promise.finally(function(){
			$mdDialog.hide();
			return promise;
		});
	};

	// Initialize menu items
	$scope.menu = {
		opened: false,
		showTooltips: false,
		items: [{
			label: "Back to InfoDB",
			icon: "/resources/images/home.svg",
			click: function(){
				location.href = "/";
			}
		}]
	};
	$scope.$watch("menu.opened", function(state){
		// show tooltips after 250ms
		$timeout(function(){
			$scope.menu.showTooltips = $scope.menu.opened;
		}, state ? 250 : 0);
	});
})

// Controllers below inherit $scope from top
.controller("idle", function($scope, $http, BASE_TITLE){
	console.log("idle", $scope.cy);
	$scope.currentState.state = "idle";
	document.title = BASE_TITLE;
	// set up fab menu
	$scope.menu.items = [{
		label: "Back to InfoDB",
		icon: "/resources/images/home.svg",
		click: function(){
			location.href = "/";
		}
	}];
})

.controller("person", function($scope, $q, $http, $routeParams, $mdToast, BASE_TITLE){
	console.log("person", $scope.cy, $routeParams.id);
	// set up current state
	$scope.currentState.state = "person";
	// set up fab menu
	$scope.menu.items = [{
		label: "Back to InfoDB",
		icon: "/resources/images/home.svg",
		click: function(){
			location.href = "/";
		}
	}, {
		label: "Fit to Screen",
		icon: "/resources/images/fitToScreen.svg",
		click: function(){
			cy.fit();
		}
	}];
	// store id
	$scope.id = $routeParams.id;
	// defocus input
	document.activeElement.blur();

	$scope.showPersonDetails = function(id){
		window.open("/info/person/?id=" + id, "_blank");
	};
	$scope.showMovieDetails = function(id){
		window.open("/info/movie/?id=" + id, "_blank");
	};

	$scope.loadingDialog.message = "Generating graph...";
	$scope.loads($http.get("/api/getPersonNetwork.php", {
		params: {id: $scope.id}
	}).then(function(res){
		var deferred = $q.defer();
		$scope.query.searchText = res.data.targetName;
		document.title = res.data.targetName + " - " + BASE_TITLE;
		$scope.loadingDialog.message = "Rendering " + (res.data.nodes.length + 1) + " nodes...";
		// update dom
		setTimeout(function(){
			deferred.resolve(res.data);
		});
		return deferred.promise;
	}).then(function(data){
		console.log(data);
		var cy = $scope.cy;
		// remove existing nodes
		cy.remove("*");
		// create new elements
		var numLevels = 3,
			length = data.maxCinematographiesSize - data.minCinematographiesSize,
			levelLength = length / numLevels;

		var elements = [{
			group: "nodes",
			data: {
				id: $scope.id,
				display: data.targetName,
				nodeLevel: numLevels,
				nodeSize: 50
			},
			classes: "target",
			grabbable: false
		}];

		angular.forEach(data.nodes, function(node){
			var nodeLevel = Math.min(numLevels - 1, Math.floor((node.cinematographies.length - data.minCinematographiesSize) / levelLength));
			elements.push({
				group: "nodes",
				data: {
					id: node.id,
					display: node.display,
					nodeLevel: nodeLevel,
					nodeSize: 50,
					cinematographies: node.cinematographies
				},
				classes: "related-nodes",
				grabbable: false
			});
			elements.push({
				group: "edges",
				data: {
					source: $scope.id,
					target: node.id,
					nodeLevel: nodeLevel,
					cinematographies: node.cinematographies
				}
			});
		});
		// add elements to graph
		cy.startBatch();				
		var collection = cy.add(elements);
		collection.on("select", function(){
			var id = this.id();
			var node = this;
			$scope.$apply(function(){
				$scope.inspectingNode = node;
			});
			if(node.renderedBoundingBox().w > 12 * 5){
				cy.animate({
					duration: 300,
					center: {
						eles: node
					},
					easing: "ease-out-cubic" // produces weird easing
				});
			}else{
				cy.animate({
					duration: 300,
					fit: {
						eles: node,
						padding: node.data("nodeSize") * 7
					},	
					easing: "ease-out-cubic" // produces weird easing
				});
			}
		})
		.on("unselect", function(){
			$scope.$apply(function(){
				$scope.inspectingNode = undefined;
			});
		});
		cy.elements().layout({
			name: "concentric",
			concentric: function(node){
				var level = node.data("nodeLevel");
				if(level === undefined)	return numLevels;
				else	return level;
			},
			levelWidth: function(nodes){
				return 1;
			},
			equidistant: true,
			// This function resizes the nodes into suitable sizes
			stop: function(){
				// get level distance
				var targetNode = cy.$(".target"),
					targetPos = targetNode.position(),
					levelNodes = cy.$("node[nodeLevel=0]");
				var max = levelNodes[0].position().y;
				for(var j = 1; j < levelNodes.length; j++){
					var y = levelNodes[j].position().y;
					if(y < max){
						max = y;
					}
				}
				var distance = Math.abs(max - targetPos.y),
					levelDistance = distance / numLevels / 2;

				for(var i = 0; i <= numLevels; i++){
					// get node available radius
					var nodes = cy.$("node[nodeLevel="+i+"]");
					var edges = cy.$("edge[nodeLevel="+i+"]");
					var maxRadius = nodes.length < 3 ? levelDistance / 2 : levelDistance * (numLevels - i) * Math.sin(Math.PI * 2 /nodes.length);
					var size = Math.min(levelDistance * (1 - 0.2 * (numLevels - i) / numLevels), maxRadius * 2);
					nodes.style({width: size, height: size, fontSize: size / 5, textOutlineWidth: size / 50, borderWidth: size / 25});
					edges.style({width: size * 0.1, zIndex: i});
				}
			}
		});
		cy.endBatch();
	})).catch(function(err){
		console.error(err);
		$mdToast.show(
      			$mdToast.simple()
        		.textContent("Failed to load graph: " + err.data.replace(/<.+?>/g, "").trim())
			.action("OK")
			.hideDelay(5000)
		);
	});
})

;
