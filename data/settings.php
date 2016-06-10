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




if(isset($action)) {	
	if($action === "getUsers") {
		$users=GetUsers($configdb);
		echo $users;
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
		// unset from $updatedusers
		print_r($result);
		echo "<br><br>";
		
		
		//else update remaining $updatedusers
		echo "update these entries:";
		foreach($updatedusers as $user){
			echo $user['userid']."<br>";
		}
	}
}
?>