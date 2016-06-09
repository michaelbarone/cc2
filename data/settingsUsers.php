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
			$userid = $row['userid'];
			foreach($row as $item => $key) {
				if(is_numeric($item)) { continue; }
				$usersArray[$userid][$item] = $key;
			}
		}
		$result = $usersArray;
		
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo $json;
?>