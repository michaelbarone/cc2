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
		$roomGroups = '';
		foreach ($configdb->query("SELECT roomGroupAccess,roomAccess FROM users WHERE userid = $userid LIMIT 1") as $row) {
			if($row['roomGroupAccess'] != '') {
				$thisRoomGroup = $row['roomGroupAccess'];
				foreach ($configdb->query("SELECT roomAccess FROM roomgroups WHERE roomGroupId = $thisRoomGroup LIMIT 1") as $row2) {
					$roomGroups=$row2['roomAccess'];
				}
			}
			$roomGroups.=$row['roomAccess'];
		}
		$roomGroups = explode(',', $roomGroups);
		foreach($roomGroups as $x) {
			if(!isset($x) || $x == '' || is_array($x)) { continue; }
			$sql = "SELECT * FROM rooms WHERE roomId = $x LIMIT 1";
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