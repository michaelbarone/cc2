'use strict';

app.factory('cron', ['$http','$timeout','inform','Idle','spinnerService','$rootScope', function ($http,$timeout,inform,Idle,spinnerService,$rootScope) {

	var cronStop=0;
	var cronKeeper = 0;
	var cronRunning = 0;
	var idleResume = 0;
	if(!$rootScope.systemInfo){
		$rootScope.systemInfo={};
	}
	if(!$rootScope.systemInfo[0]){
		$rootScope.systemInfo[0]={};
	}
	function runCron(){
		if(cronRunning===1) { return; }
		if( Idle.idling() === true) {
			$timeout(function() {
				idleResume = 1;
				runCron();
			}, 5000);
		} else {
			if($rootScope.testrun==1 || cronStop>0){ return; }			
			cronRunning = 1;
			if(idleResume===1){
				spinnerService.add("idleResume");
				$timeout(function() {
					spinnerService.remove("idleResume");
					idleResume=0;
				}, 4500);				
			}
			$http.get('data/cron.php')
				.success(function(data) {
					if(data == "failed") {
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
					cronRunning = 0;
					inform.add('No Connection to Server', {
						ttl: 4700, type: 'danger'
					});
				}).finally(function(){
					if (cronKeeper == '1') {
						$timeout(function() {
							cronRunning = 0;
							runCron();
						}, 2500);
					} else {
						$timeout(function() {
							cronRunning = 0;
							runCron();
						}, 5000);
					}
				});
		}
	};

	return{
		start:function(func=null){
			//console.log('start cron');
			runCron();
		},
		stop:function(func=null){
			//console.log('stop cron');
			cronStop = 1;
		}
	};
}]);