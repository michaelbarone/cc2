'use strict';
app.factory('userService',function($http, $location, sessionService, inform, cron){
	return{
		login:function(data){
			var oldUID=sessionService.get('userid');
			var $promise=$http.post('data/session_login.php',data);
			$promise.then(function(msg){
				if(!msg.data['failed']) {
					if(msg.data['uid']){
						cron.start();
						inform.clear();
						inform.add('Welcome, ' + msg.data['username']);					
						$.each(msg.data, function(k,i) {
							sessionService.set(k,i);
							if(k=="homeRoom" && oldUID!=msg.data['userid']){
								sessionService.set('currentRoom',i);
							}					
						});
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
			sessionStorage.removeItem('passwordv');
			sessionStorage.removeItem('homeRoom');
			sessionStorage.removeItem('avatar');
			sessionStorage.removeItem('mobile');
			sessionStorage.removeItem('settingsAccess');
			$location.path('/login');
		},
		islogged:function(){
			var $checkSessionServer=$http.post('data/session_check.php');
			return $checkSessionServer;
		},
		setPassword:function(userid,password,currentPassword,activeUserid){
			var data = {};
			data['set']='password';
			data['userid']=userid;
			data['password']=password;
			data['currentPassword']=currentPassword;
			data['activeUserid']=activeUserid;
			var $promise=$http.post('data/userSet.php',JSON.stringify(data));
			$promise.then(function(msg){
				if(msg.data=="badPass"){
					inform.add("Current Password Didn't Match", {
						ttl: 4000, type: 'warning'
					});					
					return "wrongPassword";
				}else if(msg.data=="failed"){
					inform.add('Failed to Set Password', {
						ttl: 4000, type: 'danger'
					});
					return "failed";
				}else if(msg.data=="success"){
					inform.add('Set Password Successfully', {
						ttl: 4000, type: 'success'
					});
					return "success";
				}	
			});
		}
	}
});