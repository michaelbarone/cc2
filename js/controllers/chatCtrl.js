'use strict';

app.chatController('chatCtrl', ['$scope','$timeout','loginService','$http','inform','Idle', function ($scope, $timeout, loginService, $http, inform, Idle){
	$scope.chatBoxOpen=false;
	if(sessionStorage.getItem('currentRoom') && sessionStorage.getItem('currentRoom') != '') {
		$scope.userdata.currentRoom=sessionStorage.getItem('currentRoom');
	}


	$scope.closeChat = function() {
		$scope.closeChatBox();
		$timeout(function() {
			$scope.$parent.chatOpen=false;
		}, 150);
	};

	$scope.closeChatBox = function() {
		$scope.chatBoxOpen=false;
	};
	
	$scope.chatWithRoom = function() {
		$scope.chatBoxOpen=true;
	};

	$scope.chatWithUser = function() {
		$scope.chatBoxOpen=true;
	};
	
	$scope.sendNewMessage = function() {
	};

}])