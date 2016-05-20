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
	if(isset($_GET['type']) && $_GET['type']==="room") {
		if(isset($_GET['room']) && $_GET['room']>0) {
			$room=$_GET['room'];
			$addons=array();
			$sql = "SELECT addons.*,details.globalDisable,details.controlWindow,info.* FROM rooms_addons as addons 
					LEFT JOIN rooms_addons_details as details ON addons.addonid = details.addonid 
					LEFT JOIN rooms_addons_info as info ON addons.rooms_addonsid = info.rooms_addonsid
					WHERE addons.enabled ='1' AND details.globalDisable='0' AND addons.roomid='$room';";
			foreach ($configdb->query($sql) as $row) {
				if($row['device_alive']==='0'){
					$addonparts = explode(".",$row['addonid']);
					$addontype=$addonparts[0];
					$addonName=$addonparts[1];	
					$addonid=$row['addonid'];				
					$ip=$row['ip'];
					$mac=$row['mac'];
					if(file_exists("../addons/$addonid/$addonid.php") && $ip !='') {
						if(!isset(${$addonName})) {
							include "../addons/$addonid/$addonid.php";
							${$addonName} = new $addonName();
						}
						$vars = array();
						$vars['ip']=$ip;
						$vars['mac']=$mac;
						${$addonName}->SetVariables($vars);
						$poweron = ${$addonName}->PowerOn();
						if($poweron==="wol" && $mac != ''){
							include("wakeAddon.php");
						}
					}
				}
			}
			
		} else {
			$failed++;
		}
	} elseif(isset($_GET['type']) && $_GET['type']==="addon") {
	
	
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






exit;
?>