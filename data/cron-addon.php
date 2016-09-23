<?php
//if(!isset($_POST['rooms_addonsid']){ exit; }
$cronaddon=1;
require_once "startsession.php";
$rooms_addonsid=$_POST['rooms_addonsid'];
$addonid=$_POST['addonid'];
$addonparts = explode(".",$_POST['addonid']);
$addonName=$addonparts[1];
$addonType=$addonparts[0];
$ip=$_POST['ip'];
$mac=$_POST['mac'];
$statusorig=$_POST['device_alive'];
if(file_exists("../addons/$addonid/$addonType.$addonName.php") && $ip !='') {
	if(!isset(${$addonName})) {
		include "../addons/$addonid/$addonType.$addonName.php";
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
			$time = $nowPlayingInfo['time'];
			$statement = $configdb->prepare("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, info, infoType, thumbnail, fanart, time) VALUES (:rooms_addonsid,:title,:type,:thumbnail,:fanart,:time)");
			try {
				$statement->execute(array(':rooms_addonsid'=>$rooms_addonsid,
				':title'=>$title,
				':type'=>$type,
				':thumbnail'=>$thumbnail,
				':fanart'=>$fanart,
				':time'=>$time
				));
			} catch(PDOException $e) {
				$log->LogError("$e->getMessage()" . basename(__FILE__));
				return "Statement failed: " . $e->getMessage();
			}
		} elseif($_POST['info']!='') {
			$execquery = $configdb->exec("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, info, infoType, thumbnail, fanart, time) VALUES ('$rooms_addonsid','','','','','')");
		}
	}
}


?>