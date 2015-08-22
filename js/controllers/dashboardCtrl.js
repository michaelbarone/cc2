'use strict';

app.controller('dashboardCtrl', ['$scope','loginService','$http','inform', function ($scope, loginService, $http, inform){
	$scope.txt='Dashboard';
	$scope.userdata = [];
	$scope.userdata.username=sessionStorage.getItem('username');
	$scope.userdata.userid=sessionStorage.getItem('userid');
	$scope.userdata.mobile=sessionStorage.getItem('mobile');	
	$scope.links = [];
	$scope.rooms = [];
	$scope.userdata.linkGroupSelected = '';
	$scope.userdata.currentRoom = 'noRoom';

    $http.get('data/getLinks.php')
		.success(function(data) {
			$scope.links = data;
		});

    $http.get('data/getRooms.php')
		.success(function(data) {
			$scope.rooms = data;
		});
		
	// check set currentRoom, check for cookies? then for homeRoom
	if(sessionStorage.getItem('currentRoom') && sessionStorage.getItem('currentRoom') != '') {
		$scope.userdata.currentRoom=sessionStorage.getItem('currentRoom');
	} else {
		$scope.userdata.currentRoom=sessionStorage.getItem('homeRoom');
	}
	
	$scope.changeRoom = function(room) {
		$scope.userdata.currentRoom=room;
		sessionStorage.setItem('currentRoom',room);
	};	
	
		
	$scope.loadLink = function(name,element) {
		document.getElementById(name).attributes['class'].value += ' loaded';
	};

    $scope.linkReOrder = function(linkgroup,index) {
		var theLi = document.getElementById(linkgroup + '-group');
		$(theLi).parent().prepend(theLi);
    };	


	$scope.testmessage = function() {

		inform.add('test');
	
		inform.add('Default', {
		  ttl: 120000, type: 'default'
		});

		inform.add('Primary', {
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
		
	$scope.logout=function(){
		loginService.logout();
	};
}])

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
