var app = angular.module("infodb", ["ngPrism"]);

app.controller("main", function($scope, $http){
	$scope.stage4 = {
		status: "pending",
		sampleCode: "",
		result: undefined
	};

	// Fetch sample code
	$http.get("/resources/stage4/sample_code.txt")
	.then(function(content){
		$scope.stage4.sampleCode = content.data;
		return $http.post("/api/query.php", {query: content.data});
	})
	.then(function(result){
		$scope.stage4.status = "ok";
		$scope.stage4.result = result.data.data;
	}, function(result){
		$scope.stage4.status = "failed";
	});


});
