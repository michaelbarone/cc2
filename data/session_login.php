<?php 
	require 'startsession.php';
	$user=json_decode(file_get_contents('php://input'),true);  //get user from login page
	if(!$user) {
		$log->LogWARN("Login FAILED no user info set from " . basename(__FILE__));
		print "failed1";
		exit;
	}
	if($user['username'] && $user['userid']) {
		$username = $user['username'];
		$userid = $user['userid'];
		try {
			$sql = "SELECT password,passwordv,homeRoom,settingsAccess,wanAccess,avatar,disabled FROM users WHERE username = '$username' AND userid = '$userid' LIMIT 1";
			foreach ($configdb->query($sql) as $row) {
				$password=$row['password'];
				$passwordv=$row['passwordv'];
				$homeRoom=$row['homeRoom'];
				$settingsAccess=$row['settingsAccess'];
				$wanAccess=$row['wanAccess'];
				$avatar=$row['avatar'];
				$disabled=$row['disabled'];
			}
		} catch(PDOException $e)
			{
			$log->LogFatal("User $username could not open DB: $e->getMessage().  from " . basename(__FILE__));
			print "failed2";
			exit;
			}		
		$check=false;
		if($disabled==0){
			if($passwordv > 0 && $password != '') {
				if(isset($user['pass'])) {
					$userpass=$user['pass'];
					switch($passwordv) {
						case "1":
							if($password==$userpass) {
								$check=true;						
							}
							break;				
						case "2":
							require "../lib/php/PasswordHash.php";
							$hasher = new PasswordHash(8, false);
							if (strlen($userpass) > 72) { $userpass = substr($userpass,0,72); }
							$stored_hash = "*";
							$stored_hash = "$password";
							$check = $hasher->CheckPassword($userpass, $stored_hash);
							break;
					}
				} else { 
					$check=false;
				}
			} elseif($user['passwordset']=='0' && !isset($user['pass']) && $passwordv == 0) {
				//no password set
				$check=true;
			} else {
				$check=false;
			}
		} else {
			$log->LogWARN("Login FAILED account:$username - is disabled " . basename(__FILE__));
			print "failed3";
			exit;			
		}
		if($check){
			$_SESSION['uid']=uniqid('cc_');
			$_SESSION['username']=$user['username'];
			$_SESSION['userid']=$user['userid'];
			$_SESSION['homeRoom']=$homeRoom;
			$_SESSION['settingsAccess']=$settingsAccess;
			$_SESSION['wanAccess']=$wanAccess;
			$_SESSION['avatar']=$avatar;
			require_once "../lib/php/mobile_device_detect.php";
			if(mobile_device_detect(true,false,true,true,true,true,true,false,false) ) {
				$_SESSION['mobile']='1';
				$log->LogInfo("Login Success in mobile mode by " . $user['username'] . " from " . basename(__FILE__));
			} else {
				$_SESSION['mobile']='0';
				$log->LogInfo("Login Success in full mode by " . $user['username'] . " from " . basename(__FILE__));
			}
			$json=json_encode($_SESSION);
			print_r($json);
		} else {
			$log->LogWARN("Login FAILED bad credentials for " . $user['username'] . " from " . basename(__FILE__));
			print "failed4";
			exit;
		}
	} else {
		$log->LogWARN("Login FAILED no credentials from " . basename(__FILE__));
		print "failed5";
		exit;		
	}
?>