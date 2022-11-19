app.factory("userService", function ($http, $location, sessionService, inform, cron, $timeout) {
  return {
    login(data) {
      const oldUID = sessionService.get("userid");
      const $promise = $http.post("data/session_login.php", data);
      $promise.then(function (msg) {
        if (!msg.data.failed) {
          if (msg.data.uid) {
            cron.start();
            inform.clear();
            inform.add(`Welcome, ${msg.data.username}`);
            $.each(msg.data, function (k, i) {
              sessionService.set(k, i);
              if (k == "homeRoom" && oldUID != msg.data.userid) {
                sessionService.set("currentRoom", i);
              }
            });
            $location.path("/dashboard");
          } else {
            inform.clear();
            inform.add("Incorrect Information", {
              ttl: 5000,
              type: "warning"
            });
            $location.path("/login");
            return "failed";
          }
        } else {
          inform.clear();
          inform.add("Incorrect Information", {
            ttl: 5000,
            type: "warning"
          });
          $location.path("/login");
          return "failed";
        }
      });
    },
    logout() {
      inform.clear();
      sessionService.destroy("uid");
      sessionStorage.removeItem("uid");
      sessionStorage.removeItem("username");
      sessionStorage.removeItem("passwordv");
      sessionStorage.removeItem("homeRoom");
      sessionStorage.removeItem("avatar");
      sessionStorage.removeItem("mobile");
      sessionStorage.removeItem("settingsAccess");
      $location.path("/login");
    },
    islogged() {
      const $checkSessionServer = $http.post("data/session_check.php");
      return $checkSessionServer;
    },
    setPassword(userid, password, currentPassword, activeUserid) {
      const data = {};
      data.set = "password";
      data.userid = userid;
      data.password = password;
      data.currentPassword = currentPassword;
      data.activeUserid = activeUserid;
      const $promise = $http.post("data/userSet.php", JSON.stringify(data));
      $promise.then(function (msg) {
        if (msg.data == "badPass") {
          inform.add("Current Password Didn't Match", {
            ttl: 4000,
            type: "warning"
          });
          return "wrongPassword";
        }
        if (msg.data == "failed") {
          inform.add("Failed to Set Password", {
            ttl: 4000,
            type: "danger"
          });
          return "failed";
        }
        if (msg.data == "success") {
          inform.add("Set Password Successfully", {
            ttl: 4000,
            type: "success"
          });
          return "success";
        }
      });
    },
    removePassword(userid, activeUserid) {
      const data = {};
      data.set = "removePassword";
      data.userid = userid;
      data.activeUserid = activeUserid;
      const $promise = $http.post("data/userSet.php", JSON.stringify(data));
      $promise.then(function (msg) {
        if (msg.data == "failed") {
          inform.add("Failed to Remove Password", {
            ttl: 4000,
            type: "danger"
          });
          return "failed";
        }
        if (msg.data == "success") {
          inform.add("Removed Password Successfully", {
            ttl: 4000,
            type: "success"
          });
          return "success";
        }
      });
    }
  };
});
