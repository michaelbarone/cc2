'use strict';

app.controller('loginCtrl', ['$scope','loginService','$http', '$location','spinnerService', function ($scope,loginService,$http,$location,spinnerService) {
	spinnerService.clear();
	var connected=loginService.islogged();
	connected.then(function(msg){
		if(msg.data==="passedAuth") {
			$location.path('/dashboard');
		}
	});

	$scope.msgtxt='';
	$scope.users = [];
	spinnerService.add("login page users load");
	
    $http.get('data/getUsers.php')
		.success(function(data) {
			$scope.users = data;
		})
		.finally(function() {
			$scope.loaded=1;
			spinnerService.remove("login page users load");
		});

	$scope.closeLockedTile=function(toggleLockedLogin) {
		$scope.loginMsg = '';
		toggleLockedLogin.user = null;
	};
		
	$scope.login=function(data){
		loginService.login(data,$scope);
	};
}]);