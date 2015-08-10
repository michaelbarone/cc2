'use strict';

app.controller('loginCtrl', ['$scope','loginService','$http', function ($scope,loginService,$http) {
	$scope.msgtxt='';
	$scope.users = [];
	
    $http.get('data/getUsers.php')
		.success(function(data) {
			$scope.users = data;
		});	

	$scope.closeLockedTile=function(toggleLockedLogin) {
		$scope.loginMsg = '';
		toggleLockedLogin.user = null;
	};
		
	$scope.login=function(data){
		loginService.login(data,$scope);
	};
}]);