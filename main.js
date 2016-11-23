var app = angular.module("infodb", ["ngMessages", "ngMaterial"]);

app.config(function($mdThemingProvider) {
	$mdThemingProvider.theme('default')
		.primaryPalette("indigo")
		.accentPalette("blue");
});

app.controller("main", function($scope, $http){
	$scope.query = {
		person: {},
		movie: {
			productionYearComp: "during"
		},
		dos: {},
		helpers: {
			queryPersonId: function(name){
				return $http.get("/api/getId.php", {
					params: {type: "person", name: name}
				})
				.then(function(res){
					return res.data;
				});
			}
		}
	};

});
