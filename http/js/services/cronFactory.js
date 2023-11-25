app.factory("cron", [
  "$http",
  "$timeout",
  "inform",
  "Idle",
  "spinnerService",
  "$rootScope",
  "$location",
  function ($http, $timeout, inform, Idle, spinnerService, $rootScope, $location) {
    if (!$rootScope.systemInfo) {
      $rootScope.systemInfo = {};
    }
    if (!$rootScope.systemInfo[0]) {
      $rootScope.systemInfo[0] = {};
    }

    const cronVars = {};

    function resetCronVars() {
      cronVars.cronStop = 0;
      cronVars.cronKeeper = 0;
      cronVars.informSystemVersionDifferentCron = "";
      cronVars.informNoServerConnectionCron = "";
      $rootScope.cronRunning = 0;
      if ($rootScope.debug == 1) {
        console.log("cronFactory - reset cronVars:");
        console.log(cronVars);
      }
    }

    function runCron(firstrun = 0, cronVars) {
      if (firstrun != 0) {
        resetCronVars();
      }
      if ($rootScope.cronRunning && $rootScope.cronRunning == 1) {
        return;
      }
      if (Idle.idling() === true) {
        if ($rootScope.debug == 1) {
          console.log("cronFactory - Cron Start Idle");
        }
        $timeout(function () {
          cronVars.cronKeeper = 0;
          runCron(0, cronVars);
        }, 5000);
      } else {
        if ($rootScope.testrun == 1 || cronVars.cronStop > 0) {
          console.log("Cron Stopped");
          return;
        }
        if ($rootScope.debug == 1) {
          console.log("cronFactory - Cron Start");
        }
        $rootScope.cronRunning = 1;
        $http
          .get("data/cron.php")
          .success(function (data) {
            if (data == "failed") {
              if ($rootScope.debug == 1) {
                console.log("cronFactory - Cron Stopped data Failed");
              }
              cronVars.cronStop = 1;
              return;
            }
            if (data[0].status == "takeover") {
              cronVars.cronKeeper = "1";
            } else if (data[0].status == "completed") {
              cronVars.cronKeeper = "0";
            }
            if ($rootScope.systemInfo[0].ccversion && data[0].ccversion != $rootScope.systemInfo[0].ccversion) {
              /* system version is different from browser cache, refresh browser  */
              if ($rootScope.debug == 1) {
                console.log("cronFactory - Cron System Version Different from Browser Cache");
              }
              inform.remove(cronVars.informSystemVersionDifferentCron);
              cronVars.informSystemVersionDifferentCron = inform.add(
                "System has been updated. <a href'#' class='btn btn-danger' onclick='location.reload(true);return false;'>Refresh</a>",
                {
                  ttl: 15000,
                  type: "danger",
                  html: true
                }
              );
            } else {
              $rootScope.systemInfo = data;
              inform.remove(cronVars.informNoServerConnectionCron);
            }
          })
          .error(function () {
            if ($rootScope.debug == 1) {
              console.log("cronFactory - Cron data return error");
            }
            cronVars.cronKeeper = "0";
            $rootScope.cronRunning = 0;
            inform.remove(cronVars.informNoServerConnectionCron);
            cronVars.informNoServerConnectionCron = inform.add("No Connection to Server", {
              ttl: 10000,
              type: "danger"
            });
          })
          .finally(function () {
            if ($rootScope.debug == 1) {
              console.log("cronFactory - Cron Complete");
              console.log(cronVars);
            }
            if (cronVars.cronKeeper == "1") {
              cronVars.informSystemVersionDifferentCron = "";
              cronVars.informNoServerConnectionCron = "";
              $timeout(function () {
                $rootScope.cronRunning = 0;
                runCron(0, cronVars);
              }, 3000);
            } else if ($location.path() == "/login") {
              resetCronVars();
            } else {
              $timeout(function () {
                $rootScope.cronRunning = 0;
                runCron(0, cronVars);
              }, 6000);
            }
          });
      }
    }

    return {
      start() {
        $timeout(function () {
          runCron(1, cronVars);
        }, 1500);
      },
      stop() {
        cronVars.cronStop = 1;
      }
    };
  }
]);
