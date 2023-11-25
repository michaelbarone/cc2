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
	$time = time();
	
	if(isset($_GET['type']) && $_GET['type']==="addon") {
		if(isset($_GET['addonid']) && $_GET['addonid']>0) {
			$addonid=$_GET['addonid'];
			$sql = "SELECT addons.*,settings.globalDisable,settings.controlWindow FROM rooms_addons as addons 
					LEFT JOIN addons as settings ON addons.addonid = settings.addonid 
					WHERE addons.enabled ='1' AND settings.globalDisable='0' AND addons.rooms_addonsid='$addonid';";
			foreach ($configdb->query($sql) as $row) {
				if((($row['device_alive']==='0' && $_GET['option']==='on') || ($row['device_alive']==='1' && $_GET['option']==='off')) && $row['PowerOptions']==='1'){
					$addonparts = explode(".",$row['addonid']);
					$addonType=$addonparts[0];
					$addonName=$addonparts[1];
					$addonid=$row['addonid'];			
					$ip=$row['ip'];
					$mac=$row['mac'];
					if(file_exists("../addons/$addonid/$addonType.$addonName.php") && $ip !='') {
						if(!isset(${$addonid})) {
							include "../addons/$addonid/$addonType.$addonName.php";
							${$addonid} = new $addonName();
						}
						$vars = array();
						foreach($row as $item => $value){
							$vars[$item]=$value;
						}
						${$addonid}->SetVariables($vars);

						if($_GET['option']==='off') {
							$poweroff = ${$addonid}->PowerOff();
						} elseif($_GET['option']==='on') {
							$poweron = ${$addonid}->PowerOn();
							if($poweron==="wol" && $mac != ''){
								include("wakeAddon.php");
							}
						}
					}
				}
			}			
		}
	} elseif(isset($_GET['type']) && $_GET['type']==="room") {
		if(isset($_GET['room']) && $_GET['room']>0) {
			$room=$_GET['room'];
			$sql = "SELECT addons.*,settings.globalDisable,settings.controlWindow FROM rooms_addons as addons 
					LEFT JOIN addons as settings ON addons.addonid = settings.addonid 
					WHERE addons.enabled ='1' AND settings.globalDisable='0' AND addons.roomid='$room';";
			foreach ($configdb->query($sql) as $row) {
				if((($row['device_alive']==='0' && $_GET['option']==='on') || ($row['device_alive']==='1' && $_GET['option']==='off')) && $row['PowerOptions']==='1'){
					$addonparts = explode(".",$row['addonid']);
					$addonType=$addonparts[0];
					$addonName=$addonparts[1];	
					$addonid=$row['addonid'];
					$ip=$row['ip'];
					$mac=$row['mac'];					
					if(file_exists("../addons/$addonid/$addonType.$addonName.php") && $ip != '') {
						if(!isset(${$addonid})) {
							include "../addons/$addonid/$addonType.$addonName.php";
							${$addonid} = new $addonName();
						}
						$vars = array();
						foreach($row as $item => $value){
							$vars[$item]=$value;
						}
						${$addonid}->SetVariables($vars);

						if($_GET['option']==='off') {
							$poweroff = ${$addonid}->PowerOff();
						} elseif($_GET['option']==='on') {
							$poweron = ${$addonid}->PowerOn();
							if($poweron==="wol" && $vars['mac'] != ''){
								include("wakeAddon.php");
							}
						}
					}
				}
			}
			
		} else {
			$failed++;
		}
	} elseif(isset($_GET['type']) && $_GET['type']==="addon555") {
	
	
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