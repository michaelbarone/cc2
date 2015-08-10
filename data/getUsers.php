<?php 
	require 'startsession.php';
	try {
		$usersArray = array();
		foreach ($configdb->query("SELECT * FROM users") as $row) {
			$passwordset="0";
			if($row['password']=="" || !isset($row['password'])) {
				$passwordset="0";
			} else {
				$passwordset="1";
			}
			$usersArray[] = array(
				'userid' => $row['userid'], 
				'username' => $row['username'],
				'passwordset' => "$passwordset",
				'passwordreset' => $row['passwordreset'],
				'avatar' => $row['avatar']
			);
		}
		$result = $usersArray;
		
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo $json;
?>