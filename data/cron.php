<?php
require_once "startsession.php";
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
	} else {
		echo "failed";
		$log->LogWarn("No user session data from " . basename(__FILE__));
		exit;
	}
$log->LogDebug("User " . $_SESSION['username'] . " loaded " . basename(__FILE__) . " from " . $_SERVER['SCRIPT_FILENAME']);
$time = time();
try {
	$sql = "SELECT CCvalue FROM controlcenter WHERE CCsetting = 'lastcrontime' LIMIT 1";
	foreach ($configdb->query($sql) as $lastcrontime) {
		$lastcron = $lastcrontime['CCvalue'];
	}
} catch(PDOException $e)
	{
		echo "failed";
		$log->LogError("$e->getMessage()" . basename(__FILE__));
	}

$echoTakeover=0;
if($lastcron < ($time - 30)) {
	echo "takeover";
	$echoTakeover=1;
	$log->LogInfo("Cron taken over by user " . $_SESSION['username']);
} else if(($lastcron + 4) > $time) {
	echo "release";
	exit;
}
//////   Cron items


//  check addon alive status for all enabled users' addons that have been checked recently
try {
	$addons=array();
	$sql = "SELECT addons.*,settings.globalDisable,settings.controlWindow,info.* FROM rooms_addons as addons 
			LEFT JOIN rooms_addons_global_settings as settings ON addons.addonid = settings.addonid 
			LEFT JOIN rooms_addons_info as info ON addons.rooms_addonsid = info.rooms_addonsid
			WHERE addons.enabled ='1' AND settings.globalDisable='0';";
	foreach ($configdb->query($sql) as $row) {
		if(($row['lastCheck']+60) < $time && $row['lastCheck']!='') { continue; }
		$rooms_addonsid=$row['rooms_addonsid'];
		$addonid=$row['addonid'];
		$addonparts = explode(".",$row['addonid']);
		$addonName=$addonparts[1];
		$addonType=$addonparts[0];
		$ip=$row['ip'];
		$mac=$row['mac'];
		$statusorig=$row['device_alive'];
		if(file_exists("../addons/$addonid/$addonid.php") && $ip !='') {
			if(!isset(${$addonName})) {
				include "../addons/$addonid/$addonid.php";
				${$addonName} = new $addonName();
			}
			$vars = array();
			$vars['ip']=$ip;
			$vars['mac']=$mac;		
			${$addonName}->SetVariables($vars);
			$devicealive='';
			if($statusorig==1) {
				$devicealive=${$addonName}->PingApp($ip);
			} 
			if($statusorig==0 || $devicealive!='alive') {
				$devicealive=${$addonName}->Ping($ip);
			}
			if ($devicealive == "alive") {
				if($statusorig==0) {
					$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 1 WHERE rooms_addonsid = '$rooms_addonsid';");
				}
			} else {
				if($statusorig==1) {
					$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
				}
				continue;
			}
			
			
			
			
			if($addonType=='mediaplayer'){
				// need to standardize nowplayinginfo response in class files
				$nowPlayingInfo = ${$addonName}->GetPlayingItemInfo();
				//print_r($nowPlayingInfo);
				if(isset($nowPlayingInfo['title']) && $nowPlayingInfo['title']!='') {
					$title = $nowPlayingInfo['title'];
					if(isset($nowPlayingInfo['showtitle']) && $nowPlayingInfo['showtitle']!='') {
						$episode = "";
						if(isset($nowPlayingInfo['season']) && $nowPlayingInfo['season']!='' && isset($nowPlayingInfo['episode']) && $nowPlayingInfo['episode']!='') {
							$episode = " " . $nowPlayingInfo['season'] . "x" . $nowPlayingInfo['episode'];
						}
						$title = $nowPlayingInfo['showtitle'] . $episode . " - " . $nowPlayingInfo['title'];
					} elseif(isset($nowPlayingInfo['year']) && $nowPlayingInfo['year']!='') {
						$title = $nowPlayingInfo['title'] . " (" . $nowPlayingInfo['year'] . ")";
					}
					$thumbnail="";
					$fanart="";
					if(isset($nowPlayingInfo['thumbnail']) && $nowPlayingInfo['thumbnail']!='') {
						$thumbnail = $nowPlayingInfo['thumbnail'];
					}
					if(isset($nowPlayingInfo['fanart']) && $nowPlayingInfo['fanart']!='') {
						$fanart = $nowPlayingInfo['fanart'];
					}
					$type = $nowPlayingInfo['type'];
					$execquery = $configdb->exec("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, info, infoType, thumbnail, fanart) VALUES ('$rooms_addonsid','$title','$type','$thumbnail','$fanart')");
				} elseif($row['info']!='') {
					$execquery = $configdb->exec("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, info, infoType, thumbnail, fanart) VALUES ('$rooms_addonsid','','','','')");
				}
			}
		}
	}
} catch(PDOException $e)
	{
	$log->LogError("$e->getMessage()" . basename(__FILE__));
	}

//////  Cron items end

try {
	$execquery = $configdb->exec("INSERT OR REPLACE INTO controlcenter (CCid, CCsetting, CCvalue) VALUES (3,'lastcrontime','$time')");
} catch(PDOException $e)
	{
	$log->LogError("$e->getMessage()" . basename(__FILE__));
	}

if($echoTakeover===0) {
	echo "completed";
}
?>