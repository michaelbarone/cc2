'use strict';
app.factory('loginService',function($http, $location, sessionService, inform){
	return{
		login:function(data,scope){
			var $promise=$http.post('data/session_login.php',data);
			$promise.then(function(msg){
				if(!msg.data['failed']) {
					var uid=msg.data['uid'];
					var username=msg.data['username'];
					var userid=msg.data['userid'];
					var homeRoom=msg.data['homeRoom'];
					var mobile=msg.data['mobile'];
					var settingsAccess=msg.data['settingsAccess'];
					if(uid){
						inform.clear();
						inform.add('Welcome, ' + username);						
						sessionService.set('uid',uid);
						sessionService.set('username',username);
						sessionService.set('userid',userid);
						sessionService.set('homeRoom',homeRoom);
						sessionService.set('mobile',mobile);
						sessionService.set('settingsAccess',settingsAccess);
						$location.path('/dashboard');
					} else {
						inform.add('Incorrect Information', {
						  ttl: 5000, type: 'warning'
						});
						scope.loginMsg='Incorrect Information';
						$location.path('/login');
						return "failed";
					}
				} else {
					inform.add('Incorrect Information', {
					  ttl: 5000, type: 'warning'
					});
					scope.loginMsg='Incorrect Information';
					$location.path('/login');
					return "failed";					
				}
			});
		},
		logout:function(){
			inform.clear();
			sessionService.destroy('uid');
			sessionStorage.removeItem('uid');
			sessionStorage.removeItem('username');
			sessionStorage.removeItem('userid');
			sessionStorage.removeItem('homeRoom');
			$location.path('/login');
		},
		islogged:function(){
			var $checkSessionServer=$http.post('data/session_check.php');
			return $checkSessionServer;
		}	
	}

});