<?php 
	require 'startsession.php';
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
			$addonArray[$x]['0']['allAddonsAlive']=$allAddonsAlive;
			$addonArray[$x]['0']['allAddonsMacs']='';
			$i=0;
			$sql = "SELECT * FROM rooms_addons,rooms_addons_info WHERE rooms_addons.rooms_addonsid = rooms_addons_info.rooms_addonsid AND rooms_addons.roomid = $x AND rooms_addons.enabled = '1'";
			foreach ($configdb->query($sql) as $row) {
				$i++;
				$addonparts = explode('.',$row['addonid']);
				$addonArray[$x][$i]['addontype']=$addonparts[0];
				$addonArray[$x][$i]['addon']=$addonparts[1];
				$addonArray[$x][$i]['ip']=$row['ip'];
				$addonArray[$x][$i]['mac']=$row['mac'];
				$addonArray[$x][$i]['alive']=$row['device_alive'];
				$addonArray[$x][$i]['info']=$row['info'];
				$addonArray[$x][$i]['infoType']=$row['infoType'];
				if($row['device_alive']==="0") {
					$allAddonsAlive="0";
					$addonArray[$x]['0']['allAddonsAlive']=$allAddonsAlive;
					if($row['mac']!==''||$row['mac']!=='null') {
						$addonArray[$x]['0']['allAddonsMacs']=$row['mac'] . "," . $addonArray[$x]['0']['allAddonsMacs'];
					}
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