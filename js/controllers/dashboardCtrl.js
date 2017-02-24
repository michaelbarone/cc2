'use strict';

app.dashboardController('dashboardCtrl', ['$rootScope','$scope','$timeout','loginService','$http','inform','Idle','$location','ModalService','spinnerService','Fullscreen','addonFunctions', function ($rootScope, $scope, $timeout, loginService, $http, inform, Idle, $location, ModalService, spinnerService, Fullscreen, addonFunctions){
	spinnerService.clear();
	$scope.links = [];
	$scope.links['0'] = [];
	$scope.userdata = [];
	$scope.room_addons = [];
	$scope.room_addons['0'] = [];
	$scope.room_addons_ping = {};
	$scope.room_addons_ping['0'] = {};
	$scope.userdata.currentpage = "dashboard";
	$scope.userdata.roomcount=0;
	$scope.modalOpen=0;
	$scope.colors = ['blue', 'gray', 'green', 'maroon', 'navy', 'olive', 'orange', 'purple', 'red', 'silver', 'teal', 'white', 'lime', 'aqua', 'fuchsia', 'yellow'];
	

	/*
	some security could be to match userid and username in db
	may not need here, login check when querying for addons/rooms and in cron function
	*/
	if(sessionStorage.getItem('username')=='' || sessionStorage.getItem('username')==null){
		loginService.logout();
	} else {
		$scope.userdata.username=sessionStorage.getItem('username');
	}
	$scope.userdata.userid=sessionStorage.getItem('userid');
	$scope.userdata.mobile=sessionStorage.getItem('mobile');
	$scope.userdata.avatar=sessionStorage.getItem('avatar');
	if(window.innerWidth<850 && ($scope.userdata.mobile==='0' || $scope.userdata.mobile===null)){
		$scope.userdata.mobile='1';
		sessionStorage.setItem('mobile','1');
	}
	if(window.innerWidth>849 && ($scope.userdata.mobile==='1' || $scope.userdata.mobile===null)){
		$scope.userdata.mobile='0';
		sessionStorage.setItem('mobile','0');
	}
	$scope.userdata.linkGroupSelected = '';
	$scope.userdata.linkSelected = '';
	$scope.userdata.currentRoom = 'noRoom';
	$scope.userdata.lastRoomChange=Math.round(+new Date()/1000);
	$scope.userdata.settingsAccess=sessionStorage.getItem('settingsAccess');
	if(sessionStorage.getItem('currentRoom')>0) {
		$scope.userdata.currentRoom=sessionStorage.getItem('currentRoom');
		$scope.userdata.linkSelected="room"+$scope.userdata.currentRoom;
	} else {
		$scope.userdata.currentRoom=sessionStorage.getItem('homeRoom');
		sessionStorage.setItem('currentRoom',sessionStorage.getItem('homeRoom'));
		$scope.userdata.linkSelected="room"+$scope.userdata.currentRoom;
	}

/**
 *  Load Initial Data
 */	
 
	$scope.loadLinks = function(){
		$http.get('data/getLinks.php?mobile='+$scope.userdata.mobile)
			.success(function(data) {
				$scope.links = data;
			})
			.finally(function() {
				spinnerService.remove("loadLinks");
			});
	}

	$timeout(function() {
		spinnerService.add("updateAddons");
		$scope.updateAddons();
	}, 10);
	$timeout(function() {
		spinnerService.add("loadLinks");
		$scope.loadLinks();
	}, 100);	
	$timeout(function() {
		$scope.loaded=1;
	}, 300);	
	$scope.FullscreenSupported = Fullscreen.isSupported();

	
/***/


   $rootScope.toggleFullscreen = function () {
      if (Fullscreen.isEnabled())
         Fullscreen.cancel();
      else
         Fullscreen.all();
   }	

	
	
/**
 *  Top Menu
 */
 
	$scope.changeRoom = function(room) {
		var unix = Math.round(+new Date()/1000);
		$scope.userdata.currentRoom=room;
		$scope.userdata.lastRoomChange=unix;
		sessionStorage.setItem('currentRoom',room);
		sessionStorage.setItem('lastRoomChange',unix);
		$scope.userdata.linkSelected="room"+room;
		document.getElementById("room"+room).scrollIntoView();
	};

	$scope.loadLinkLongPress = function(name) {
		if (name.substring(0, 4) == "room" && name.substring(4,5)>0) {
			document.getElementById(name+'L').attributes['class'].value += ' longpress';
		} else if(document.getElementById(name+'L').classList.contains('loaded')) {
			document.getElementById(name+'L').attributes['class'].value += ' longpress';
			document.getElementById(name+'L').classList.remove('loaded');
			document.getElementById(name).attributes['src'].value = '';
		}
	};
	
	$scope.loadLink = function(name) {
		if (name.substring(0, 4) == "room" && name.substring(4,5)>0) {
			if(document.getElementById(name+'L').classList.contains('longpress')) {
				document.getElementById(name+'L').classList.remove('longpress');
				return;
			} else {
				$scope.userdata.linkSelected=name;
				document.getElementById(name).scrollIntoView();
			}
		} else {
			if(document.getElementById(name+'L').classList.contains('longpress')) {
				document.getElementById(name+'L').classList.remove('longpress');
				return;
			} else if(document.getElementById(name+'L').classList.contains('selected')) {
				if(document.getElementById(name).attributes['data'].value != "none"){
					document.getElementById(name).attributes['src'].value = document.getElementById(name).attributes['data'].value;
				}
				if(!document.getElementById(name+'L').classList.contains('loaded')) {
					$scope.userdata.linkSelected=name;
					document.getElementById(name+'L').attributes['class'].value += ' loaded';
				}
			} else {
				$scope.userdata.linkSelected=name;
				if(document.getElementById(name+'L').classList.contains('loaded')) {
					document.getElementById(name).scrollIntoView();
				} else {
					document.getElementById(name+'L').attributes['class'].value += ' loaded';
					document.getElementById(name).scrollIntoView();
					if(document.getElementById(name).attributes['data'].value != "none"){
						document.getElementById(name).attributes['src'].value = document.getElementById(name).attributes['data'].value;
					}
				}
			}
		}
	};
	
	$scope.refreshLink = function(name) {
		if(document.getElementById(name).attributes['data'].value != "none" && $scope.userdata.linkSelected==name){
			document.getElementById(name).attributes['src'].value = document.getElementById(name).attributes['data'].value;
		}
	}

	// 2 jquery functions here:
    $scope.linkReOrder = function(linkgroup,index) {
		var theLi = document.getElementById(linkgroup + '-group');
		$(theLi).parent().prepend(theLi);
    };
	
	
	// needed now?  after selective hiding panels may not be needed
	$(window).resize(function(){
		var thename = $scope.userdata.linkSelected;
		if(thename!="roomnull" || thename !='' || thename !=null){
			document.getElementById(thename).scrollIntoView();
		}
	});	

/***/




/**
 *  Addon functions
 *
 */
 
	
	/*
	this may need to go in dashboardCtrl so it has access to scope.room_addons
	function checkAlive(deadoralive,addonid=null,room=null){
		if(addonid!=null){
			check if addonid is alive
			if alive, remove("powerOnAddon");
		}elseif(room!=null){
			check if roomalive>0
			if alive, remove("powerOnRoom");
		}
		some repeat to checkAlive for either certain time or number of retries
		can also use this to check when powered off..  
	}
	*/ 
 
 
 
	$scope.powerOnAddon = function(addonid){
		addonFunctions.powerOnAddon(addonid);
	};

	$scope.powerOffAddon = function(addonid){
		addonFunctions.powerOffAddon(addonid);
	};
	
	$scope.powerOnRoom = function(room){
		addonFunctions.powerOnRoom(room);
	};

	$scope.powerOffRoom = function(room){
		addonFunctions.powerOffRoom(room);
	};

	$scope.sendMedia = function (sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
		addonFunctions.sendMedia(sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID);
		if($scope.sendFromAddonLock!=2){
			$scope.sendFromAddonReSet();
		}
	}

	$scope.cloneMedia = function (sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
		addonFunctions.cloneMedia(sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID);
		if($scope.sendFromAddonLock!=2){
			$scope.sendFromAddonReSet();
		}
	}
	
	$scope.startMedia = function (sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
		addonFunctions.startMedia(sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID);
		$scope.sendFromAddonReSet();
	}


	/* this controls the room menu, and if it should stay open for multiple commands/inputs */
	$scope.sendFromAddonReSet = function(){
		$scope.sendFromAddonID = '';
		$scope.sendFromAddonIP = '';
		$scope.sendFromAddonLock = 0;
	};
	$scope.sendFromAddonReSet();

	$scope.sendFromAddonSet = function(sendFromAddonIP,sendFromAddonID){
		if($scope.sendFromAddonIP==sendFromAddonIP){
			$scope.sendFromAddonLockAdd();
		} else {
			$scope.sendFromAddonID = sendFromAddonID;
			$scope.sendFromAddonIP = sendFromAddonIP;
			$scope.sendFromAddonLock = 1;
		}
	};
	
	$scope.sendFromAddonLockAdd = function(){
		$scope.sendFromAddonLock++;
		if($scope.sendFromAddonLock==3){
			$scope.sendFromAddonReSet();
		}
	};	
	
	
/***/



/**
 *  Services
 *
 */

	$scope.chart=[];
	$scope.chart.ping=[];
	$scope.chart.ping.datasetOverride = [
		{ yAxisID: 'y-axis-1',
		  backgroundColor: "rgba(0,0,0,0)",
		  type: 'line'
		},
		{ yAxisID: 'y-axis-1',
		  type: 'bar'
		},
		{ yAxisID: 'y-axis-2',
		  type: 'line'
		},
		{ yAxisID: 'y-axis-2',
		  type: 'line'
		},	
	];
	$scope.chart.ping.options = {
		animation: {
			duration: 7000,
		},
		scales: {
			yAxes: [
			{
			  id: 'y-axis-1',
			  type: 'linear',
			  display: true,
			  position: 'left',
			  ticks: {
				min: 0,
				stepSize: 1
			  }			  
			},
			{
			  id: 'y-axis-2',
			  type: 'linear',
			  display: true,
			  position: 'right',
			  ticks: {
				min: 0,
				stepSize: 5
			  }
			}	
			]
		}
	};

	$scope.showModal = function(data,type) {
		spinnerService.add("showModal");
		
		if(type==="addon"){
			var turl = "./partials/tpl/modalAddonInfo.html";
		}else if(type==="userpref"){
			var turl = "./partials/tpl/modalUserPreferences.html";
		}
		
		ModalService.showModal({
			templateUrl: turl
			, controller: "ModalController"
			,inputs: {
				data: data,
		    }
			, scope: $scope
		}).then(function(modal) {
			$scope.modalOpen=1;
			spinnerService.remove("showModal");
            modal.close.then(function() {
				$scope.modalOpen=0;
            });
		});
	};

	$scope.logout=function(){
		loginService.logout();
	};


/***/	
	

/**
 *  update addon loop
 */ 	

	var updateAddonsFirstRun=1;
	var updateAddonsRunning = 0;
	var cronStaleCount = 0;
	var updateAddonsRunningStartTime = 0;
	$scope.updateAddons = function(){
		if(updateAddonsRunning===1 || $location.path()!="/dashboard") { return; }
		updateAddonsRunning = 1;	
		if( Idle.idling() === true) {
			spinnerService.remove("updateAddons");
			$timeout(function() {
				updateAddonsRunning = 0;
				$scope.updateAddons();
			}, 5000);
		} else {
			$http.get('data/getRoomAddonsData.php')
			.success(function(data) {
				if(data == "failedAuth"){
					loginService.logout();
					return;
				} else if(data == "failed") {
					return;
				} else if(data == "noRoomAccess"){
					if(updateAddonsFirstRun===1){
						updateAddonsFirstRun=0;
						$timeout(function() {
							/* load first left side menu item if exists */
							if($scope.links.length>0){
								for(var linkg in $scope.links[0]){
									break;
								}
								for(var linkid in $scope.links[0][linkg]){
									break;
								}
								var linkname = $scope.links[0][linkg][linkid]['navname'];
								linkname = "#"+linkname+"L";
								angular.element(linkname).triggerHandler('click');
							}
						}, 1500);
					}
				} else {
					updateAddonsRunningStartTime = Math.round((new Date).getTime()/1000);
					if($rootScope.systemInfo[0]['lastcrontime']<(updateAddonsRunningStartTime-30)){
						if(cronStaleCount<6){
							cronStaleCount++;			
						} else {
							cronStaleCount = 0;
							inform.add("Browser data needs to be refreshed. <a href'#' class='btn btn-danger' onclick='location.reload(true);return false;'>Refresh</a>", {
								ttl: 8000, type: 'danger', "html": true
							});
						}
					} else {
						cronStaleCount = 0;
						$scope.room_addons=data;
						$scope.userdata.roomcount=Object.keys($scope.room_addons[0]).length;
						if(updateAddonsFirstRun===1){
							if($scope.userdata.currentRoom<1) {
								$scope.userdata.currentRoom=sessionStorage.getItem('currentRoom');
							}
							/* timeout added to allow dom to create the room divs, otherwise the first run gets a console error (cannot find div) */
							spinnerService.remove("updateAddons");
							updateAddonsFirstRun=0;
							$timeout(function() {
								$scope.changeRoom($scope.userdata.currentRoom);								
							}, 100);
						}
						
						var ping = [];
						var theaddonid = '';
						angular.forEach($scope.room_addons[0], function(value, key) {
							angular.forEach(value, function(value2, key2) {
								angular.forEach(value2, function(value3, key3) {
									if(key3=='rooms_addonsid'){
										theaddonid = value3;
									}
									if(key3=='ping'){
										ping = value3;
									}
								});
								if(theaddonid==''){  } else {
									if(!angular.isObject($scope.room_addons_ping[0][theaddonid])) {
										$scope.room_addons_ping[0][theaddonid] = [];
									}
									if(!$scope.room_addons_ping[0][theaddonid+'LastUpdate']) {
										$scope.room_addons_ping[0][theaddonid+'LastUpdate'] = "";
									}								
									if(ping['lastUpdate']>$scope.room_addons_ping[0][theaddonid+'LastUpdate']) {

										angular.forEach(ping, function(pingitem, pingkey) {										
											if(pingkey=='sent'){
												pingkey=0;
											}else if(pingkey=='lost'){
												pingkey=1;
											}else if(pingkey=='timeMax'){
												pingkey=2;
											}else if(pingkey=='timeAve'){
												pingkey=3;
											}else if(pingkey=='lastUpdate'){
												pingkey=-1;
											}
											if(!angular.isArray($scope.room_addons_ping[0][theaddonid][pingkey]) && pingkey>-1) {
												$scope.room_addons_ping[0][theaddonid][pingkey] = [0,0,0,0,0,0,0,0,0,0];
											}
											if(pingkey>-1){
												if($scope.room_addons_ping[0][theaddonid][pingkey].length>9){
													$scope.room_addons_ping[0][theaddonid][pingkey].shift();
												}
												$scope.room_addons_ping[0][theaddonid][pingkey].push(pingitem);
											} else {
												$scope.room_addons_ping[0][theaddonid+'LastUpdate'] = pingitem;
											}
										});
									}
								}
							});
						});
					}
				}
			}).finally(function(){
				if($rootScope.testrun!=1){
					$timeout(function() {
						updateAddonsRunning = 0;
						if(updateAddonsFirstRun<1){
							spinnerService.remove("updateAddons");
						}
						$scope.updateAddons();
					}, 1500);
				}
			});
		}
	};


/***/

	$scope.testmessage = function($scope) {
		inform.add('test');
		inform.add('Default', {
		  ttl: 120000, type: 'default'
		});
		inform.add('Primary with long text string to se asdhafsdifjaskjdf a skdjf laskdjflkajsdf alksdj flkasjdf', {
		  ttl: 120000, type: 'primary'
		});
		inform.add('Info', {
		  ttl: 120000, type: 'info'
		});
		inform.add('Success', {
		  ttl: 120000, type: 'success'
		});
		inform.add('Warning', {
		  ttl: 120000, type: 'warning'
		});
		inform.add('Danger', {
		  ttl: 120000, type: 'danger'
		});
		inform.add("text and <a class='btn btn-default'>a button</a>", {
			  ttl: 60000, type: 'success', "html": true
		});
	};
}])

						/*				
							dont know if overhead of forEach is better than just overwriting the whole variable below
						
						var arrayEqual = '';
						angular.forEach(data[0], function(value, key) {
							if(!$scope.room_addons[0][key]) {
								$scope.room_addons[0][key]=data[0][key];
							} else {
								arrayEqual = angular.equals($scope.room_addons[0][key], data[0][key]);
								//console.log(key + "  " + arrayEqual);
								if(arrayEqual===false){
									$scope.room_addons[0][key]=data[0][key];
								}
							}
							
							
						});	
						*/
						
						/*  option 2
						var arrayEqual = angular.equals($scope.room_addons, data);						
						//if($scope.room_addons != data) {
						if(arrayEqual===false || !$scope.room_addons){
							$scope.room_addons=data;
						}
						*/