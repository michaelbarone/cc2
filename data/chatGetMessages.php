<?php 
require 'startsession.php';

if(!isset($_SESSION['userid']){
	$log->LogWarn("No Userid set in session " . basename(__FILE__));
	return "failed";
	exit;
}
$timenow = time();
try {
	$sql = "SELECT * FROM chat WHERE sentTo = '$_SESSION['userid']' ORDER BY chatId DSC LIMIT 100";
	foreach ($configdb->query($sql) as $thischat) {
		if($thischat['sendType']==='user'){
			// deal with user based messages
			
			
			
		} elseif($thischat['sendType']==='room'){
			// deal with room based messages
			
		} else {
			return "failed";
		}
	}
} catch(PDOException $e)
	{
		$log->LogError("$e->getMessage()" . basename(__FILE__));
		return "failed";
	}

	
	
	
	
/*

	// $_SESSION['roomAccess']  has been removed, need to get room list from $scope or pass to this 


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
*/	
	
	
	
	
	
	
	


//return message_array
?>