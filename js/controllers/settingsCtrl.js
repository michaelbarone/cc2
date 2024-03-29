app.settingsController("settingsCtrl", [
  "$rootScope",
  "$scope",
  "$timeout",
  "$location",
  "userService",
  "$http",
  "inform",
  "Idle",
  "$route",
  "spinnerService",
  "ModalService",
  function ($rootScope, $scope, $timeout, $location, userService, $http, inform, Idle, $route, spinnerService, ModalService) {
    spinnerService.clear();
    $scope.userdata = [];
    $scope.userdata.currentpage = "settings";
    $scope.Users = [];
    $scope.Addons = [];
    $scope.Rooms = [];
    $scope.loaded = 0;
    $scope.userdata.username = sessionStorage.getItem("username");
    $scope.userdata.passwordv = sessionStorage.getItem("passwordv");
    $scope.userdata.userid = sessionStorage.getItem("userid");
    $scope.userdata.mobile = sessionStorage.getItem("mobile");
    $scope.userdata.avatar = sessionStorage.getItem("avatar");
    $scope.userdata.settingsAccess = sessionStorage.getItem("settingsAccess");

    if ($scope.userdata.settingsAccess != "1") {
      $location.path("/dashboard");
      return;
    }
    if ($scope.userdata.username == "" || $scope.userdata.username == null) {
      userService.logout();
      return;
    }

    /* also in other controller until this is pulled into the db  */
    $scope.colors = ["blue", "gray", "green", "maroon", "navy", "olive", "orange", "purple", "red", "silver", "teal", "white", "lime", "aqua", "fuchsia", "yellow"];

    $scope.showModal = function (data, type) {
      spinnerService.add("showModal");

      if (type === "addon") {
        const turl = "./partials/tpl/modalAddonInfo.html";
      } else if (type === "userpref") {
        const turl = "./partials/tpl/modalUserPreferences.html";
      }

      ModalService.showModal({
        templateUrl: turl,
        controller: "ModalController",
        inputs: {
          data
        },
        scope: $scope
      }).then(function (modal) {
        $scope.modalOpen = 1;
        spinnerService.remove("showModal");
        modal.close.then(function () {
          $scope.modalOpen = 0;
        });
      });
    };

    $scope.tabs = [
      {
        title: "Users",
        url: "./partials/tpl/settingsUsers.tpl.html"
      },
      {
        title: "Addons",
        url: "./partials/tpl/settingsAddons.tpl.html"
      },
      {
        title: "Rooms",
        url: "./partials/tpl/settingsRooms.tpl.html"
      },
      {
        title: "Navigation",
        url: "./partials/tpl/settingsNavigation.tpl.html"
      },
      {
        title: "Server Info",
        url: "./partials/servercheck.html"
      }
    ];
    $scope.currentTab = "./partials/tpl/settingsUsers.tpl.html";

    $scope.onClickTab = function (tab) {
      $scope.CheckLogged();
      $scope.currentTab = tab.url;
    };

    $scope.CheckLogged = function () {
      const connected = userService.islogged();
      connected.then(function (msg) {
        if (msg.data != "passedAuth" || !msg.data) {
          $location.path("/login");
        } else {
          return msg.data;
        }
      });
    };

    $scope.isActiveTab = function (tabUrl) {
      return tabUrl == $scope.currentTab;
    };

    $scope.cancelChanges = function () {
      $scope.init();
    };

    $scope.init = function () {
      $scope.CheckLogged();
      $scope.getAllUsers();
      $timeout(function () {
        $scope.getAddons();
      }, 10);
      $timeout(function () {
        $scope.getRooms();
      }, 50);
      $timeout(function () {
        $scope.getNavigation();
      }, 100);
      $scope.usersChanged = 0;
      $scope.addonsChanged = 0;
      $scope.roomsChanged = 0;
      $scope.navChanged = 0;
      $timeout(function () {
        $scope.loaded = 1;
      }, 500);
    };

    /* users section */

    $scope.getAllUsers = function () {
      $http.get("data/settings.php?action=getUsers").success(function (data) {
        $scope.Users = data;
      });
    };

    $scope.saveUser = function (user) {
      $scope.CheckLogged();
      $http.get(`data/settings.php?action=saveUser&user=${JSON.stringify(user)}`).success(function (data) {
        // empty
      });
    };

    /*
	$scope.saveUsers = function(users){
		$scope.CheckLogged();
		$http.get('data/settings.php?action=saveUsers&users='+JSON.stringify(users))
			.success(function(data) {
			});
	}
	*/

    $scope.editUser = function (user, scope = $scope) {
      ModalService.showModal({
        templateUrl: "./partials/tpl/modalSettingsUsers.html",
        controller: "ModalController",
        inputs: {
          data: user
        },
        scope
      }).then(function (modal) {
        $scope.modalOpen = 1;
        modal.close.then(function () {
          $scope.modalOpen = 0;
          $scope.init();
        });
      });
    };

    $scope.addUser = function () {
      let lastuserid = 0;
      let userid = 0;
      for (userid in $scope.Users) {
        lastuserid = userid;
      }
      const nextuserid = parseInt(lastuserid) + 1;
      $scope.Users[nextuserid] = { userid: nextuserid.toString() };
      $scope.editUser(nextuserid);
    };

    $scope.deleteUser = function (item) {
      //console.log(item);
      //console.log("try to delete userid "+item);
      //var index = $scope.Users.indexOf(item);
      //$rootScope.spinnerArray.splice(index, 1);

      //	$scope.Users.splice(item, 1);

      //delete $scope.Users[item];
      const user = $scope.Users[item];
      $scope.CheckLogged();
      $http.get(`data/settings.php?action=deleteUser&user=${JSON.stringify(user)}`).success(function (data) {
        // empty
      });
    };

    $scope.usersChangedAdd = function () {
      $scope.usersChanged++;
    };

    /* password reset modal
		also in dashboardCtrl
	*/
    $scope.setPassword = function (userid, password, currentPassword, activeUserid) {
      $scope.CheckLogged();
      userService.setPassword(userid, password, currentPassword, activeUserid);
      if (userid == activeUserid) {
        $scope.userdata.passwordv = "2";
      }
      $timeout(function () {
        $scope.getAllUsers();
      }, 500);
    };
    $scope.removePassword = function (userid, activeUserid) {
      $scope.CheckLogged();
      userService.removePassword(userid, activeUserid);
      if (userid == activeUserid) {
        $scope.userdata.passwordv = "0";
      }
      $timeout(function () {
        $scope.getAllUsers();
      }, 500);
    };

    $scope.setPasswordModal = function (data) {
      spinnerService.add("showModal");
      ModalService.showModal({
        templateUrl: "./partials/tpl/modalPasswordSet.html",
        controller: "ModalController",
        inputs: {
          data
        },
        scope: $scope
      }).then(function (modal) {
        $scope.modalOpen = 1;
        spinnerService.remove("showModal");
        modal.close.then(function () {
          $scope.modalOpen = 0;
        });
      });
    };

    /* end users section  */

    /* addons section */

    $scope.getAddons = function () {
      $http.get("data/settings.php?action=getAddons").success(function (data) {
        $scope.Addons = data;
      });
    };

    $scope.scanAddons = function () {
      $scope.CheckLogged();
      $http
        .get("data/settings.php?action=scanAddons")
        .success(function (data) {
          // empty
        })
        .finally(function () {
          $scope.getAddons();
        });
    };

    /*  direct link file for now, dont need this function
	$scope.downloadAddon = function(addon){
		$scope.CheckLogged();
		$http.get('data/settings.php?action=downloadAddon&addon='+JSON.stringify(addon))
			.success(function(data) {
			}).finally(function(){
			});
	}
	*/

    $scope.saveAddon = function (addon) {
      $scope.CheckLogged();
      $http.get(`data/settings.php?action=saveAddon&addon=${JSON.stringify(addon)}`).success(function (data) {
        // empty
      });
    };

    $scope.editAddon = function (addon, scope = $scope) {
      $scope.CheckLogged();
      ModalService.showModal({
        templateUrl: "./partials/tpl/modalSettingsAddons.html",
        controller: "ModalController",
        inputs: {
          data: addon
        },
        scope
      }).then(function (modal) {
        $scope.modalOpen = 1;
        modal.close.then(function () {
          $scope.modalOpen = 0;
          $scope.init();
        });
      });
    };

    $scope.addonsChangedAdd = function () {
      $scope.addonsChanged++;
    };

    $scope.uploadAddon = function () {
      $scope.CheckLogged();
    };

    /* end addons section  */

    /* rooms section */

    $scope.getRooms = function () {
      $http.get("data/settings.php?action=getRooms").success(function (data) {
        $scope.Rooms = data;
      });
    };

    $scope.addRoom = function () {
      let lastroomid = 0;
      let room = 0;
      for (room in $scope.Rooms) {
        if (!isNaN(parseFloat(room)) && isFinite(room)) {
          lastroomid = room;
        }
      }
      const nextroomid = parseInt(lastroomid) + 1;
      $scope.Rooms[nextroomid] = { roomId: nextroomid.toString() };
      $scope.editRoom(nextroomid);
      //$scope.roomsChanged++;
    };

    $scope.editRoom = function (room, scope = $scope) {
      $scope.roomsChanged = 0;
      ModalService.showModal({
        templateUrl: "./partials/tpl/modalSettingsRooms.html",
        controller: "ModalController",
        inputs: {
          data: room
        },
        scope
      }).then(function (modal) {
        $scope.modalOpen = 1;
        modal.close.then(function () {
          $scope.modalOpen = 0;
          $scope.init();
        });
      });
    };

    $scope.saveRoom = function (data) {
      $scope.CheckLogged();
      $http.get(`data/settings.php?action=saveRoom&data=${JSON.stringify(data)}`).success(function (data) {
        $scope.roomsChanged = 0;
      });
    };

    $scope.saveRooms = function () {
      $scope.CheckLogged();
      const data = $scope.Rooms;
      $http.get(`data/settings.php?action=saveRooms&data=${JSON.stringify(data)}`).success(function (data) {
        $scope.roomsChanged = 0;
      });
    };

    $scope.deleteRoom = function (data) {
      $scope.CheckLogged();
      $http.get(`data/settings.php?action=deleteRoom&data=${JSON.stringify(data)}`).success(function (data) {
        // empty
      });
    };

    $scope.roomsChangedAdd = function () {
      $scope.roomsChanged++;
    };

    $scope.roomMenuOrderChange = function (func, roomId) {
      if (func == "up") {
        $scope.Rooms[roomId].roomOrder = parseInt($scope.Rooms[roomId].roomOrder, 10) - 1;
      } else if (func == "down") {
        $scope.Rooms[roomId].roomOrder = parseInt($scope.Rooms[roomId].roomOrder, 10) + 1;
      }
    };

    /* end rooms section */

    /* navigation section */

    $scope.getNavigation = function () {
      $http.get("data/settings.php?action=getNavigation").success(function (data) {
        $scope.Navigation = data;
      });
    };

    $scope.addNavigation = function () {
      let lastnavid = 0;
      let nav = 0;
      for (nav in $scope.Navigation) {
        if (!isNaN(parseFloat(nav)) && isFinite(nav)) {
          lastnavid = nav;
        }
      }
      const nextnavid = parseInt(lastnavid) + 1;
      $scope.Navigation[nextnavid] = { navid: nextnavid.toString() };
      $scope.editNavigation(nextnavid);
    };

    $scope.editNavigation = function (navigation, scope = $scope) {
      ModalService.showModal({
        templateUrl: "./partials/tpl/modalSettingsNavigation.html",
        controller: "ModalController",
        inputs: {
          data: navigation
        },
        scope
      }).then(function (modal) {
        $scope.modalOpen = 1;
        modal.close.then(function () {
          $scope.modalOpen = 0;
          $scope.init();
        });
      });
    };

    $scope.addNavigationGroup = function () {
      let lastnavid = 0;
      let nav = 0;
      for (nav in $scope.Navigation.groups) {
        if (!isNaN(parseFloat(nav)) && isFinite(nav)) {
          lastnavid = nav;
        }
      }
      const nextnavid = parseInt(lastnavid) + 1;
      $scope.Navigation.groups[nextnavid] = { navgroupid: nextnavid.toString() };
      $scope.navChanged++;
    };

    $scope.saveNavigation = function (Navigation) {
      $scope.CheckLogged();
      $http.get(`data/settings.php?action=saveNavigation&data=${JSON.stringify(Navigation)}`).success(function (data) {
        $scope.navChanged = 0;
      });
    };

    $scope.deleteNavigation = function (Navigation) {
      $scope.CheckLogged();
      $http.get(`data/settings.php?action=deleteNavigation&Navigation=${JSON.stringify(Navigation)}`).success(function (data) {
        // empty
      });
    };

    $scope.deleteNavigationGroup = function (index) {
      delete $scope.Navigation.groups[index];
      $scope.navChanged++;
    };

    $scope.navChangedAdd = function () {
      $scope.navChanged++;
    };

    /* end navigation section */

    $scope.init();

    $scope.logout = function () {
      userService.logout();
    };

    $scope.testmessage = function ($scope) {
      inform.add("test");
      inform.add("Default", {
        ttl: 120000,
        type: "default"
      });
      inform.add("Primary with long text string to se asdhafsdifjaskjdf a skdjf laskdjflkajsdf alksdj flkasjdf", {
        ttl: 120000,
        type: "primary"
      });
      inform.add("Info", {
        ttl: 120000,
        type: "info"
      });
      inform.add("Success", {
        ttl: 120000,
        type: "success"
      });
      inform.add("Warning", {
        ttl: 120000,
        type: "warning"
      });
      inform.add("Danger", {
        ttl: 120000,
        type: "danger"
      });
    };
  }
]);
