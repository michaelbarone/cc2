<?php 
	require 'startsession.php';
	if( isset($_SESSION['uid']) ) {
	} else {
		print 'failedAuth';
		exit;
	}
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
		$roomIds=$_SESSION['roomAccess'];
	} else {
		echo "failed";
		$log->LogWarn("No user session data from " . basename(__FILE__));
		exit;
	}
	try {
		$addonArray = array();
		foreach($roomIds as $x) {
			if(!isset($x) || $x == '' || is_array($x)) { continue; }
			$allAddonsAlive="1";
			$allPowerOptions=0;
			$addonArray[$x]['0']['allAddonsAlive']=$allAddonsAlive;
			$addonArray[$x]['0']['allAddonsMacs']='';
			$i=0;
			$sql = "SELECT * FROM rooms_addons,rooms_addons_info,rooms_addons_global_settings WHERE rooms_addons.rooms_addonsid = rooms_addons_info.rooms_addonsid AND rooms_addons_global_settings.addonid = rooms_addons.addonid AND rooms_addons.roomid = $x AND rooms_addons.enabled = '1' AND rooms_addons_global_settings.globalDisable='0'";
			foreach ($configdb->query($sql) as $row) {
				$i++;
				foreach($row as $item => $value){
					if($item==='addonid'){
						$addonparts = explode('.',$value);
						$addonArray[$x][$i]['addontype']=$addonparts[0];
						$addonArray[$x][$i]['addon']=$addonparts[1];						
					}
					if(is_numeric($item)===false){
						$addonArray[$x][$i][$item]=$value;
					}
				}
				if($row['device_alive']==="0" && $row['roomRequiresAlive']==="1") {
					$allAddonsAlive="0";
					$addonArray[$x]['0']['allAddonsAlive']=$allAddonsAlive;
					if($row['mac']!==''||$row['mac']!=='null') {
						$addonArray[$x]['0']['allAddonsMacs']=$row['mac'] . "," . $addonArray[$x]['0']['allAddonsMacs'];
					}
				}
				if($row['PowerOptions']==="1") {
					$allPowerOptions++;
					$addonArray[$x]['0']['allPowerOptions']=$allPowerOptions;
				}				
				$timenow = time();
				if(($row['lastCheck']+4) < $timenow) {
					$addonid = $row['addonid'];
					$execquery = $configdb->exec("UPDATE rooms_addons SET lastCheck = '$timenow' WHERE roomid = '$x' AND addonid = '$addonid';");
				}
			}
		}
		$result = $addonArray;
		
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo "[".$json."]";
?>