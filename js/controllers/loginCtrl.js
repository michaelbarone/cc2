'use strict';

app.controller('loginCtrl', ['$scope','userService','$http', '$location', 'inform', 'spinnerService', function ($scope,userService,$http,$location,inform,spinnerService) {
	spinnerService.clear();
	var connected=userService.islogged();
	connected.then(function(msg){
		if(msg.data==="passedAuth") {
			$location.path('/dashboard');
		}
	});
	if(!$scope.users){
		$scope.users = [];
	}
	$scope.toggleLockedLogin = [];
	$scope.toggleLockedLogin.user = 0;
	$scope.usersError=0;


	$scope.getUsers=function(){
		inform.clear();
		$http.get('data/getUsers.php')
			.success(function(data) {
				$scope.users = data;
			})
			.error(function() {
				$scope.usersError=1;  // check to make sure this doesnt break initial load when no users are set
				if($scope.loaded>0){
					inform.add('No Connection to Server', {
						ttl: 4700, type: 'danger'
					});
				}
			})
			.finally(function() {
				$scope.loaded=1;
				spinnerService.remove("getUsers");
			});		
	}
	$scope.getUsers();
	
	$scope.getUsersCheck=function(){
		spinnerService.add("getUsers");
		$scope.getUsers();
	}

	$scope.closeLockedTile=function(toggleLockedLogin=null) {
		$scope.loginMsg = '';
		if(toggleLockedLogin!=null){
			toggleLockedLogin.user = null;
		}
	};
		
	$scope.login=function(data){
		userService.login(data,$scope);
	};
	
	
	$scope.$watch('toggleLockedLogin.user', function() {
		if($scope.toggleLockedLogin.user>0){
			document.getElementById('InputPassword'+$scope.toggleLockedLogin.user).focus();
		}
	});
	
}]);