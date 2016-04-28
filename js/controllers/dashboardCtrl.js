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
 *  Top Right Addon Menu
 */ 	

	$scope.changeRoom = function(room) {
		var unix = Math.round(+new Date()/1000);
		$scope.userdata.currentRoom=room;
		$scope.userdata.lastRoomChange=unix;
		sessionStorage.setItem('currentRoom',room);
		sessionStorage.setItem('lastRoomChange',unix);
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
	
/***/

	
/**
 *  Top Left Menu
 */ 
	
	$scope.loadLinkLongPress = function(name,element) {
		var elementid = name;
		elementid = elementid.substring(0, elementid.length - 1);
		if(document.getElementById(name).classList.contains('loaded')) {
			document.getElementById(name).classList.remove('loaded');
			document.getElementById(elementid).attributes['src'].value = '';
			document.getElementById(name).attributes['class'].value += ' longpress';
		}
	};
	
	$scope.loadLink = function(name,element) {
		var elementid = name;
		elementid = elementid.substring(0, elementid.length - 1);
		
		if(document.getElementById(name).classList.contains('longpress')) {
			document.getElementById(name).classList.remove('longpress');
		}else if(document.getElementById(name).classList.contains('selected')) {
			document.getElementById(elementid).attributes['src'].value = document.getElementById(elementid).attributes['data'].value;
			if(!document.getElementById(name).classList.contains('loaded')) {
				document.getElementById(name).attributes['class'].value += ' loaded';
			}
		} else {
			if(document.getElementById(name).classList.contains('loaded')) {
				document.getElementById(elementid).scrollIntoView();
			} else {
				document.getElementById(name).attributes['class'].value += ' loaded';
				document.getElementById(elementid).attributes['src'].value = document.getElementById(elementid).attributes['data'].value;
				document.getElementById(elementid).scrollIntoView();
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

	var updateAddonsFirst=1;
	$scope.updateAddons = function(){
		if( Idle.idling() === true ) {
			$timeout(function() {
				$scope.updateAddons();
			}, 5000)
		} else {
			$http.get('data/getRoomAddonInfo.php')
				.success(function(data) {
					if(data == "failed") {
						return;
					}
					$scope.room_addons=data;
					if(updateAddonsFirst===1){
						var thisRoom = $scope.userdata.currentRoom;
						document.getElementById("room"+thisRoom).scrollIntoView();						
						updateAddonsFirst=0;
					}
					$timeout(function() {
						$scope.updateAddons();
					}, 5000)		
				});
		}
	};
	$timeout(function() {
		$scope.updateAddons();
	}, 500);

	var cronKeeper = 0;
	$scope.runCron = function(){
		if( Idle.idling() === true ) {
			$timeout(function() {
				$scope.runCron();
			}, 5000)
		} else {
			$http.get('data/cron.php')
				.success(function(data) {
					if(data == "failed") {
						return;
					}
					//$scope.room_addons=data;
					if(data == "takeover") {
						cronKeeper = "1";
					}
					if(data == "release") {
						cronKeeper = "0";
					}
					if (cronKeeper == '1') {
						$timeout(function() {
							$scope.runCron();
						}, 5000)
					} else {
						$timeout(function() {
							$scope.runCron();
						}, 60000)
					}
				});
		}
	};		
	$timeout(function() {
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