app.factory("spinnerService", function ($rootScope) {
  $rootScope.spinnerArray = [];
  let index = 0;
  let spinnerTimout = "";
  function resetTimeout() {
    clearTimeout(spinnerTimout);
    spinnerTimout = setTimeout(function () {
      clearTimeout(spinnerTimout);
      if ($rootScope.spinnerArray.length > 0) {
        $rootScope.spinnerArray = [];
        console.log("Spinner: clear all timeout");
        $rootScope.$digest();
      }
    }, 30000);
  }
  return {
    add(func = null, limit = 0) {
      resetTimeout();
      if (limit > 0) {
        index = $rootScope.spinnerArray.indexOf(func);
        if (index > -1) {
          /* already added */
          return;
        }
      }
      console.log(`Spinner: added ${func}`);
      $rootScope.spinnerArray.push(func);
    },
    remove(func = null) {
      if ($rootScope.spinnerArray.length > 0) {
        index = $rootScope.spinnerArray.indexOf(func);
        if (index > -1) {
          $rootScope.spinnerArray.splice(index, 1);
          console.log(`Spinner: removed ${func}`);
        }
        resetTimeout();
      } else {
        clearTimeout(spinnerTimout);
      }
    },
    clear() {
      console.log("Spinner: clear all");
      $rootScope.spinnerArray = [];
    }
  };
});
