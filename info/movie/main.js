var app = angular.module("infodb", ["ngMessages", "ngMaterial"]);

app.config(function($mdThemingProvider) {
	$mdThemingProvider.theme('default')
		.primaryPalette("indigo")
		.accentPalette("blue");
});

app.controller("main", function($scope, $http){
	$scope.displayPerson = function(id){
		location.href = "/info/person/?id=" + id;
	};
});
