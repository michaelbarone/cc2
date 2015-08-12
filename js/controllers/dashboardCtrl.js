'use strict';

app.controller('dashboardCtrl', ['$scope','loginService','$http', function ($scope,loginService,$http,$sce){
	$scope.txt='Dashboard';
	$scope.username=sessionStorage.getItem('username');
	$scope.userid=sessionStorage.getItem('userid');
	$scope.links = [];
	$scope.rooms = [];
	$scope.userdata = [];
	$scope.userdata.linkGroupSelected = '';
	$scope.userdata.currentRoom = 'noRoom';
	
    $http.get('data/getLinks.php')
		.success(function(data) {
			$scope.links = data;
		});

    $http.get('data/getRooms.php')
		.success(function(data) {
			$scope.rooms = data;
			//$scope.rooms = JSON.stringify(data);
			//alert(JSON.stringify(data));
		});		
		
		
	$scope.loadLink = function(name,element) {
		document.getElementById(name).attributes['class'].value += ' loaded';
	};

    $scope.linkReOrder = function(linkgroup,index) {
		var theLi = document.getElementById(linkgroup + '-group');
		$(theLi).parent().prepend(theLi);
    };	
	
		
	$scope.logout=function(){
		loginService.logout();
	}
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
