'use strict';

app.settingsController('settingsCtrl', ['$scope','$timeout','$http','inform','Idle','$route', function ($scope, $timeout, $http, inform, Idle, $route){
	$scope.userdata = [];
	$scope.Users = [];
	$scope.Rooms = [];
	$scope.userdata.username=sessionStorage.getItem('username');
	$scope.userdata.userid=sessionStorage.getItem('userid');
	$scope.userdata.mobile=sessionStorage.getItem('mobile');

    $scope.tabs = [{
            title: 'Users',
            url: './partials/tpl/settingsUsers.tpl.html'		
        }, {
            title: 'Rooms',
            url: './partials/tpl/settingsRooms.tpl.html'
        }, {
            title: 'Navigation',
            url: './partials/tpl/settingsNavigation.tpl.html'
        }, {
            title: 'Three',
            url: './partials/tpl/settings3.tpl.html'

	}];
	$scope.currentTab = './partials/tpl/settingsUsers.tpl.html';
	
    $scope.onClickTab = function (tab) {
        $scope.currentTab = tab.url;
    }
    
    $scope.isActiveTab = function(tabUrl) {
        return tabUrl == $scope.currentTab;
    }

	$scope.cancelChanges = function(){
		$scope.init();
	}
	
	$scope.init = function(){
		$scope.getAllUsers();
		$scope.getRooms();
		$scope.getNavigation();
		$scope.usersChanged=0;
		$scope.roomsChanged=0;
		$scope.navChanged=0;
	}
	
	
	
	/* users section */
	
	$scope.getAllUsers = function() {
		$http.get('data/settings.php?action=getUsers')
			.success(function(data) {
				$scope.Users = data;
			});
	}
	 
	$scope.saveUsers = function(users){
		$http.get('data/settings.php?action=saveUsers&users='+JSON.stringify(users))
			.success(function(data) {
				$scope.usersChanged=0;
			});
	}
	 
	$scope.addUser = function(){
		var lastuserid = 0;
		var userid = 0;
		for(userid in $scope.Users) {
			lastuserid = userid;
		}
		var nextuserid = parseInt(lastuserid) + 1;
		$scope.Users[nextuserid]={'userid': nextuserid.toString()};
		$scope.usersChanged++;
	}
	 
	$scope.deleteUser = function(index){
		delete $scope.Users[index];
		$scope.usersChanged++;
	}
	
	$scope.usersChangedAdd = function(){
		$scope.usersChanged++;
	}
	
	
	/* end users section  */
	
	
	
	/* rooms section */
	
	$scope.getRooms = function() {
		$http.get('data/settings.php?action=getRooms')
			.success(function(data) {
				$scope.Rooms = data;
			});
	}	

	$scope.addRoom = function(){
		var lastroomid = 0;
		var room = 0;
		for(room in $scope.Rooms) {
			if(!isNaN(parseFloat(room)) && isFinite(room)){
				lastroomid = room;
			}
		}
		var nextroomid = parseInt(lastroomid) + 1;
		$scope.Rooms[nextroomid]={'roomId': nextroomid.toString()};
		$scope.roomsChanged++;
	}

	$scope.addRoomGroup = function(){
		var lastroomid = 0;
		var room = 0;
		for(room in $scope.Rooms['groups']) {
			if(!isNaN(parseFloat(room)) && isFinite(room)){
				lastroomid = room;
			}
		}
		var nextroomid = parseInt(lastroomid) + 1;
		$scope.Rooms['groups'][nextroomid]={'roomGroupId': nextroomid.toString()};
		$scope.navChanged++;
	}

	$scope.saveRooms = function(){
		// get room and addon info ready for saving
		/*
		$http.get('data/settings.php?action=saveRooms&data='+JSON.stringify(data))
			.success(function(data) {
				$scope.usersChanged=0;
			});
		*/
	}	
	
	$scope.deleteRoom = function(index){
		delete $scope.Rooms[index];
		$scope.roomsChanged++;
	}

	$scope.deleteRoomGroup = function(index){
		delete $scope.Rooms['groups'][index];
		$scope.roomsChanged++;
	}
	
	$scope.roomsChangedAdd = function(){
		$scope.roomsChanged++;
	}
	
	
	
	/*
	
	addons:
	
	need scan addon folder for new addons (add)
	
	save addons
	
	delete addons?  remove folder from dir?
	
need to add version to addons -->>   {addon}/{addon}.php  >>   {addon}=type.addonname.version-subversion-subversion   classname in {addon}.php remains addonname
	
	
	
	*/
	
	
	
	
	
	/* end rooms section */
	
	
	
	
	
	/* navigation section */	
	
	$scope.getNavigation = function() {
		$http.get('data/settings.php?action=getNavigation')
			.success(function(data) {
				$scope.Navigation = data;
			});
	}	

	$scope.addNavigation = function(){
		var lastnavid = 0;
		var nav = 0;
		for(nav in $scope.Navigation) {
			if(!isNaN(parseFloat(nav)) && isFinite(nav)){
				lastnavid = nav;
			}
		}
		var nextnavid = parseInt(lastnavid) + 1;
		$scope.Navigation[nextnavid]={'navid': nextnavid.toString()};
		$scope.navChanged++;
	}

	$scope.addNavigationGroup = function(){
		var lastnavid = 0;
		var nav = 0;
		for(nav in $scope.Navigation['groups']) {
			if(!isNaN(parseFloat(nav)) && isFinite(nav)){
				lastnavid = nav;
			}
		}
		var nextnavid = parseInt(lastnavid) + 1;
		$scope.Navigation['groups'][nextnavid]={'navgroupid': nextnavid.toString()};
		$scope.navChanged++;
	}	
	
	$scope.saveNavigation = function(){
		// get room and addon info ready for saving
		/*
		$http.get('data/settings.php?action=saveRooms&data='+JSON.stringify(data))
			.success(function(data) {
				$scope.usersChanged=0;
			});
		*/
	}	
	
	$scope.deleteNavigation = function(index){
		delete $scope.Navigation[index];
		$scope.navChanged++;
	}

	$scope.deleteNavigationGroup = function(index){
		delete $scope.Navigation['groups'][index];
		$scope.navChanged++;
	}

	$scope.navChangedAdd = function(){
		$scope.navChanged++;
	}	
	
	
	/* end navigation section */	
	
	
	


	$scope.init();	
	
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