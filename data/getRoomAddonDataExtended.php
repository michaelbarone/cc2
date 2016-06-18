<?php 
	require 'startsession.php';
	if( isset($_SESSION['uid']) ) {
	} else {
		print 'failedAuth';
		exit;
	}
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
	} else {
		echo "failed";
		$log->LogWarn("No user session data from " . basename(__FILE__));
		exit;
	}
	$addonArray = array();
	$addonArray = $_GET['data'];
	$addonArray=json_decode($addonArray, true);
	//print_r($addonArray);
	
	
	$rooms_addonsid=$addonArray['rooms_addonsid'];
	$addonid=$addonArray['addonid'];
	$addonparts = explode(".",$addonArray['addonid']);
	$addonName=$addonparts[1];
	$addonType=$addonparts[0];
	$ip=$addonArray['ip'];
	$mac=$addonArray['mac'];	
	if(file_exists("../addons/$addonid/$addonid.php") && $ip !='') {
		if(!isset(${$addonName})) {
			include "../addons/$addonid/$addonid.php";
			${$addonName} = new $addonName();
		}
		$vars = array();
		$vars['ip']=$ip;
		$vars['mac']=$mac;
		${$addonName}->SetVariables($vars);	
	
	
		$nowPlayingInfo = ${$addonName}->GetPlayingItemInfo();
		//print_r($nowPlayingInfo);
		if(isset($nowPlayingInfo['title']) && $nowPlayingInfo['title']!='') {
			$title = $nowPlayingInfo['title'];
			if(isset($nowPlayingInfo['showtitle']) && $nowPlayingInfo['showtitle']!='') {
				$episode = "";
				if(isset($nowPlayingInfo['season']) && $nowPlayingInfo['season']!='' && isset($nowPlayingInfo['episode']) && $nowPlayingInfo['episode']!='') {
					$episode = " " . $nowPlayingInfo['season'] . "x" . $nowPlayingInfo['episode'];
				}
				$nowPlayingInfo['title'] = $nowPlayingInfo['showtitle'] . $episode . " - " . $nowPlayingInfo['title'];
			} elseif(isset($nowPlayingInfo['year']) && $nowPlayingInfo['year']!='') {
				$nowPlayingInfo['title'] = $nowPlayingInfo['title'] . " (" . $nowPlayingInfo['year'] . ")";
			}
			$nowPlayingInfo['addonType']=$addonType;
			header('Content-Type: application/json');
			$json=json_encode($nowPlayingInfo);
			echo "[".$json."]";
		}
	}
?>