'use strict';

app.factory('addonFunctions', function($http,spinnerService){
	return{
		powerOnAddon:function(addonid){
			spinnerService.add("powerOnAddon");
			$http.post('data/power.php?type=addon&option=on&addonid='+addonid);
		}
		,powerOffAddon:function(addonid){
			$http.post('data/power.php?type=addon&option=off&addonid='+addonid);
		}
		,powerOnRoom:function(room){
			spinnerService.add("powerOnRoom");
			$http.post('data/power.php?type=room&option=on&room='+room);
		}
		,powerOffRoom:function(room){
			$http.post('data/power.php?type=room&option=off&room='+room);
		}
		
		
		
		/* mediaplayer addons  */
		,sendMedia:function(sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
			$http.post('data/mediaSend.php?fromip='+sendFromAddonIP+'&fromaddon='+sendFromAddonID+'&toip='+sendToAddonIP+'&toaddon='+sendToAddonID);
		}
		,cloneMedia:function(sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
			$http.post('data/mediaSend.php?fromip='+sendFromAddonIP+'&fromaddon='+sendFromAddonID+'&toip='+sendToAddonIP+'&toaddon='+sendToAddonID+'&type=clone');
		}
		,startMedia:function(sendFromAddonIP,sendFromAddonID,sendToAddonIP,sendToAddonID){
			$http.post('data/mediaSend.php?fromip='+sendFromAddonIP+'&fromaddon='+sendFromAddonID+'&toip='+sendToAddonIP+'&toaddon='+sendToAddonID+'&type=start');
		}
	}
});