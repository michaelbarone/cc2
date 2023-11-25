app.chatController("chatCtrl", [
  "$scope",
  "$timeout",
  "$http",
  "inform",
  "Idle",
  function ($scope, $timeout, $http, inform, Idle) {
    $scope.chatBoxOpen = false;
    if (sessionStorage.getItem("currentRoom") && sessionStorage.getItem("currentRoom") != "") {
      $scope.userdata.currentRoom = sessionStorage.getItem("currentRoom");
    }
    $scope.currenttime = Date.now() / 1000 || 0;

    $scope.users = [];

    $http.get("data/getUsers.php?type=chat").success(function (data) {
      $scope.users = data;
    });

    $scope.resetChattingWith = function () {
      $scope.chattingWith = false;
      $scope.$parent.chattingWith = $scope.chattingWith;
      $scope.chattingWithSendTo = false;
      $scope.chattingWithType = false;
      $scope.newMessage = "";
    };
    $scope.resetChattingWith();

    $scope.closeChat = function () {
      $scope.closeChatBox();
      $timeout(function () {
        $scope.$parent.chatOpen = false;
      }, 150);
    };

    $scope.closeChatBox = function () {
      $scope.chatBoxOpen = false;
      $scope.resetChattingWith();
    };

    $scope.chatWithRoom = function (roomid) {
      $scope.closeChatBox();
      $scope.chatBoxOpen = true;
      $scope.chattingWith = $scope.$parent.room_addons[0][roomid][0].roomName;
      $scope.$parent.chattingWith = $scope.chattingWith;
      $scope.chattingWithSendTo = roomid;
      $scope.chattingWithType = "room";
    };

    $scope.chatWithUser = function (userid) {
      $scope.closeChatBox();
      $scope.chatBoxOpen = true;
      $scope.chattingWith = $scope.users[userid].username;
      $scope.$parent.chattingWith = $scope.chattingWith;
      $scope.chattingWithSendTo = userid;
      $scope.chattingWithType = "user";
    };

    $scope.chatWithGroup = function (userids) {
      $scope.closeChatBox();
      $scope.chatBoxOpen = true;
      //alert(userid);
      // check for users in userids
      $scope.chattingWith = $scope.users[userid].username;
      $scope.$parent.chattingWith = $scope.chattingWith;
      $scope.chattingWithSendTo = userids;
      $scope.chattingWithType = "group";
    };

    $scope.sendNewMessage = function (themsg) {
      const xsrf = $.param({ message: themsg, from: $scope.userdata.userid, to: $scope.chattingWithSendTo, type: $scope.chattingWithType });
      $http({
        method: "POST",
        url: "data/chatSendMessage.php",
        data: xsrf,
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
      });
      $scope.newMessage = "";
    };

    /*   update cycle to refresh only when chattingWith != false

	$scope.getNewMessages = function(){
		if( Idle.idling() === true || $scope.chattingWith === false) {
			$timeout(function() {
				$scope.getNewMessages();
			}, 2000);
		} else {
			$http.get('data/chatGetMessages.php?cw=' + $scope.chattingWith)
				.success(function(data) {
					if(data == "failed") {
						return;
					}
					// need to figure out how to append data, not replace
					//$scope.room_addons=data;
					$timeout(function() {
						$scope.getNewMessages();
					}, 4000);
				});
		}
	};
	$timeout(function() {
		$scope.getNewMessages();
	}, 1000);

*/
  }
]);
