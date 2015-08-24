<?php 
	require 'startsession.php';
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
	} else {
		echo "failed";
		$log->LogWarn("No user session data from " . basename(__FILE__));
		exit;
	}
	try {
		$roomArray = array();
		$roomIds = '';
		foreach ($configdb->query("SELECT roomGroupAccess,roomAccess FROM users WHERE userid = $userid LIMIT 1") as $row) {
			if($row['roomGroupAccess'] != '') {
				$thisRoomGroup = $row['roomGroupAccess'];
				foreach ($configdb->query("SELECT roomAccess FROM roomgroups WHERE roomGroupId = $thisRoomGroup LIMIT 1") as $row2) {
					$roomIds=$row2['roomAccess'] . ",";
				}
			}
			$roomIds.=$row['roomAccess'];
		}
		$roomIds = explode(',', $roomIds);
		$_SESSION['roomAccess']=$roomIds;
		foreach($roomIds as $x) {
			if(!isset($x) || $x == '' || is_array($x)) { continue; }
			$sql = "SELECT roomName FROM rooms WHERE roomId = $x LIMIT 1";
			foreach ($configdb->query($sql) as $row) {
				$roomArray[$x]['name']=$row['roomName'];
			}
		}
		$result = $roomArray;
		
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo "[".$json."]";
?>