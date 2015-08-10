'use strict';
app.factory('loginService',function($http, $location, sessionService){
	return{
		login:function(data,scope){
			var $promise=$http.post('data/session_login.php',data);
			$promise.then(function(msg){
				if(!msg.data['failed']) {
					var uid=msg.data['uid'];
					var username=msg.data['username'];
					var userid=msg.data['userid'];
					if(uid){
						sessionService.set('uid',uid);
						sessionService.set('username',username);
						sessionService.set('userid',userid);
						$location.path('/dashboard');
					} else {
						scope.loginMsg='incorrect information';
						$location.path('/login');
						return "failed";
					}
				} else {
					scope.loginMsg='incorrect information';
					$location.path('/login');
					return "failed";					
				}
			});
		},
		logout:function(){
			sessionService.destroy('uid');
			sessionStorage.removeItem('uid');
			sessionStorage.removeItem('username');
			sessionStorage.removeItem('userid');
			//sessionService.destroy('username');
			//sessionService.destroy('userid');
			$location.path('/login');
		},
		islogged:function(){
			var $checkSessionServer=$http.post('data/session_check.php');
			return $checkSessionServer;
		}	
	}

});