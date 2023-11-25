<?php 
	require 'startsession.php';
	$type='';
	if(isset($_GET['type']) && $_GET['type'] != '') {
		$type=$_GET['type'];
	}
	try {
		$usersArray = array();
		foreach ($configdb->query("SELECT userid,username,password,forceLogout,avatar,lastaccess,disabled FROM users") as $row) {
			if(($type==='' || $type==="chat") && $row['disabled']==1) {
				continue;
			}			
			$passwordset="0";
			if($row['password']=="" || !isset($row['password'])) {
				$passwordset="0";
			} else {
				$passwordset="1";
			}
			$userid = $row['userid'];
			$usersArray[$userid] = array(
				'userid' => $userid, 
				'username' => $row['username'],
				'passwordset' => "$passwordset",
				'forceLogout' => $row['forceLogout'],
				'avatar' => $row['avatar']
			);
			if($type==="chat" || $type==="settings"){
				$usersArray[$userid]['lastaccess'] = $row['lastaccess'];
				$usersArray[$userid]['disabled'] = $row['disabled'];
			}
		}
		$result = $usersArray;
		
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo ")]}',\n".$json;
?>