'use strict';

app.settingsController('serverCheckCtrl', ['$scope','$timeout','$location','loginService','$http','inform','$route', function ($scope, $timeout, $location, loginService, $http, inform, $route){
	$scope.userdata = [];
	$scope.userdata.currentpage = "Server Check";
	$scope.Users = [];
	$scope.serverCheck = [];
	$scope.username="";
	$scope.password="";
	

	

	
	$scope.serverCheck = function() {
		$http.get('data/serverCheck.php')
			.success(function(data) {
				$scope.serverCheck = data;
			});		
	}
	
	
	
	
	
	
	$scope.getAllUsers = function() {
		$http.get('data/settings.php?action=getUsers')
			.success(function(data) {
				$scope.Users = data;
			});
	}
	
	
	
	
	$scope.submitNU = function(){

		$scope.getAllUsers();
		
		if($scope.Users.length===0){


			console.log('success - no existing users');
			console.log("username: "+this.username+" and pass: "+this.password);
			
		} else {
			console.log('failed-existing users');
			console.log("username: "+this.username+" and pass: "+this.password);
			return;
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	$scope.getAllUsers();
	$scope.serverCheck();
	
	
	
	
	
	
	
}])	