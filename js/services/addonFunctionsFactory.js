app.factory("addonFunctions", function ($http, spinnerService) {
  return {
    powerOnAddon(addonid) {
      $http.post(`data/power.php?type=addon&option=on&addonid=${addonid}`);
    },
    powerOffAddon(addonid) {
      $http.post(`data/power.php?type=addon&option=off&addonid=${addonid}`);
    },
    powerOnRoom(room) {
      $http.post(`data/power.php?type=room&option=on&room=${room}`);
    },
    powerOffRoom(room) {
      $http.post(`data/power.php?type=room&option=off&room=${room}`);
    },

    /* mediaplayer addons  */
    sendMedia(sendFromAddonIP, sendFromAddonID, sendToAddonIP, sendToAddonID) {
      $http.post(`data/mediaSend.php?fromip=${sendFromAddonIP}&fromaddon=${sendFromAddonID}&toip=${sendToAddonIP}&toaddon=${sendToAddonID}`);
    },
    cloneMedia(sendFromAddonIP, sendFromAddonID, sendToAddonIP, sendToAddonID) {
      $http.post(`data/mediaSend.php?fromip=${sendFromAddonIP}&fromaddon=${sendFromAddonID}&toip=${sendToAddonIP}&toaddon=${sendToAddonID}&type=clone`);
    },
    startMedia(sendFromAddonIP, sendFromAddonID, sendToAddonIP, sendToAddonID) {
      $http.post(`data/mediaSend.php?fromip=${sendFromAddonIP}&fromaddon=${sendFromAddonID}&toip=${sendToAddonIP}&toaddon=${sendToAddonID}&type=start`);
    }
  };
});
