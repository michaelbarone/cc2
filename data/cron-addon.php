<?php
//if(!isset($_POST['rooms_addonsid']){ exit; }
$cronaddon=1;
if(!isset($configdb)) {
	require "startsession.php";
}
$rooms_addonsid=$_POST['rooms_addonsid'];
$addonid=$_POST['addonid'];
$addonparts = explode(".",$addonid);
$addonName=$addonparts[1];
$addonType=$addonparts[0];
$ip=$_POST['ip'];
$mac=$_POST['mac'];
$statusorig=$_POST['device_alive'];
$infoorig=$_POST['info'];
if(file_exists("../addons/$addonid/$addonType.$addonName.php") && $ip !='') {
	if(!isset(${$addonid})) {
		include "../addons/$addonid/$addonType.$addonName.php";
		${$addonid} = new $addonName();
	}
	$count=0;
	$countneg=0;
	$vars = array();
	$vars['ip']=$ip;
	$vars['mac']=$mac;
	${$addonid}->SetVariables($vars);


	

	checkfalseneg3:
	$addoninfo=array();
	$alivevalue = 0;
	$addoninfo = ${$addonid}->GetAddonInfo();
	if((isset($addoninfo['status']) && $addoninfo['status']=='alive')){
		$alivevalue = 2;
	}

	
	checkfalseneg:
	$devicealive=array();	
	$devicealive=json_decode(${$addonid}->Ping($ip), true);
	if(isset($devicealive['status']) && $devicealive['status']=='alive') {
		if($alivevalue==2) { $alivevalue = 3; } else { $alivevalue = 1; }
		if($addonName=='ping' && $alivevalue<3) { $alivevalue = 3; }
	} else {
		$alivevalue=0;
	}
	
	
	
	// need to update this section for error checking

	$fanart="";
	$title="";
	$thumbnail="";
	$time="";
	$type="";	
	if ($alivevalue>0){
		if($count==0){
			$count++;		
			if($alivevalue!=$statusorig) {
				if($alivevalue>2 && $statusorig>0){
					goto checkfalseneg3;
				}else{
					goto checkfalseneg;
				}
			}
		}
		
		if(isset($addoninfo['title']) && $addoninfo['title']!='') {
			$title = $addoninfo['title'];
			if(isset($addoninfo['showtitle']) && $addoninfo['showtitle']!='') {
				$episode = "";
				if(isset($addoninfo['season']) && $addoninfo['season']!='' && isset($addoninfo['episode']) && $addoninfo['episode']!='') {
					$episode = " " . $addoninfo['season'] . "x" . $addoninfo['episode'];
				}
				$title = $addoninfo['showtitle'] . $episode . " - " . $addoninfo['title'];
			} elseif(isset($addoninfo['year']) && $addoninfo['year']!='') {
				$title = $addoninfo['title'] . " (" . $addoninfo['year'] . ")";
			}
			if(isset($addoninfo['thumbnail']) && $addoninfo['thumbnail']!='') {
				$thumbnail = $addoninfo['thumbnail'];
			}
			if(isset($addoninfo['fanart']) && $addoninfo['fanart']!='') {
				$fanart = $addoninfo['fanart'];
			}
		}
		if(isset($addoninfo['type'])){ $type = $addoninfo['type']; }
		if(isset($addoninfo['time'])){ $time = $addoninfo['time']; }
		
		
		// place some checks for known fails before goto writeme;
		goto writeme;
		
		
		
		if($alivevalue!=$statusorig){
			goto writeme;
			//$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = ".$alivevalue." WHERE rooms_addonsid = '$rooms_addonsid';");
		}
		if($title!=""){
			goto writeme;
		}
	} else {
		if($statusorig>0) {
			if($countneg==0){
				$countneg++;
				goto checkfalseneg;
			}
		}
		//if($alivevalue!=$statusorig){
			goto writeme;
			//$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
		//}
	}
	
	
	
	goto skipme;	
	writeme:
	
	$statement = $configdb->prepare("UPDATE rooms_addons SET info = ?, infoType = ?, thumbnail = ?, fanart = ?, time = ?, ping = ?, device_alive = ?
			WHERE rooms_addonsid = ?");
	try {
		$statement->execute([$title, $type, $thumbnail, $fanart, $time, $devicealive['data'], $alivevalue, $rooms_addonsid]);
	} catch(PDOException $e) {
		return "Statement failed: " . $e->getMessage();
	}
	
	skipme:





	
} exit;

	
	
	
	/*
	$devicealive='';
	// need to move addon specific stuff up here, if fail, then app not on, but device may be on
	//if($statusorig==1 && $addonName!='ping') {
	if($addonName!='ping') {
		$devicealive=json_decode(${$addonid}->PingApp($ip), true);
	}
	if(isset($devicealive['status']) && $devicealive['status']=='alive') {
		$alivevalue = $alivevalue+1;
	}
	
	
	
	
	checkfalseneg2:
	$devicealive=json_decode(${$addonid}->Ping($ip), true);

	if(isset($devicealive['status']) && $devicealive['status']=='alive') {
		if($alivevalue<2) { $alivevalue = $alivevalue+1; }
		if($addonName=='ping' && $alivevalue<2) { $alivevalue = $alivevalue+1; }
	}else{
		$alivevalue=0;
	}
	

	if ($alivevalue>0){
		if($statusorig==0 ) {
			if($count==0){
				$count++;
				goto checkfalseneg2;
			}
		}
		if($alivevalue!=$statusorig){
			$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = ".$alivevalue." WHERE rooms_addonsid = '$rooms_addonsid';");
		}
	} else {
		if($statusorig>0) {
			if($count==0){
				$count++;
				goto checkfalseneg2;
			}
		}
		if($alivevalue!=$statusorig){
			$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
		}
	}
	
	
	//if($addonType=='service'||$addonType=='receiver'){
	if($addonType!='mediaplayer'){
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
		} elseif($_POST['info']!='' || (!isset($nowPlayingInfo['title']) || $nowPlayingInfo['title']=='')) {
			$pingdata=$devicealive['data'];
			$execquery = $configdb->exec("INSERT OR REPLACE INTO rooms_addons_info (rooms_addonsid, info, infoType, thumbnail, fanart, time, ping) VALUES ('$rooms_addonsid','','','','','','$pingdata')");
		}
	}
	*/

?>