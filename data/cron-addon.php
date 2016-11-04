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
	if(!isset(${$addonid})) {
		include "../addons/$addonid/$addonType.$addonName.php";
		${$addonid} = new $addonName();
	}
	$count=0;
	$vars = array();
	$vars['ip']=$ip;
	$vars['mac']=$mac;
	${$addonid}->SetVariables($vars);
	$devicealive=array();
	if($statusorig==1) {
		$devicealive=json_decode(${$addonid}->PingApp($ip), true);
	}
	if($statusorig==0 || (isset($devicealive['status']) && $devicealive['status']!='alive')) {
		if(isset($devicealive['pingApp']) && $devicealive['pingApp']==1){
		} else {
			checkfalseneg:
			$devicealive=json_decode(${$addonid}->Ping($ip), true);
		}
	}
	if (isset($devicealive['status']) && $devicealive['status'] == "alive") {
		if($statusorig==0) {
			$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 1 WHERE rooms_addonsid = '$rooms_addonsid';");
		}
	} else {
		if($statusorig==1) {
			if($count==0){
				$count++;
				goto checkfalseneg;
			}
			$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
		}
	}
	if($addonType=='service'){
			$statement = $configdb->prepare("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, infoType, ping) VALUES (:rooms_addonsid,:type,:ping)");
			try {
				$statement->execute(array(':rooms_addonsid'=>$rooms_addonsid,
				':type'=>$addonName,
				':ping'=>$devicealive['data']
				));
			} catch(PDOException $e) {
				$log->LogError("$e->getMessage()" . basename(__FILE__));
				return "Statement failed: " . $e->getMessage();
			}

	} elseif($addonType=='mediaplayer'){
		// need to standardize nowplayinginfo response in class files
		$nowPlayingInfo = ${$addonid}->GetPlayingItemInfo();
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
			$statement = $configdb->prepare("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, info, infoType, thumbnail, fanart, time, ping) VALUES (:rooms_addonsid,:title,:type,:thumbnail,:fanart,:time,:ping)");
			try {
				$statement->execute(array(':rooms_addonsid'=>$rooms_addonsid,
				':title'=>$title,
				':type'=>$type,
				':thumbnail'=>$thumbnail,
				':fanart'=>$fanart,
				':time'=>$time,
				':ping'=>$devicealive['data']
				));
			} catch(PDOException $e) {
				$log->LogError("$e->getMessage()" . basename(__FILE__));
				return "Statement failed: " . $e->getMessage();
			}
		} elseif($_POST['info']!='') {
			$execquery = $configdb->exec("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, info, infoType, thumbnail, fanart, time, ping) VALUES ('$rooms_addonsid','','','','','','')");
		}
	}
}


?>