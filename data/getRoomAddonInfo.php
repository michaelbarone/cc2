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
			$i=0;
			$sql = "SELECT * FROM rooms_addons WHERE roomid = $x AND enabled ='1'";
			foreach ($configdb->query($sql) as $row) {
				$i++;
				$addonparts = explode('.',$row['addonid']);
				$addonArray[$x][$i]['addontype']=$addonparts[0];
				$addonArray[$x][$i]['addon']=$addonparts[1];
				$addonArray[$x][$i]['ip']=$row['ip'];
				$addonArray[$x][$i]['mac']=$row['mac'];
				$addonArray[$x][$i]['alive']=$row['device_alive'];
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