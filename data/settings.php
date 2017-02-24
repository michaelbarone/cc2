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
				if($item=="password") { continue; }
				$usersArray[$userid][$item] = $key;
			}
		}
		$result = $usersArray;
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	return ")]}',\n".$json;
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
	return ")]}',\n".$json;
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
	return ")]}',\n".$json;
}

function GetAddons(){
	if(!isset($ADDONDIR)) {
		$found = false;
		$ADDONDIR = './addons';
		while(!$found){
			if(file_exists($ADDONDIR)){ 
				$found = true;
			}
			else{ $ADDONDIR = '../'.$ADDONDIR; }
		}
	}
	// for each folder in $ADDONDIR loop to get addonfolders array
	
	// room addons global settings db table to create addons array, compare with addonfolders.
	
	// list of addonsfolders that are not in addons
	
	// list of addons with global settings and addon.info
	
	header('Content-Type: application/json');
	$json=json_encode($result);
	return ")]}',\n".$json;
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
		
		$result = array_diff_key($users, $updatedusers);
		// delete these
		foreach($result as $user){
			$query = "DELETE FROM `users` WHERE userid = ".$user['userid']." AND username = ".$user['username'];
			$statement = $configdb->prepare($query);
			$statement->execute();
		}

		
		
		
		$result = array_diff_key($updatedusers, $users);
		// add these
		//echo "add these:<br>";
		foreach($result as $user){
			// need to add all user options here... include password?  or leave blank and use general password reset modal
			// see update query below 
			$query = "INSERT INTO `users` (";
			
			// "sanitize" username by stripping all non alpha/numeric characters
			// this is also done in session_login.php to make sure no usernames have sql injection stuff
			//$username = preg_replace('/[^a-zA-Z0-9]/', '', $user['username']);
			
			foreach($user as $setting => $setas){
				if($setting==='userid'){ continue; }
				if($setting==='password'){ continue; }
				if($setting==='passwordv'){ continue; }
				if($setting==='lastaccess'){ continue; }
				if($setting!='username') {
					$query .= ",";
				}
				$query .= $setting;
			}
			
			$query .= ") VALUES (";
			
			foreach($user as $setting => $setas){
				if($setting==='userid'){ continue; }
				if($setting==='password'){ continue; }
				if($setting==='passwordv'){ continue; }
				if($setting==='lastaccess'){ continue; }
				if($setting!='username') {
					$query .= ",";
				}
				$query .= "'$setas'";
			}			
			$query.= ")";

			$statement = $configdb->prepare($query);
			$statement->execute();				
			unset($updatedusers[$user['userid']]);
		}		
		//print_r($result);
		//echo "<br><br>";
		
		
		//else update remaining $updatedusers
		//echo "update these entries:";
		foreach($updatedusers as $user){			
			// show changed item column(s)
			$result2 = array_diff($user, $users[$user['userid']]);
			//print_r($result2);
			
			
			// check if $result2 is an array, if so update this whole userid
			if(is_array($result2) && count($result2)>0){
				$query = "UPDATE `users` SET ";

				foreach($user as $setting => $setas){
					if($setting==='userid'){ continue; }
					if($setting==='password'){ continue; }
					if($setting==='passwordv'){ continue; }
					if($setting==='lastaccess'){ continue; }
					if($setting!='username') {
						$query .= ", ";
					}
					$query .= "$setting = '$setas'";
				}

				$query .= " WHERE userid = ".$user['userid'];
				$statement = $configdb->prepare($query);
				$statement->execute();
			}
		}		
		return;
	} elseif($action === "saveRooms"){
		//$users=json_decode(GetUsers($configdb),true);
		//print_r($users);
		//echo "<br /><br />";
		$data = json_decode($_GET['data'], true);
		print_r($data);
		
		
		
		
		
		
		
		
		
	}
}
?>