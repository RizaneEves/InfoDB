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
		dos: {
			status: "idle",
			doSearch: function(){
				if(!this.name1 || !this.name2)	return false;

				$scope.query.dos.status = "loading";
				$http.get("/api/getDOS.php", {
					params: {
						id1: this.name1.id,
						id2: this.name2.id
					}
				})
				.then(function(res){
					if(!res.data.found){
						$scope.query.dos.status = "not-found";
						return false;
					}
					$scope.query.dos.status = "success";
					var list = [];
					for(var i = 0; i < res.data.celebrities.length; i++){
						var entry = res.data.celebrities[i];
						entry.type = "celebrity";
						list[i * 2] = entry;
					}
					for(var i = 0; i < res.data.cinematographies.length; i++){
						var entry = res.data.cinematographies[i];
						entry.type = "cinematography";
						list[i * 2 + 1] = entry;
					}
					$scope.query.dos.results = list;
					$scope.query.dos.resultsMeta = {
						sourceName: list[0].firstName + " " + list[0].lastName,
						targetName: list[list.length - 1].firstName + " " + list[list.length - 1].lastName,
						degrees: res.data.celebrities.length - 1
					};
				})
				.catch(function(){
					$scope.query.dos.status = "failed";
				});
			}
		},
		helpers: {
			queryPersonId: function(name){
				return $http.get("/api/getId.php", {
					params: {type: "person", name: name}
				})
				.then(function(res){
					return res.data;
				});
			},
			goToInfo: function(type, id){
				switch(type){
					case "celebrity":
						window.open("/info/person/?id=" + id, "_blank"); break;
					case "cinematography":
						window.open("/info/movie/?id=" + id, "_blank"); break;
				}
			}
		}
	};

});
