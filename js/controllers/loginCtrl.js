'use strict';

app.controller('loginCtrl', ['$scope','loginService','$http', '$location', function ($scope,loginService,$http,$location) {
	var connected=loginService.islogged();
	connected.then(function(msg){
		if(msg.data==="passedAuth") {
			$location.path('/dashboard');
		}
	});

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