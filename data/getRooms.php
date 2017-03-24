<?php 
	require 'startsession.php';
	
	
	
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
				$roomId = $row['roomId'];
				$roomArray[$roomId] = array(
					'roomId' => intval($roomId), 
					'roomName' => $row['roomName'],
					'roomOrder' => intval($row['roomOrder'])
				);
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
	header('Content-Type: application/json');
	$json=json_encode($roomArray);
	echo ")]}',\n".$json;
?>