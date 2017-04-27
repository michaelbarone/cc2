<?php 
	require 'startsession.php';
	$data=json_decode(file_get_contents('php://input'),true);
	if(!$data['set']) {
		$log->LogWARN("userSet FAILED no data set from " . basename(__FILE__));
		print "failed";
		exit;
	}
	
	if($data['set']=='password'){
		if(!$data['userid'] || !$data['password'] || !$data['activeUserid']){
			print "failed";
			exit;
		}

		
		if($data['activeUserid']!='' && $data['userid']!=$data['activeUserid']){
			/* check if activeUserid is admin, then set pass  */
			try {
				$sql = "SELECT username,settingsAccess,disabled FROM users WHERE userid = ".$data['activeUserid']." LIMIT 1";
				foreach ($configdb->query($sql) as $row) {
					$activeUserName=$row['username'];
					$settingsAccess=$row['settingsAccess'];
					$disabled=$row['disabled'];
				}
			} catch(PDOException $e) {
				$log->LogFatal("Userid ".$data['activeUserid']." could not open DB: $e->getMessage().  from " . basename(__FILE__));
				print "failed";
				exit;
			}
			if((isset($settingsAccess) && $settingsAccess!='1') || (isset($disabled) && $disabled=='1')){
				print "failed";
				exit;				
			}
			if($settingsAccess!='1'){
				$log->LogWARN("PASSWORD UPDATE ATTEMPT: user $activeUserName has set a new password for userid: ".$data['userid']." from " . basename(__FILE__));
			}
		} else {
			/* user is setting own pass, check if pass exists and matches before setting new pass  */
			try {
				$sql = "SELECT username,password,passwordv FROM users WHERE userid = ".$data['userid']." LIMIT 1";
				foreach ($configdb->query($sql) as $row) {
					$username=$row['username'];
					$currentPass=$row['password'];
					$passwordv=$row['passwordv'];
				}
			} catch(PDOException $e) {
				$log->LogFatal("User ".$username." could not open DB: $e->getMessage().  from " . basename(__FILE__));
				print "failed";
				exit;
			}



			if($data['currentPassword']=='' && (isset($passwordv) && $passwordv==0)){
				/* no pass set */

			} elseif(isset($passwordv) && $passwordv==1){
				/* plain text current password */
				if($data['currentPassword']!=$currentPass){
					print "badPass";
					exit;
				}
			} elseif(isset($passwordv) && $passwordv==2){
				/* v2 password, phpass */
				require_once "../lib/php/PasswordHash.php";
				$hasher = new PasswordHash(8, false);
				$userpass = $data['currentPassword'];
				if (strlen($userpass) > 72) { $userpass = substr($userpass,0,72); }
				$stored_hash = "*";
				$stored_hash = "$currentPass";
				$check = $hasher->CheckPassword($userpass, $stored_hash);
				if(!$check){
					print "badPass";
					exit;
				}
			} else {
				print "failed";
				exit;				
			}
			$log->LogWARN("PASSWORD UPDATE ATTEMPT: user $username has set a new password from " . basename(__FILE__));
		}


		/*
			write new password to db for this user
			will need to check what passwordv the server is using (default to v2)
		*/
		require_once "../lib/php/PasswordHash.php";
		if(!isset($hasher)){
			$hasher = new PasswordHash(8, false);
		}
		$userpass = $data['password'];
		if (strlen($userpass) > 72) { $userpass = substr($userpass,0,72); }		
		$newPassHash = $hasher->HashPassword($userpass);
		$userid = $data['userid'];
		
		$query = "UPDATE `users` SET password = '".$newPassHash."', passwordv = '2' WHERE userid = '".$userid."'";
		$statement = $configdb->prepare($query);
		$statement->execute();
		print "success";
		exit;
	} elseif($data['set']=='removePassword'){
		if(!$data['userid'] || !$data['activeUserid']){
			print "failed";
			exit;
		}		
		$userid = $data['userid'];
		$auserid = $data['activeUserid'];
		
		if($userid!=$auserid){
			/* admin changing pass  */
			$log->LogWARN("PASSWORD REMOVED: user $userid has no password set by an admin user $auserid from " . basename(__FILE__));
		} else {
			/*  user changing pass  */
			$log->LogWARN("PASSWORD REMOVED: user $userid has no password set by themself from " . basename(__FILE__));
		}
		$query = "UPDATE `users` SET password = '', passwordv = '0' WHERE userid = '".$userid."'";
		$statement = $configdb->prepare($query);
		$statement->execute();
		print "success";
		exit;		
	}
?>