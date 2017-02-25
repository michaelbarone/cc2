'use strict';

app.factory('cron', ['$http','$timeout','inform','Idle','spinnerService','$rootScope','$location', function ($http,$timeout,inform,Idle,spinnerService,$rootScope,$location) {
	
	if(!$rootScope.systemInfo){
		$rootScope.systemInfo={};
	}
	if(!$rootScope.systemInfo[0]){
		$rootScope.systemInfo[0]={};
	}
	
	var cronVars={};
	
	function resetCronVars(){
		cronVars['cronStop']=0;
		cronVars['cronKeeper']=0;
		cronVars['idleResume']=0;
		cronVars['informSystemVersionDifferentCron']='';
		cronVars['informNoServerConnectionCron']='';
		$rootScope.cronRunning = 0;
		//console.log("reset cronVars:");
		//console.log(cronVars);
	}
	
	

	//function runCron(firstrun=0,cronStop=0,idleResume=0,informSystemVersionDifferentCron='',informNoServerConnectionCron=''){
	function runCron(firstrun=0){
		if(firstrun!=0){
			resetCronVars();
		}
		if($rootScope.cronRunning && $rootScope.cronRunning==1) { return; }
		if( Idle.idling() === true) {
			//console.log("idle start");
			$timeout(function() {
				cronVars['idleResume']=1;
				runCron();
			}, 5000);
		} else {
			if($rootScope.testrun==1 || cronVars['cronStop']>0){ return; }
			//console.log("cron running");			
			$rootScope.cronRunning = 1;
			if(cronVars['idleResume']==1){
				spinnerService.add("idleResume");
				$timeout(function() {
					spinnerService.remove("idleResume");
					cronVars['idleResume']=0;
				}, 4500);				
			}
			$http.get('data/cron.php')
				.success(function(data) {
					if(data == "failed") {
						cronVars['cronStop']=1;
						return;
					}
					if(data[0]['status'] == "takeover") {
						cronVars['cronKeeper'] = "1";
					} else if(data[0]['status'] == "release") {
						cronVars['cronKeeper'] = "0";
					}
					if($rootScope.systemInfo[0]['ccversion'] && data[0]['ccversion']!=$rootScope.systemInfo[0]['ccversion']){
						/* system version is different from browser cache, refresh browser  */
						inform.remove(cronVars['informSystemVersionDifferentCron']);
						cronVars['informSystemVersionDifferentCron'] = inform.add("System has been updated. <a href'#' class='btn btn-danger' onclick='location.reload(true);return false;'>Refresh</a>", {
							ttl: 15000, type: 'danger', "html": true
						});
					} else {
						inform.remove(cronVars['informNoServerConnectionCron']);
						$rootScope.systemInfo = data;
					}
				}).error(function(){
					cronVars['cronKeeper'] = "0";
					$rootScope.cronRunning = 0;
					inform.remove(cronVars['informNoServerConnectionCron']);
					cronVars['informNoServerConnectionCron'] = inform.add('No Connection to Server', {
						ttl: 10000, type: 'danger'
					});
				}).finally(function(){
					if (cronVars['cronKeeper'] == '1') {
						$timeout(function() {
							$rootScope.cronRunning = 0;
							runCron();
							//runCron(0,cronStop,0,informSystemVersionDifferentCron,informNoServerConnectionCron);
						}, 2500);
					} else if($location.path()=="/login"){
						resetCronVars();
						return;
					} else {
						$timeout(function() {
							$rootScope.cronRunning = 0;
							runCron();
							//runCron(0,cronStop,0,informSystemVersionDifferentCron,informNoServerConnectionCron);
						}, 5000);
					}
				});
		}
	};

	return{
		start:function(func=null){
			//console.log('start cron');
			$timeout(function() {
				runCron(1);
			}, 1500);
		},
		stop:function(func=null){
			//console.log('stop cron');
			cronVars['cronStop']=1;
		}
	};
}]);