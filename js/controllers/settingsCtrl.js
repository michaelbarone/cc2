'use strict';

app.settingsController('settingsCtrl', ['$scope','$timeout','$http','inform','Idle', function ($scope, $timeout, $http, inform, Idle){
	$scope.userdata = [];
	$scope.userdata.username=sessionStorage.getItem('username');
	$scope.userdata.userid=sessionStorage.getItem('userid');
	$scope.userdata.mobile=sessionStorage.getItem('mobile');




    $scope.tabs = [{
            title: 'One',
            url: './partials/tpl/settings1.tpl.html'
        }, {
            title: 'Two',
            url: './partials/tpl/settings2.tpl.html'
        }, {
            title: 'Three',
            url: './partials/tpl/settings3.tpl.html'
    }];

    $scope.currentTab = './partials/tpl/settings1.tpl.html';

    $scope.onClickTab = function (tab) {
        $scope.currentTab = tab.url;
    }
    
    $scope.isActiveTab = function(tabUrl) {
        return tabUrl == $scope.currentTab;
    }



	
	
	$scope.logout=function(){
		loginService.logout();
	};

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


/*
	$scope.userdata = [];
	$scope.userdata.username=sessionStorage.getItem('username');
	$scope.userdata.userid=sessionStorage.getItem('userid');
	$scope.userdata.mobile=sessionStorage.getItem('mobile');	
	$scope.links = [];
	$scope.rooms = [];
	$scope.userdata.linkGroupSelected = '';
	$scope.userdata.linkSelected = '';
	$scope.userdata.currentRoom = 'noRoom';
	$scope.userdata.settingsAccess=sessionStorage.getItem('settingsAccess');
	if(sessionStorage.getItem('currentRoom') && sessionStorage.getItem('currentRoom') != '') {
		$scope.userdata.currentRoom=sessionStorage.getItem('currentRoom');
	} else {
		$scope.userdata.currentRoom=sessionStorage.getItem('homeRoom');
	}

    $http.get('data/getLinks.php')
		.success(function(data) {
			$scope.links = data;
		});

    $http.get('data/getRooms.php')
		.success(function(data) {
			$scope.rooms = data;
		});	
	
	
	$scope.changeRoom = function(room) {
		$scope.userdata.currentRoom=room;
		sessionStorage.setItem('currentRoom',room);
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
		
	$scope.loadLink = function(name,element) {
		document.getElementById(name).attributes['class'].value += ' loaded';
	};

    $scope.linkReOrder = function(linkgroup,index) {
		var theLi = document.getElementById(linkgroup + '-group');
		$(theLi).parent().prepend(theLi);
    };

	$scope.logout=function(){
		loginService.logout();
	};

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
	
	$scope.updateAddons = function(){
		if( Idle.idling() === true ) {
			$timeout(function() {
				$scope.updateAddons();
			}, 5000)
		} else {
			$http.get('data/getRoomAddonInfo.php')
				.success(function(data) {
					$scope.room_addons=data;
					$timeout(function() {
						$scope.updateAddons();
					}, 5000)		
				});
		}
	};		
	$scope.updateAddons();
	
	var cronKeeper = 0;
	$scope.runCron = function(){
		if( Idle.idling() === true ) {
			$timeout(function() {
				$scope.runCron();
			}, 5000)
		} else {
			$http.get('data/cron.php')
				.success(function(data) {
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
	$scope.runCron();		


app.filter('trustUrl', function ($sce) {
	return function(url) {
		return $sce.trustAsResourceUrl(url);
	};
});

app.config(function(ngScrollToOptionsProvider) {
    ngScrollToOptionsProvider.extend({
        handler: function(el) {
			var myEl = document.getElementById(el.id);
			if(myEl.attributes['src'].value===""){
				myEl.attributes['src'].value = myEl.attributes['data'].value;
			}
            el.scrollIntoView();
        }
    });
});
*/