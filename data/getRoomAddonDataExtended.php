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
	$rooms_addonsid=$addonArray['rooms_addonsid'];
	$addonid=$addonArray['addonid'];
	$addonparts = explode(".",$addonArray['addonid']);
	$addonName=$addonparts[1];
	$addontype=$addonparts[0];
	
	if($addontype!="mediaplayer"){
		header('Content-Type: application/json');
		$json=json_encode($addonArray);
		echo ")]}',\n"."[".$json."]";		
		
	} else {
		
		$ip=$addonArray['ip'];
		$mac=$addonArray['mac'];	
		if(file_exists("../addons/$addonid/$addontype.$addonName.php") && $ip !='') {
			if(!isset(${$addonid})) {
				include "../addons/$addonid/$addontype.$addonName.php";
				${$addonid} = new $addonName();
			}
			$vars = array();
			$vars['ip']=$ip;
			$vars['mac']=$mac;
			${$addonid}->SetVariables($vars);	
		
		
			$nowPlayingInfo = ${$addonid}->GetAddonInfo();
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
				if(isset($nowPlayingInfo['time']) && $nowPlayingInfo['time']!=''){
					$temparray = json_decode($nowPlayingInfo['time'], true);
					unset($nowPlayingInfo['time']);
					foreach($temparray as $temp => $item) {
						if(!isset($item)||!isset($temp)){continue;}
						$nowPlayingInfo['time'][$temp]=$item;
					}
				}			
				
				
				
				$nowPlayingInfo['addontype']=$addontype;
				header('Content-Type: application/json');
				$json=json_encode($nowPlayingInfo);
				echo ")]}',\n"."[".$json."]";
			}
		}
	}
?>