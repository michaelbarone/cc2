<?php 
	require 'startsession.php';
	if( isset($_SESSION['uid']) ) {
	} else {
		print 'failedAuth';
		$log->LogWarn("No user session data from " . basename(__FILE__));		
		exit;
	}
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
		try {
			$roomArray = array();
			$roomIds = '';
			foreach ($configdb->query("SELECT roomAccess,forceLogout,disabled FROM users WHERE userid = $userid LIMIT 1") as $row) {
				/* force logout user */
				if($row['forceLogout'] == '1') {
					$execquery = $configdb->exec("UPDATE users SET forceLogout=0 WHERE userid = '$userid';");
					print 'failedAuth';
					$log->LogWarn("FORCED LOGOUT: userid: " . $userid . "  " . basename(__FILE__));		
					exit;
				}
				if($row['disabled'] == '1') {
					print 'failedAuth';
					$log->LogWarn("DISABLED USER ATTEMPTED ACCESS: userid: " . $userid . "  " . basename(__FILE__));		
					exit;
				}
				if($row['roomAccess'] != '' && $row['roomAccess'] != null) {
					$roomIds=$row['roomAccess'];
				}
			}

			// strip duplicates
			//$roomIds = implode(',', array_keys(array_flip(explode(',', $roomIds))));
			$sql = "SELECT * FROM rooms WHERE roomId in ( $roomIds ) ORDER BY roomOrder";
			foreach ($configdb->query($sql) as $row) {
				$roomid=$row['roomId'];
				$roomArray[$roomid]['name']=$row['roomName'];
				$roomArray[$roomid]['order']=$row['roomOrder'];
			}
		} catch(PDOException $e) {
			$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
			echo "failed";
			exit;
		}
	} else {
		echo "failed";
		exit;
	}
	if(empty($roomArray)){
		echo "noRoomAccess";
		exit;
	}
	try {
		$addonArray = array();
		$roomIds = explode(',', $roomIds);
		foreach($roomIds as $x) {
			if(!isset($x) || $x == '' || is_array($x)) { continue; }
			$allAddonsAlive="1";
			$allPowerOptions=0;
			$addonArray[$x]['0']['roomName']=$roomArray[$x]['name'];
			$addonArray[$x]['0']['roomOrder']=intval($roomArray[$x]['order']);
			$addonArray[$x]['0']['roomId']=$x;
			$addonArray[$x]['0']['addonsAlive']=0;
			$addonArray[$x]['0']['allAddonsAlive']=$allAddonsAlive;
			$addonArray[$x]['0']['allAddonsMacs']='';
			$i=0;
			try {
				$sql = "SELECT * FROM rooms_addons,addons WHERE addons.addonid = rooms_addons.addonid AND rooms_addons.roomid = $x AND rooms_addons.enabled = '1' AND addons.globalDisable='0'";
				foreach ($configdb->query($sql) as $row) {
					$i++;
					foreach($row as $item => $value){
						if($item==='addonid'){
							$addonparts = explode('.',$value);
							$addonArray[$x][$i]['addontype']=$addonparts[0];
							$addonArray[$x][$i]['addon']=$addonparts[1];
							$addonArray[$x][$i]['addonversion']=$addonparts[2];						
						}
						if($item == "lastCheck"){ continue; }
						if($item==='time' && $value!=''){
							$temparray = json_decode($value, true);
							if(is_array($temparray)){
								foreach($temparray as $temp => $item) {
									$addonArray[$x][$i]['time'][$temp]=$item;
								}
							}
						}
						if($item==='ping' && $value!=''){
							$temparray = json_decode($value, true);
							if(is_array($temparray)){
								foreach($temparray as $temp => $item) {
									$addonArray[$x][$i]['ping'][$temp]=$item;
								}
							}
						}
						if($item==='displayInfo' && $value!=''){
							$temparray = json_decode($value, true);
							if(is_array($temparray)){
								foreach($temparray as $temp => $item) {
									$addonArray[$x][$i]['displayInfo'][$temp]=$item;
								}
							}
						}
						if(is_numeric($item)===false){
							$addonArray[$x][$i][$item]=$value;
						}
						$addonArray[$x][$i]['id']=$i;
					}
					if($row['device_alive']<"3" && $row['roomRequiresAlive']==="1") {
						$allAddonsAlive="0";
						$addonArray[$x]['0']['allAddonsAlive']=$allAddonsAlive;
						if($row['mac']!==''||$row['mac']!=='null') {
							$addonArray[$x]['0']['allAddonsMacs']=$row['mac'] . "," . $addonArray[$x]['0']['allAddonsMacs'];
						}
					}
					if($row['device_alive']>2){
						$addonArray[$x]['0']['addonsAlive']++;
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
			} catch(PDOException $e) {
				$log->LogInfo("No Addons In Room $roomArray[$x]['name'] -- $e->getMessage().  from " . basename(__FILE__));
			}			
		}
		$result = $addonArray;
		
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo ")]}',\n"."[".$json."]";
?>