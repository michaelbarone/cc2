'use strict';
app.factory('loginService',function($http, $location, sessionService, inform, cron){
	return{
		login:function(data){
			var oldUID=sessionService.get('userid');
			var $promise=$http.post('data/session_login.php',data);
			$promise.then(function(msg){
				if(!msg.data['failed']) {
					var uid=msg.data['uid'];
					var username=msg.data['username'];
					var userid=msg.data['userid'];
					var homeRoom=msg.data['homeRoom'];
					var mobile=msg.data['mobile'];
					var settingsAccess=msg.data['settingsAccess'];
					var avatar=msg.data['avatar'];
					if(uid){
						cron.start();
						inform.clear();
						inform.add('Welcome, ' + username);
						sessionService.set('uid',uid);
						sessionService.set('username',username);
						sessionService.set('userid',userid);
						sessionService.set('homeRoom',homeRoom);
						sessionService.set('mobile',mobile);
						sessionService.set('settingsAccess',settingsAccess);
						sessionService.set('avatar',avatar);
						if(oldUID!=userid){
							sessionService.set('currentRoom',homeRoom);
						}
						$location.path('/dashboard');
					} else {
						inform.clear();
						inform.add('Incorrect Information', {
						  ttl: 5000, type: 'warning'
						});
						$location.path('/login');
						return "failed";
					}
				} else {
					inform.clear();
					inform.add('Incorrect Information', {
					  ttl: 5000, type: 'warning'
					});
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
			sessionStorage.removeItem('homeRoom');
			sessionStorage.removeItem('avatar');
			sessionStorage.removeItem('mobile');
			sessionStorage.removeItem('settingsAccess');
			$location.path('/login');
		},
		islogged:function(){
			var $checkSessionServer=$http.post('data/session_check.php');
			return $checkSessionServer;
		}	
	}

});