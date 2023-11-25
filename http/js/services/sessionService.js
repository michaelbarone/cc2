app.factory("sessionService", [
  "$http",
  function ($http) {
    return {
      set(key, value) {
        return sessionStorage.setItem(key, value);
      },
      get(key) {
        return sessionStorage.getItem(key);
      },
      destroy(key) {
        $http.post("data/session_destroy.php");
      }
    };
  }
]);
