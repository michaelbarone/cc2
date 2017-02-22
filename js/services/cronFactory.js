'use strict';

app.factory('cron', ['$http','$timeout','inform','Idle','spinnerService','$rootScope', function ($http,$timeout,inform,Idle,spinnerService,$rootScope) {
	
	function runCron(firstrun=0,cronStop=0,idleResume=0){
		if(firstrun!=0){
			var cronStop = 0;
			var cronKeeper = 0;
			$rootScope.cronRunning = 0;
			var idleResume = 0;
			if(!$rootScope.systemInfo){
				$rootScope.systemInfo={};
			}
			if(!$rootScope.systemInfo[0]){
				$rootScope.systemInfo[0]={};
			}			
		}
		if($rootScope.cronRunning && $rootScope.cronRunning==1) { return; }
		if( Idle.idling() === true) {
			//console.log("idle start");
			$timeout(function() {
				idleResume = 1;
				runCron(0,cronStop,idleResume);
			}, 5000);
		} else {
			if($rootScope.testrun==1 || cronStop>0){ return; }
			//console.log("cron running");			
			$rootScope.cronRunning = 1;
			if(idleResume==1){
				spinnerService.add("idleResume");
				$timeout(function() {
					spinnerService.remove("idleResume");
					idleResume=0;
				}, 4500);				
			}
			$http.get('data/cron.php')
				.success(function(data) {
					if(data == "failed") {
						cronStop=1;
						return;
					}
					if(data[0]['status'] == "takeover") {
						cronKeeper = "1";
					} else if(data[0]['status'] == "release") {
						cronKeeper = "0";
					}
					if($rootScope.systemInfo[0]['ccversion'] && data[0]['ccversion']!=$rootScope.systemInfo[0]['ccversion']){
						/* system version is different from browser cache, refresh browser  */
						inform.add("System has been updated. <a href'#' class='btn btn-danger' onclick='location.reload(true);return false;'>Refresh</a>", {
							ttl: 9800, type: 'danger', "html": true
						});
					} else {
						$rootScope.systemInfo = data;
					}
				}).error(function(){
					$rootScope.cronRunning = 0;
					inform.add('No Connection to Server', {
						ttl: 4700, type: 'danger'
					});
				}).finally(function(){
					if (cronKeeper == '1') {
						$timeout(function() {
							$rootScope.cronRunning = 0;
							runCron(0,cronStop);
						}, 2500);
					} else {
						$timeout(function() {
							$rootScope.cronRunning = 0;
							runCron(0,cronStop);
						}, 5000);
					}
				});
		}
	};

	return{
		start:function(func=null){
			//console.log('start cron');
			runCron(1);
		},
		stop:function(func=null){
			//console.log('stop cron');
			cronStop = 1;
		}
	};
}]);