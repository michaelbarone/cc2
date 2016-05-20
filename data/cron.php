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
	$sql = "SELECT addons.*,details.globalDisable,details.controlWindow,info.* FROM rooms_addons as addons 
			LEFT JOIN rooms_addons_details as details ON addons.addonid = details.addonid 
			LEFT JOIN rooms_addons_info as info ON addons.rooms_addonsid = info.rooms_addonsid
			WHERE addons.enabled ='1' AND details.globalDisable='0';";
	foreach ($configdb->query($sql) as $row) {
		if(($row['lastCheck']+60) < $time && $row['lastCheck']!='') { continue; }
		// create array of addons that can run custom info call
		$addons[$row['rooms_addonsid']]['rooms_addonsid']=$row['rooms_addonsid'];
		$addonparts = explode(".",$row['addonid']);
		$addons[$row['rooms_addonsid']]['addontype']=$addonparts[0];
		$addons[$row['rooms_addonsid']]['addonname']=$addonparts[1];	
		$addons[$row['rooms_addonsid']]['addonid']=$row['addonid'];
		$addons[$row['rooms_addonsid']]['ip']=$row['ip'];
		$addons[$row['rooms_addonsid']]['mac']=$row['mac'];
		$addons[$row['rooms_addonsid']]['info']=$row['info'];
		$addons[$row['rooms_addonsid']]['device_alive']=$row['device_alive'];
		//  set below   $addons[$row['rooms_addonsid']]['device_alive']=$row['device_alive'];
		
		
		$rooms_addonsid = $row['rooms_addonsid'];
		$statusorig = $row['device_alive'];
		
		
		/////
		if($row['ip'] != '') {
			/*
			$disallowed = array('http://', 'https://');
			foreach($disallowed as $d) {
				if(strpos($row['ip'], $d) === 0) {
				   $thisip = strtok(str_replace($d, '', $row['ip']),':');
				}
			}
			if(strpos($thisip, "/") != false) {
				$thisip = substr($thisip, 0, strpos($thisip, "/"));
			}
			if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
				$pingresult = exec("ping -n 1 -w 1 $thisip", $output, $status);
				// echo 'This is a server using Windows!';
			} else {
				$pingresult = exec("/bin/ping -c1 -w1 $thisip", $outcome, $status);
				// echo 'This is a server not using Windows!';
			}
			if ($status == "0") {
				if($statusorig==0) {
					$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 1 WHERE rooms_addonsid = '$rooms_addonsid';");
				}
				$addons[$row['rooms_addonsid']]['device_alive']="1";
				//$status = "alive";
			} else {
				if($statusorig==1) {
					$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
				}
				//$status = "dead";
				$addons[$row['rooms_addonsid']]['device_alive']="0";
			}*/
		} else {
			if($statusorig!=0) {			
				$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
			}
		}
	}
} catch(PDOException $e)
	{
	$log->LogError("$e->getMessage()" . basename(__FILE__));
	}




////// call addon custom info php
foreach($addons as $addon) {
	$rooms_addonsid=$addon['rooms_addonsid'];
	$addonid=$addon['addonid'];
	$addonName=$addon['addonname'];
	$ip=$addon['ip'];
	$mac=$addon['mac'];
	$statusorig=$addon['device_alive'];
	if(file_exists("../addons/$addonid/$addonid.php") && $ip !='') {
		if(!isset(${$addonName})) {
			include "../addons/$addonid/$addonid.php";
			${$addonName} = new $addonName();
		}
		$vars = array();
		$vars['ip']=$ip;
		$vars['mac']=$mac;		
		${$addonName}->SetVariables($vars);


		$devicealive=${$addonName}->Ping($ip);
		//echo $addonid . $addonName . $ip . $devicealive . $statusorig . "<br><br>";
		if ($devicealive == "alive") {
			if($statusorig==0) {
				$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 1 WHERE rooms_addonsid = '$rooms_addonsid';");
			}
			$addon['device_alive']="1";
		} else {
			if($statusorig==1) {
				$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
			}
			$addon['device_alive']="0";
		}		
		if($addon['device_alive']===0){ continue; }


	
		if($addon['addontype']=='mediaplayer'){
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
			} elseif($addon['info']!='') {
				$execquery = $configdb->exec("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, info, infoType, thumbnail, fanart) VALUES ('$rooms_addonsid','','','','')");
			}
		}
	}
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