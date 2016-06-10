<?php 
	if(!isset($_GET)) { exit; }
	require 'startsession.php';
	$action = $_GET['action'];



	
	
if(isset($action)) {	
	if($action === "getUsers") {	
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
	} elseif($action === "saveUsers"){
		$users = json_decode($_GET['users'], true);
		print_r($users);
		foreach($users as $user){
			if(!isset($user['username']) || $user['username']===''){ continue; }
			echo $user['username']."<br>";
			$usertable = '';
			$count=0;
			foreach($user as $item => $name) {
				if($count===0){
					$count++;
				}else{
					$usertable .= ",";
				}
				$usertable .= " $item = '$name'";
			}
			echo $usertable;
			
		}
	}
}
?>