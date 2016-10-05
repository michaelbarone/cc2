<?php
$failed=0;
if(isset($_GET)) {
	require_once "startsession.php";
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
	} else {
		echo "failed";
		$log->LogWarn("No user session data from " . basename(__FILE__));
		exit;
	}
	$log->LogDebug("User " . $_SESSION['username'] . " loaded " . basename(__FILE__) . " from " . $_SERVER['SCRIPT_FILENAME']);

	
	$fromaddon=$_GET['fromaddon'];
	$fromip=$_GET['fromip'];
	$toaddon=$_GET['toaddon'];
	$toip=$_GET['toip'];
	$type=false;
	if(isset($_GET['type'])){
		$type=$_GET['type'];
	}
		
		// at some point, to info might be arrays to send media to multiple destinations

		
		
	// get now playing info to send
	$addonid=$fromaddon;
	$addonparts = explode(".",$fromaddon);
	$addonType=$addonparts[0];
	$addonName=$addonparts[1];
	if(file_exists("../addons/$addonid/$addonType.$addonName.php")) {
		if(!isset(${$addonid})) {
			include "../addons/$addonid/$addonType.$addonName.php";
			${$addonid} = new $addonName();
		}
	}
	${$addonid}->setIp($fromip);
	if($type=="clone" || $type=="start") {
		$sendmedia = ${$addonid}->SendMedia("$type");
	}else{
		$sendmedia = ${$addonid}->SendMedia();
	}
	
	if(isset($sendmedia['file']) && $sendmedia['file']!='') {
	
	
		// send media to next mediaplayer	
		$addonid=$toaddon;
		$addonparts = explode(".",$toaddon);
		$addonType=$addonparts[0];
		$addonName=$addonparts[1];
		if(file_exists("../addons/$addonid/$addonType.$addonName.php")) {
			if(!isset(${$addonid})) {
				include "../addons/$addonid/$addonType.$addonName.php";
				${$addonid} = new $addonName();
			}
		}
		${$addonid}->setIp($toip);
		
		
		$video=$sendmedia['file'];
		$activeplayerid=$sendmedia['activeplayerid'];
		if($type=="clone" || $type=="start") {
			$playtype=$type;
		}else{
			$playtype=false;
		}
		if($type!="start" && isset($sendmedia['playerpercentage']) && $sendmedia['playerpercentage']>=0){
			$playerpercentage=$sendmedia['playerpercentage'];
		}else{
			$playerpercentage=false;
		}
		
		${$addonid}->PlayMedia($video,$activeplayerid,$playtype,$playerpercentage);
		
		
	} else {
		// nothing found to be playing on sending mediaplayer
	}
} else {
	echo "failed";
	$log->LogWarn("No GET parameters for " . basename(__FILE__));	
	Exit;
}
if($failed>0){
	echo "failed";
	$log->LogWarn("BAD GET parameters for " . basename(__FILE__));	
	Exit;
}
?>