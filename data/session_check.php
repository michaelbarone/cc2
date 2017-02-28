<?php 
	require 'startsession.php';
	
	// need some better auth handling/checking here
	
	if(isset($_SESSION['uid']) && isset($_SESSION['userid'])) {
		$userid = $_SESSION['userid'];
		foreach ($configdb->query("SELECT forceLogout,disabled FROM users WHERE userid = $userid LIMIT 1") as $row) {
			if($row['forceLogout'] == '1') {
				$execquery = $configdb->exec("UPDATE users SET forceLogout=0 WHERE userid = '$userid';");
				print 'failedAuth';
				$log->LogWarn("FORCED LOGOUT: userid: " . $userid . "  " . basename(__FILE__));		
				exit;
			}elseif($row['disabled'] == '1') {
				print 'failedAuth';
				$log->LogWarn("DISABLED USER ATTEMPTED ACCESS: userid: " . $userid . "  " . basename(__FILE__));		
				exit;				
			}
		}
		print 'passedAuth';
	} else if( isset($_SESSION['firstrun']) ) {
		print 'firstrun';
	} else {
		print 'failedAuth';
	}
?>