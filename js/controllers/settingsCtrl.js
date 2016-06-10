'use strict';

app.settingsController('settingsCtrl', ['$scope','$timeout','$http','inform','Idle', function ($scope, $timeout, $http, inform, Idle){
	$scope.userdata = [];
	$scope.Users = [];
	$scope.userdata.username=sessionStorage.getItem('username');
	$scope.userdata.userid=sessionStorage.getItem('userid');
	$scope.userdata.mobile=sessionStorage.getItem('mobile');




    $scope.tabs = [{
            title: 'Users',
            url: './partials/tpl/settingsUsers.tpl.html'		
        }, {
            title: 'One',
            url: './partials/tpl/settings1.tpl.html'
        }, {
            title: 'Two',
            url: './partials/tpl/settings2.tpl.html'
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

	
	
	
	
	/* users section */
	
	$scope.getAllUsers = function() {
		$http.get('data/settingsUsers.php?action=getUsers')
			.success(function(data) {
				$scope.Users = data;
			});
	}
	$scope.getAllUsers();
	$scope.usersChanged=0;
	 
	$scope.saveUsers = function(users){
		$scope.usersChanged=0;
		
		/*
		$http.get('data/settingsUsers.php?action=save&users='+users)
			.success(function(data) {
				if(data == "failed") {
					return;
				}
				if(data == "failedAuth"){
					loginService.logout();
					return;
				}
				return data;
			});*/
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
	
	
	/* end users section  */
	
	
	
	/* rooms section */
	
	
	
	
	
	/* end rooms section */
	
	
	
	
	
	/* navigation section */	
	
	
	
	
	/* end navigation section */	
	
	
	


	
	
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