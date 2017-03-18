'use strict';

app.settingsController('settingsCtrl', ['$rootScope','$scope','$timeout','$location','loginService','$http','inform','Idle','$route','spinnerService','ModalService', function ($rootScope, $scope, $timeout, $location, loginService, $http, inform, Idle, $route, spinnerService, ModalService){
	spinnerService.clear();
	$scope.userdata = [];
	$scope.userdata.currentpage = "settings";
	$scope.Users = [];
	$scope.Addons = [];
	$scope.Rooms = [];
	$scope.loaded=0;
	$scope.userdata.username=sessionStorage.getItem('username');
	$scope.userdata.userid=sessionStorage.getItem('userid');
	$scope.userdata.mobile=sessionStorage.getItem('mobile');
	$scope.userdata.avatar=sessionStorage.getItem('avatar');
	
    $scope.tabs = [{
			title: 'Users',
			url: './partials/tpl/settingsUsers.tpl.html'		
		}, {
			title: 'Addons',
			url: './partials/tpl/settingsAddons.tpl.html'
		}, {
			title: 'Rooms',
			url: './partials/tpl/settingsRooms.tpl.html'
		}, {
			title: 'Navigation',
			url: './partials/tpl/settingsNavigation.tpl.html'
		}, {
			title: 'Server Info',
			url: './partials/servercheck.html'
		}];
	$scope.currentTab = './partials/tpl/settingsUsers.tpl.html';
	
    $scope.onClickTab = function (tab) {
		$scope.CheckLogged();
		$scope.currentTab = tab.url;
    }
    
	$scope.CheckLogged = function() {
		var connected=loginService.islogged();
		connected.then(function(msg){
			if(msg.data==="failedAuth" || !msg.data) {
				$location.path('/login');
			} else {
				return msg.data;
			}
		});		
	}
	
    $scope.isActiveTab = function(tabUrl) {
        return tabUrl == $scope.currentTab;
    }

	$scope.cancelChanges = function(){
		$scope.init();
	}
	

	$scope.init = function(){
		$scope.getAllUsers();
		$scope.getAddons();
		$scope.getRooms();
		$scope.getNavigation();
		$scope.usersChanged=0;
		$scope.addonsChanged=0;
		$scope.roomsChanged=0;
		$scope.navChanged=0;
		$scope.loaded=1;
	}

	
	
	/* users section */
	
	$scope.getAllUsers = function() {
		$http.get('data/settings.php?action=getUsers')
			.success(function(data) {
				if(data[$scope.userdata.userid]['settingsAccess']==='0'){
					$location.path('/dashboard');
					return;
				}
				$scope.Users = data;
			});
	}

	$scope.saveUser = function(user){
		$scope.CheckLogged();
		$http.get('data/settings.php?action=saveUser&user='+JSON.stringify(user))
			.success(function(data) {
			});
	}
	
	$scope.saveUsers = function(users){
		$scope.CheckLogged();
		$http.get('data/settings.php?action=saveUsers&users='+JSON.stringify(users))
			.success(function(data) {
			});
	}

	$scope.editUser = function(user,scope=$scope){
		ModalService.showModal({
			templateUrl: "./partials/tpl/modalSettingsUsers.html"
			, controller: "ModalController"
			,inputs: {
				data: user,
		    }
			, scope: scope
		}).then(function(modal) {
			$scope.modalOpen=1;
            modal.close.then(function() {
				$scope.modalOpen=0;
				$scope.init();
            });
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
		$scope.editUser(nextuserid);

		
		
		/*
		ModalService.showModal({
			templateUrl: "./partials/tpl/modalSettingsUsers.html"
			, controller: "ModalController"
			,inputs: {
				data: "",
		    }
			, scope: $scope
		}).then(function(modal) {
			$scope.modalOpen=1;
            modal.close.then(function() {
				$scope.modalOpen=0;
            });
		});
		*/
	}
	 
	$scope.deleteUser = function(item){
		console.log(item);
		//console.log("try to delete userid "+item);
		//var index = $scope.Users.indexOf(item);
		//$rootScope.spinnerArray.splice(index, 1);

		//	$scope.Users.splice(item, 1);

		//delete $scope.Users[item];
		var user = $scope.Users[item];
		$scope.CheckLogged();
		$http.get('data/settings.php?action=deleteUser&user='+JSON.stringify(user))
			.success(function(data) {
			});
	}
	
	$scope.usersChangedAdd = function(){
		$scope.usersChanged++;
	}
	
	
	/* end users section  */


	/* addons section */

	$scope.getAddons = function() {
		$http.get('data/settings.php?action=getAddons')
			.success(function(data) {
				$scope.Addons = data;
			});
	}	
	
	$scope.scanAddons = function(){
		$http.get('data/settings.php?action=scanAddons')
			.success(function(data) {
			});		
	}
	
	$scope.saveAddon = function(addon){
		$scope.CheckLogged();
		$http.get('data/settings.php?action=saveAddon&addon='+JSON.stringify(addon))
			.success(function(data) {
			});		
	}

	$scope.editAddon = function(addon,scope=$scope){
		$scope.CheckLogged();
		ModalService.showModal({
			templateUrl: "./partials/tpl/modalSettingsAddons.html"
			, controller: "ModalController"
			,inputs: {
				data: addon,
		    }
			, scope: scope
		}).then(function(modal) {
			$scope.modalOpen=1;
            modal.close.then(function() {
				$scope.modalOpen=0;
				$scope.init();
            });
		});
	}

	$scope.addonsChangedAdd = function(){
		$scope.addonsChanged++;
	}
	
	$scope.uploadAddon = function(){
		$scope.CheckLogged();
		
	}
	
	/*
	
	addons:
	
	need scan addon folder for new addons (add)
	
	save addons
	
	delete addons?  remove folder from dir?
	
need to add version to addons -->>   {addon}/{addon}.php  >>   {addon}=type.addonname.version-subversion-subversion   classname in {addon}.php remains addonname
	
	
	
	*/




	/* end addons section  */	
	
	
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
		$scope.CheckLogged();
		var thisdata = $scope.Rooms;
		$http.get('data/settings.php?action=saveRooms&data='+JSON.stringify(thisdata))
			.success(function() {
				$scope.roomsChanged=0;
			});
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
		$scope.CheckLogged();
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