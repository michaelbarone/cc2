'use strict';

app.dashboardController('dashboardCtrl', ['$rootScope','$scope','$timeout','loginService','$http','inform','Idle','$location','ModalService','spinnerService','Fullscreen', function ($rootScope, $scope, $timeout, loginService, $http, inform, Idle, $location, ModalService, spinnerService, Fullscreen){
	spinnerService.clear();
	var unix = Math.round(+new Date()/1000);
	$scope.links = [];
	$scope.rooms = [];
	$scope.rooms['0'] = [];
	$scope.userdata = [];
	$scope.room_addons = '';
	$scope.room_addons_current = '';
	$scope.userdata.currentpage = "dashboard";
	$scope.colors = ['blue', 'gray', 'green', 'maroon', 'navy', 'olive', 'orange', 'purple', 'red', 'silver', 'teal', 'white', 'lime', 'aqua', 'fuchsia', 'yellow'];
	
	
	
	
	/*
	some security could be to match userid and username in db
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
	$scope.userdata.lastRoomChange=unix;
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
 
	$scope.loadRooms = function(){
		$http.get('data/getRooms.php')
			.success(function(data) {
				$scope.rooms = data;
			})
			.finally(function() {
				spinnerService.remove("loadRooms");
			});
	}

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
		spinnerService.add("loadRooms");
		$scope.loadRooms();
	}, 10);		
	$timeout(function() {
		spinnerService.add("loadLinks");
		$scope.loadLinks();
	}, 75);
	$timeout(function() {		
		updateAddonsRunning = 0;
		spinnerService.add("updateAddons");
		$scope.updateAddons();
	}, 125);
	
	$timeout(function() {
		$scope.loaded=1;
		cronRunning = 0;
		$scope.runCron();
	}, 250);	
	$scope.FullscreenSupported = Fullscreen.isSupported();
	
/***/


   $rootScope.toggleFullscreen = function () {

      if (Fullscreen.isEnabled())
         Fullscreen.cancel();
      else
         Fullscreen.all();

      // Set Fullscreen to a specific element (bad practice)
      // Fullscreen.enable( document.getElementById('img') )

   }	

	
	
/**
 *  Top Menu
 */
 
	$scope.changeRoom = function(room) {
		$scope.room_addons_current = $scope.room_addons;
		var unix = Math.round(+new Date()/1000);
		$scope.userdata.currentRoom=room;
		$scope.userdata.lastRoomChange=unix;
		sessionStorage.setItem('currentRoom',room);
		sessionStorage.setItem('lastRoomChange',unix);
		$scope.userdata.linkSelected="room"+room;
		document.getElementById("room"+room).scrollIntoView();
	};

	$scope.loadLinkLongPress = function(name) {
		if (name.substring(0, 4) == "room") {
			document.getElementById(name+'L').attributes['class'].value += ' longpress';
		} else if(document.getElementById(name+'L').classList.contains('loaded')) {
			// console.log("longpress");
			document.getElementById(name+'L').attributes['class'].value += ' longpress';
			document.getElementById(name+'L').classList.remove('loaded');
			document.getElementById(name).attributes['src'].value = '';
		}
	};
	
	$scope.loadLink = function(name) {
		if (name.substring(0, 4) == "room") {
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
	$scope.powerOnAddon = function(addonid){
		spinnerService.add("powerOnAddon");
		$http.post('data/power.php?type=addon&option=on&addonid='+addonid);
	}

	$scope.powerOffAddon = function(addonid){
		$http.post('data/power.php?type=addon&option=off&addonid='+addonid);
	}
	
	$scope.powerOnRoom = function(room){
		spinnerService.add("powerOnRoom");
		$http.post('data/power.php?type=room&option=on&room='+room);
	}

	$scope.powerOffRoom = function(room){
		$http.post('data/power.php?type=room&option=off&room='+room);
	}

	
	
	
	
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
	
	$scope.sendMedia = function (sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
		$http.post('data/mediaSend.php?fromip='+sendFromAddonIP+'&fromaddon='+sendFromAddonID+'&toip='+sendToAddonIP+'&toaddon='+sendToAddonID);
		if($scope.sendFromAddonLock!=2){
			$scope.sendFromAddonReSet();
		}
	}

	$scope.cloneMedia = function (sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
		$http.post('data/mediaSend.php?fromip='+sendFromAddonIP+'&fromaddon='+sendFromAddonID+'&toip='+sendToAddonIP+'&toaddon='+sendToAddonID+'&type=clone');
		if($scope.sendFromAddonLock!=2){
			$scope.sendFromAddonReSet();
		}
	}
	
	$scope.startMedia = function (sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
		$http.post('data/mediaSend.php?fromip='+sendFromAddonIP+'&fromaddon='+sendFromAddonID+'&toip='+sendToAddonIP+'&toaddon='+sendToAddonID+'&type=start');
		$scope.sendFromAddonReSet();
	}

/***/
	
	
	
/**
 *  Modal service
 *
 */

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
		}).then(function(modal) {
			$scope.modalOpen=1;
			spinnerService.remove("showModal");
		});
	};



/**
 *  Logout service
 *  update and cron loops
 */ 	
	
	$scope.logout=function(){
		loginService.logout();
	};

	var idleResumee = 0;
	var updateAddonsFirstRun=1;
	var updateAddonsRunning = 0;
	$scope.updateAddons = function(){
		if(updateAddonsRunning===1 || $location.path()!="/dashboard") { return; }
		updateAddonsRunning = 1;	
		if( Idle.idling() === true) {
			spinnerService.remove("updateAddons");
			$timeout(function() {
				updateAddonsRunning = 0;
				$scope.updateAddons();
			}, 5000)
		} else {
			if($scope.links.constructor.toString().indexOf("Array") != -1){
				// console.log("yes");
			} else {
				// console.log("running loadLinks()");
				$scope.loadLinks();
			}
			if($scope.rooms.constructor.toString().indexOf("Array") != -1){
				// console.log("yes");
			} else {
				//console.log("running loadLinks()");
				$scope.loadRooms();
			}

			if($scope.rooms['0'].length!=0){

				$http.get('data/getRoomAddonsData.php')
					.success(function(data) {
						if(data == "failedAuth"){
							loginService.logout();
							return;
						}
						if(data == "failed") {
							// need to differentiate failed vs server not responding/not found.  if server not responding, dont return.  only if fail (as fail currently means there is no room data set in db)
							return;
						}
						if(idleResumee===1){ 
							spinnerService.clear();
							idleResumee=0;
						}
						if($scope.room_addons != data) {
							$scope.room_addons=data;
						}
						if(updateAddonsFirstRun===1){
							if($scope.userdata.currentRoom<1) {
								$scope.userdata.currentRoom=sessionStorage.getItem('currentRoom');
							}
							$scope.changeRoom($scope.userdata.currentRoom);
							updateAddonsFirstRun=0;
							spinnerService.remove("updateAddons");
						}						
						$timeout(function() {
							updateAddonsRunning = 0;
							$scope.updateAddons();
						}, 5000)
					}).error(function(){
						// no access to server
						//inform.add('No Connection to Server', {
						//	ttl: 16500, type: 'danger'
						//});
						$timeout(function() {
							updateAddonsRunning = 0;
							$scope.updateAddons();
						}, 15000);
					});
			} else {
				spinnerService.remove("updateAddons");
				$timeout(function() {
					updateAddonsRunning = 0;
					$scope.updateAddons();
				}, 5000);
			}
		}
	};

	var cronKeeper = 0;
	var cronRunning = 0;
	$scope.runCron = function(){
		//console.log("runcron");
		if(cronRunning===1 || $location.path()!="/dashboard") { return; }
		if( Idle.idling() === true) {
			$timeout(function() {
				idleResumee = 1;
				$scope.runCron();
			}, 5000)
		} else {
			cronRunning = 1;
			if(idleResumee===1){ spinnerService.add("idleResume"); }
			$http.get('data/cron.php')
				.success(function(data) {
					if(data == "failed") {
						return;
					}
					if(data == "takeover") {
						cronKeeper = "1";
					}else if(data == "release") {
						cronKeeper = "0";
					}
					if (cronKeeper == '1') {
						$timeout(function() {
							cronRunning = 0;
							$scope.runCron();
						}, 2500);
					} else {
						$timeout(function() {
							cronRunning = 0;
							$scope.runCron();
						}, 15000);
					}
				}).error(function(){
					// no access to server
					cronRunning = 0;
					$timeout(function() {
						$scope.runCron();
					}, 15000);
					inform.add('No Connection to Server', {
						ttl: 8000, type: 'danger'
					});
				});
		}
	};	
	
	
	
	
	/*  not working, need to figure out why cannot call functions from these
	inform.add("Go Full Screen <a ng-click='$rootScope.toggleFullscreen();remove(msg)' class='btn btn-default'>Full Screen</a>", {
		  ttl: 60000, type: 'success', "html": true
	});
	console.log("load");
	*/

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