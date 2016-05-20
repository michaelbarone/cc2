'use strict';

app.dashboardController('dashboardCtrl', ['$scope','$timeout','loginService','$http','inform','Idle', function ($scope, $timeout, loginService, $http, inform, Idle){
	var unix = Math.round(+new Date()/1000);
	$scope.links = [];
	$scope.rooms = [];
	$scope.userdata = [];
	$scope.userdata.username=sessionStorage.getItem('username');
	$scope.userdata.userid=sessionStorage.getItem('userid');
	$scope.userdata.mobile=sessionStorage.getItem('mobile');	
	$scope.userdata.linkGroupSelected = '';
	$scope.userdata.linkSelected = '';
	$scope.userdata.currentRoom = 'noRoom';
	$scope.userdata.lastRoomChange=unix;
	$scope.userdata.settingsAccess=sessionStorage.getItem('settingsAccess');
	if(sessionStorage.getItem('currentRoom') && sessionStorage.getItem('currentRoom') != '') {
		$scope.userdata.currentRoom=sessionStorage.getItem('currentRoom');
	} else {
		$scope.userdata.currentRoom=sessionStorage.getItem('homeRoom');
	}
	$scope.userdata.linkSelected="room"+$scope.userdata.currentRoom;

/**
 *  Load Initial Data
 */	
    $http.get('data/getRooms.php')
		.success(function(data) {
			$scope.rooms = data;
		});

    $http.get('data/getLinks.php')
		.success(function(data) {
			$scope.links = data;
		});
/***/
	

	
	
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

 
	$scope.wakeAddon = function(mac) {
		if(mac === '' || mac === null){
		} else {
			$http.post('data/wakeAddon.php?m='+mac);
		}
	};

	$scope.wakeAllAddons = function(macs) {
		var macsArray = macs.split(',');
		for(var i = 0; i < macsArray.length; i++) {
			if(macsArray[i]!=='') {
				$http.post('data/wakeAddon.php?m='+macsArray[i]);
			}
		}
	};
	
	$scope.powerOnAddon = function(addonid){
	
	}
	
	$scope.powerOnRoom = function(room){
		$http.post('data/powerOn.php?type=room&room='+room);
	}
	
	$scope.loadLinkLongPress = function(name,element) {
		if (name.substring(0, 4) == "room") {
			document.getElementById(name+'L').attributes['class'].value += ' longpress';
		} else {		
			if(document.getElementById(name+'L').classList.contains('loaded')) {
				document.getElementById(name+'L').classList.remove('loaded');
				document.getElementById(name).attributes['src'].value = '';
				document.getElementById(name+'L').attributes['class'].value += ' longpress';
			}
		}
	};
	
	$scope.loadLink = function(name,element) {
		if (name.substring(0, 4) == "room") {
			if(document.getElementById(name+'L').classList.contains('longpress')) {
				document.getElementById(name+'L').classList.remove('longpress');
			} else {
				document.getElementById(name).scrollIntoView();
			}		
		} else {	
			if(document.getElementById(name+'L').classList.contains('longpress')) {
				document.getElementById(name+'L').classList.remove('longpress');
			}else if(document.getElementById(name+'L').classList.contains('selected')) {
				document.getElementById(name).attributes['src'].value = document.getElementById(name).attributes['data'].value;
				if(!document.getElementById(name+'L').classList.contains('loaded')) {
					document.getElementById(name+'L').attributes['class'].value += ' loaded';
				}
			} else {
				if(document.getElementById(name+'L').classList.contains('loaded')) {
					document.getElementById(name).scrollIntoView();
				} else {
					document.getElementById(name+'L').attributes['class'].value += ' loaded';
					document.getElementById(name).attributes['src'].value = document.getElementById(name).attributes['data'].value;
					document.getElementById(name).scrollIntoView();
				}
			}
		}
	};

    $scope.linkReOrder = function(linkgroup,index) {
		var theLi = document.getElementById(linkgroup + '-group');
		$(theLi).parent().prepend(theLi);
    };

/***/
	
	
	
/**
 *  Logout service
 *  update and cron loops
 */ 	
	
	$scope.logout=function(){
		loginService.logout();
	};

	var updateAddonsFirstRun=1;
	var updateAddonsRunning = 0;
	$scope.updateAddons = function(){
		if(updateAddonsRunning===1) { return; }
		updateAddonsRunning = 1;	
		if( Idle.idling() === true ) {
			$timeout(function() {
				updateAddonsRunning = 0;
				$scope.updateAddons();
			}, 5000)
		} else {
			$http.get('data/getRoomAddonInfo.php')
				.success(function(data) {
					if(data == "failed") {
						return;
					}
					if($scope.room_addons != "data") {
						$scope.room_addons=data;
					}
					if(updateAddonsFirstRun===1){
						var thisRoom = $scope.userdata.currentRoom;
						$scope.changeRoom(thisRoom);
						updateAddonsFirstRun=0;
					}
					$timeout(function() {
						updateAddonsRunning = 0;
						$scope.updateAddons();
					}, 5000)		
				});
		}
	};
	$timeout(function() {
		updateAddonsRunning = 0;
		$scope.updateAddons();
	}, 500);

	var cronKeeper = 0;
	var cronRunning = 0;
	$scope.runCron = function(){
		if(cronRunning===1) { return; }
		cronRunning = 1;
		if( Idle.idling() === true ) {
			$timeout(function() {
				cronRunning = 0;
				$scope.runCron();
			}, 5000)
		} else {
			$http.get('data/cron.php')
				.success(function(data) {
					if(data == "failed") {
						return;
					}
					if(data == "takeover") {
						cronKeeper = "1";
					}
					if(data == "release") {
						cronKeeper = "0";
					}
					if (cronKeeper == '1') {
						$timeout(function() {
							cronRunning = 0;
							$scope.runCron();
						}, 5000)
					} else {
						$timeout(function() {
							cronRunning = 0;
							$scope.runCron();
						}, 15000)
					}
				});
		}
	};		
	$timeout(function() {
		cronRunning = 0;
		$scope.runCron();
	}, 1500);

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
	};
}])