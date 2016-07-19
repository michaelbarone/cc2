<?php 
	if(!isset($_GET)) { exit; }
	require 'startsession.php';
	$action = $_GET['action'];




function GetUsers($configdb){
	try {
		$usersArray = array();
		foreach ($configdb->query("SELECT * FROM users") as $row) {
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
	return $json;
}

function GetRooms($configdb){
	try {
		$roomsArray = array();
		foreach ($configdb->query("SELECT * FROM rooms") as $row) {
			$roomid = $row['roomId'];
			foreach($row as $item => $key) {
				if(is_numeric($item)) { continue; }
				$roomsArray[$roomid][$item] = $key;
			}
		}
		foreach ($configdb->query("SELECT * FROM roomgroups") as $row) {
			$roomGroupId = $row['roomGroupId'];
			$roomsArray["groups"]["roomId"]="groups";
			foreach($row as $item => $key) {
				if(is_numeric($item)) { continue; }
				$roomsArray["groups"][$roomGroupId][$item] = $key;
			}
		}		
		$result = $roomsArray;
		} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	return $json;
}

function GetNavigation($configdb){
	try {
		$roomsArray = array();
		foreach ($configdb->query("SELECT * FROM navigation") as $row) {
			$navid = $row['navid'];
			foreach($row as $item => $key) {
				if(is_numeric($item)) { continue; }
				$roomsArray[$navid][$item] = $key;
			}
		}
		foreach ($configdb->query("SELECT * FROM navigationgroups") as $row) {
			$navgroupid = $row['navgroupid'];
			$roomsArray["groups"]["navid"]="groups";
			foreach($row as $item => $key) {
				if(is_numeric($item)) { continue; }
				$roomsArray["groups"][$navgroupid][$item] = $key;
			}
		}		
		$result = $roomsArray;
		} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	return $json;
}

if(isset($action)) {	
	if($action === "getNavigation") {
		$navigation=GetNavigation($configdb);
		echo $navigation;
	} elseif($action === "getRooms") {
		$rooms=GetRooms($configdb);
		echo $rooms;
	} elseif($action === "getUsers") {
		$users=GetUsers($configdb);
		echo $users;
	} elseif($action === "createFirstUser") {
		$users=json_decode(GetUsers($configdb),true);	
		//print_r($users);
		if(empty($users)){
			$username = $_GET['username'];
			$password = $_GET['password'];

			require "../lib/php/PasswordHash.php";
			$hasher = new PasswordHash(8, false);
			if (strlen($password) > 72) { $password = substr($password,0,72); }
			$password = $hasher->HashPassword($password);

			$query = "INSERT INTO `users` (username,password,passwordv,settingsAccess) VALUES ('$username','$password','2','1')";
			$statement = $configdb->prepare($query);
			$statement->execute();			

			header('Content-Type: application/json');
			$json=json_encode("success");
			return $json;	
		} else {
			$log->LogWarn("Create First User attempt failed: users already present in system. " . basename(__FILE__));
			echo "error";
		}
		

		
		return;
	} elseif($action === "saveUsers"){
		$users=json_decode(GetUsers($configdb),true);
		//print_r($users);
		//echo "<br /><br />";
		$updatedusers = json_decode($_GET['users'], true);
		//print_r($updatedusers);
		foreach($updatedusers as $user){
			if(!isset($user['username']) || $user['username']===''){
				echo "attempt to unload -- ".$user['userid'];
				unset($updatedusers[$user['userid']]);
			}
		}
		
		
		
		/*
		foreach($updatedusers as $user){
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
			
		*/
		$result = array_diff_key($users, $updatedusers);
		// delete these
		echo "delete these:<br>";
		foreach($result as $user){
			echo $user['userid']."<br>";
		}
		print_r($result);
		echo "<br><br>";
		
		
		
		$result = array_diff_key($updatedusers, $users);
		// add these
		echo "add these:<br>";
		foreach($result as $user){
			echo $user['userid']."<br>";
			unset($updatedusers[$user['userid']]);
		}		
		print_r($result);
		echo "<br><br>";
		
		
		//else update remaining $updatedusers
		echo "update these entries:";
		foreach($updatedusers as $user){
			print_r($user);
			print_r($users[$user['userid']]);
			$result = array_diff($users[$user['userid']], $user);
			print_r($result);
			$result = array_diff($user, $users[$user['userid']]);
			print_r($result);			
			echo $user['userid']."<br>";
		}
		
		
		
	}
}
?>