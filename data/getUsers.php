<?php 
	require 'startsession.php';
	$type='';
	if(isset($_GET['type']) && $_GET['type'] != '') {
		$type=$_GET['type'];
	}
	try {
		$usersArray = array();
		foreach ($configdb->query("SELECT userid,username,password,passwordreset,avatar,lastaccess FROM users") as $row) {
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
				'passwordreset' => $row['passwordreset'],
				'avatar' => $row['avatar']
			);
			if($type==="chat"){
				$usersArray[$userid]['lastaccess'] = $row['lastaccess'];
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