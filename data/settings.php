<?php 
	if(!isset($_GET)) { exit; }
	require 'startsession.php';
	$action = $_GET['action'];

function pathUrl($dir=__DIR__){

    $root = "";
    $dir = str_replace('\\', '/', realpath($dir));

    //HTTPS or HTTP
    $root .= !empty($_SERVER['HTTPS']) ? 'https' : 'http';

    //HOST
    $root .= '://' . $_SERVER['HTTP_HOST'];

    //ALIAS
    if(!empty($_SERVER['CONTEXT_PREFIX'])) {
        $root .= $_SERVER['CONTEXT_PREFIX'];
        $root .= substr($dir, strlen($_SERVER[ 'CONTEXT_DOCUMENT_ROOT' ]));
    } else {
        $root .= substr($dir, strlen($_SERVER[ 'DOCUMENT_ROOT' ]));
    }

    $root .= '/';

    return $root;
}	
	

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

function GetAddons($configdb){
	try {
		$addonsArray = array();
		foreach ($configdb->query("SELECT * FROM addons order by addonid asc") as $row) {
			foreach($row as $item => $key) {
				if($item!='addonid') { continue; }
				$addonid=$key;
			}
			foreach($row as $item => $key) {
				if(is_numeric($item)) { continue; }
				$addonsArray[$addonid][$item] = $key;
			}
		}
		$result = $addonsArray;
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	return ")]}',\n".$json;
}

function GetAddonInfo($addonid){
	//$addonid = $newaddon['addonid'];
	$addonidpart = explode(".",$addonid);
	$addonType=$addonidpart[0];
	$addonName=$addonidpart[1];
	$addoninfo="none";

	//$url = pathUrl(__DIR__ . '/../');
	$url = "http".( (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")?'s':'' )."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
	$url = rtrim($url,"/data");	
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_URL, "http".( (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")?'s':'' )."://".$_SERVER['HTTP_HOST']."/e/cc2/addons/$addonid/$addonType.$addonName.php");
	curl_setopt($ch, CURLOPT_URL, $url."/addons/$addonid/$addonType.$addonName.php");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$addoninfo = curl_exec($ch);
	curl_close($ch);
	return $addoninfo;
}

/*
function ScanAddons(){
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
*/

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


/* check if user has access to settings before performing action */
$users=json_decode(ltrim(GetUsers($configdb),")]}',\n"),true);
$userid=$_SESSION['userid'];
$username=$_SESSION['username'];

if($users[$userid]['settingsAccess']!=1){
	$log->LogAlert("ILLEGAL ACCESS TO SETTINGS.PHP by user: $username and userid: $userid.  from " . basename(__FILE__));
	exit;
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
	} elseif($action === "getAddons") {
		$addons=getAddons($configdb);
		echo $addons;		
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


	/*

	} elseif($action === "saveUsers"){
		$users=json_decode(ltrim(GetUsers($configdb),")]}',\n"),true);
		//print_r($users);
		//echo "3333<br /><br />";
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
			if(isset($user['userid']) && is_numeric($user['userid']) && $user['userid']>0) {  
				$query = "DELETE FROM `users` WHERE userid = ".$user['userid']." AND username = '".$user['username']."'";
				$statement = $configdb->prepare($query);
				$statement->execute();
			}
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
			//print_r($user);
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
	*/	
		
		
	} elseif($action === "saveUser"){
		$user = json_decode($_GET['user'], true);
		$userarray[$user['userid']] = $user;
		$users=json_decode(ltrim(GetUsers($configdb),")]}',\n"),true);
		
		$result = array_diff_key($userarray, $users);		
		// new user add		
		foreach($result as $newuser){
			$query = "INSERT INTO `users` (";
			
			foreach($newuser as $setting => $setas){
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
			
			foreach($newuser as $setting => $setas){
				if($setting==='userid'){ continue; }
				if($setting==='password'){ continue; }
				if($setting==='passwordv'){ continue; }
				if($setting==='lastaccess'){ continue; }
				if($setting!='username') {
					$query .= ",";
				} else {
					// username sanitize here
					$setas = preg_replace('/[^a-zA-Z0-9]/', '', $setas);
				}
				$query .= "'$setas'";
			}			
			$query.= ")";

			$statement = $configdb->prepare($query);
			$statement->execute();
			
			return;
			// end if new user, continue if existing
		}
		
		
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


	} elseif($action === "deleteUser"){
			$user = json_decode($_GET['user'], true);



			if(isset($user['userid']) && is_numeric($user['userid']) && $user['userid']>0) {  
				$query = "DELETE FROM `users` WHERE userid = ".$user['userid']." AND username = '".$user['username']."'";
				$statement = $configdb->prepare($query);
				$statement->execute();
			}		


			
			
	} elseif($action === "scanAddons"){

		$addonFolders = scandir("../addons");
		$addonArray = array();
		$i=0;
		foreach($addonFolders as $key => $item){
			if($item=='.' || $item=='..') { 
				unset($addonFolders[$key]);
				continue;
			}
			$i++;
			$addonArray[$i]['addonid']=$item;
			$addonArray[$i]['id']=$i;
			
		}
		$addons=json_decode(ltrim(GetAddons($configdb),")]}',\n"),true);
		foreach($addonArray as $addonFolder){
			$inarray=0;
			$notinarray=0;
			foreach($addons as $addon){
				if(in_array($addonFolder['addonid'], $addon)){
					$inarray++;
				} else {
					$notinarray++;
				}
			}
			if($inarray>0){
				unset($addonArray[$addonFolder['id']]);
			}
		
			
		}
		//echo "add these addons";
		//print_r($addonArray);
		foreach($addonArray as $newaddon){
			$addoninfo = GetAddonInfo($newaddon['addonid']);
			$addoninfo = json_decode($addoninfo, true);
			$addonArray[$newaddon['id']]['info']=$addoninfo['info'];
			

			
			
		}
	
		foreach($addonArray as $newaddon){
			$query = "INSERT INTO `addons` (";
			
			foreach($newaddon as $setting => $setas){
				if($setting==='id'){ continue; }
				if($setting!='addonid') {
					$query .= ",";
				}
				$query .= $setting;
			}
			
			$query .= ") VALUES (";
			
			foreach($newaddon as $setting => $setas){
				if($setting==='id'){ continue; }
				if($setting!='addonid') {
					$query .= ",";
				}
				$query .= "'$setas'";
			}			
			$query.= ")";

			$statement = $configdb->prepare($query);
			$statement->execute();
		}			
			
			
			

	} elseif($action === "saveAddon"){
		$addon = json_decode($_GET['addon'], true);
		
		$query = "UPDATE `addons` SET ";

		foreach($addon as $setting => $setas){
			if($setting==='id'){ continue; }
			if($setting==='addonid'){ continue; }
			if($setting==='info'){ continue; }
			if($setting!='globalDisable') {
				$query .= ", ";
			}
			$query .= "$setting = '$setas'";
		}

		$addonid = $addon['addonid'];
		$query .= " WHERE addonid = '$addonid'";
		$statement = $configdb->prepare($query);
		$statement->execute();		


	} elseif($action === "saveRooms"){
		$Rooms = json_decode($_GET['data'], true);

		foreach($Rooms as $room){
			if($room['roomId']=="groups"){continue;}
			$query = "UPDATE `rooms` SET ";

			foreach($room as $setting => $setas){
				if($setting==='roomId'){ continue; }
				if($setting=='$$hashKey') { continue; }
				if($setting!='roomName') {
					$query .= ", ";
				}
				$query .= "$setting = '$setas'";
			}

			$roomId = $room['roomId'];
			$query .= " WHERE roomId = '$roomId'";
			$statement = $configdb->prepare($query);
			$statement->execute();
		}
		
		
	} elseif($action === "saveRoom"){
		
		$Room = json_decode($_GET['data'], true);
		
		$roomarray[$Room['roomId']] = $Room;
		$origrooms=json_decode(ltrim(GetRooms($configdb),")]}',\n"),true);
		
		$result = array_diff_key($roomarray, $origrooms);		
		// new room add		
		foreach($result as $newroom){
			$query = "INSERT INTO `rooms` (";
			
			foreach($newroom as $setting => $setas){
				if($setting==='roomId'){ continue; }
				if($setting!='roomName') {
					$query .= ",";
				}
				$query .= $setting;
			}
			
			$query .= ") VALUES (";
			
			foreach($newroom as $setting => $setas){
				if($setting==='roomId'){ continue; }
				if($setting!='roomName') {
					$query .= ",";
				} else {
					$setas = preg_replace('/[^a-zA-Z0-9]/', '', $setas);
				}
				$query .= "'$setas'";
			}			
			$query.= ")";

			$statement = $configdb->prepare($query);

			$statement->execute();
			
			return;
			// end if new room, continue if existing
		}
		
		
		$query = "UPDATE `rooms` SET ";

		foreach($Room as $setting => $setas){
			if($setting==='roomId'){ continue; }
			if($setting!='roomName') {
				$query .= ", ";
			}
			$query .= "$setting = '$setas'";
		}

		$roomId = $Room['roomId'];
		$query .= " WHERE roomId = '$roomId'";
		$statement = $configdb->prepare($query);
		$statement->execute();
		
		
	} elseif($action === "deleteRoom"){
			$Room = json_decode($_GET['data'], true);

			if(isset($Room['roomId']) && is_numeric($Room['roomId']) && $Room['roomId']>0) {  
				$query = "DELETE FROM `rooms` WHERE roomid = ".$Room['roomId']." AND roomName = '".$Room['roomName']."'";
				$statement = $configdb->prepare($query);
				$statement->execute();
			}	
			
	
		
		
		
		
		
		

	} elseif($action === "saveNavigation"){
		$Navigation = json_decode($_GET['data'], true);
		
		$navarray[$Navigation['navid']] = $Navigation;
		$orignav=json_decode(ltrim(GetNavigation($configdb),")]}',\n"),true);
		
		$result = array_diff_key($navarray, $orignav);		
		// new navigation add		
		foreach($result as $newnav){
			$query = "INSERT INTO `navigation` (";
			
			foreach($newnav as $setting => $setas){
				if($setting==='navid'){ continue; }
				if($setting!='navname') {
					$query .= ",";
				}
				$query .= $setting;
			}
			
			$query .= ") VALUES (";
			
			foreach($newnav as $setting => $setas){
				if($setting==='navid'){ continue; }
				if($setting!='navname') {
					$query .= ",";
				} else {
					$setas = preg_replace('/[^a-zA-Z0-9]/', '', $setas);
				}
				$query .= "'$setas'";
			}			
			$query.= ")";

			$statement = $configdb->prepare($query);

			$statement->execute();
			
			return;
			// end if new navigation, continue if existing
		}
		
		
		$query = "UPDATE `navigation` SET ";

		foreach($Navigation as $setting => $setas){
			if($setting==='navid'){ continue; }
			if($setting!='navname') {
				$query .= ", ";
			}
			$query .= "$setting = '$setas'";
		}

		$navid = $Navigation['navid'];
		$query .= " WHERE navid = '$navid'";
		$statement = $configdb->prepare($query);
		$statement->execute();
		
		
	} elseif($action === "deleteNavigation"){
			$Navigation = json_decode($_GET['Navigation'], true);

			if(isset($Navigation['navid']) && is_numeric($Navigation['navid']) && $Navigation['navid']>0) {  
				$query = "DELETE FROM `navigation` WHERE navid = ".$Navigation['navid']." AND navname = '".$Navigation['navname']."'";
				$statement = $configdb->prepare($query);
				$statement->execute();
			}	
			
	}
}
?>