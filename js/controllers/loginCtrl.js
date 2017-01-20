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
	$scope.toggleLockedLogin = [];
	$scope.toggleLockedLogin.user = 0;
	
    $http.get('data/getUsers.php')
		.success(function(data) {
			$scope.users = data;
		})
		.finally(function() {
			$scope.loaded=1;
		});

	$scope.closeLockedTile=function(toggleLockedLogin=null) {
		$scope.loginMsg = '';
		if(toggleLockedLogin!=null){
			toggleLockedLogin.user = null;
		}
	};
		
	$scope.login=function(data){
		loginService.login(data,$scope);
	};
	
	
	$scope.$watch('toggleLockedLogin.user', function() {
		if($scope.toggleLockedLogin.user>0){
			document.getElementById('InputPassword'+$scope.toggleLockedLogin.user).focus();
		}
	});
	
}]);