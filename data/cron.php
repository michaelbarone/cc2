<?php
require_once "startsession.php";
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
	} else {
		echo "failed";
		$log->LogWarn("No user session data from " . basename(__FILE__));
		exit;
	}
$log->LogDebug("User " . $_SESSION['username'] . " loaded " . basename(__FILE__) . " from " . $_SERVER['SCRIPT_FILENAME']);
$time = time();
try {
	$sql = "SELECT CCvalue FROM controlcenter WHERE CCsetting = 'lastcrontime' LIMIT 1";
	foreach ($configdb->query($sql) as $lastcrontime) {
		$lastcron = $lastcrontime['CCvalue'];
	}
} catch(PDOException $e)
	{
		$log->LogError("$e->getMessage()" . basename(__FILE__));
	}

if($lastcron < ($time - 45)) {
	echo "takeover";
	$log->LogInfo("Cron taken over by user " . $_SESSION['username']);
} else if(($lastcron + 4) > $time) {
	echo "release";
	exit;
}

//////   Cron items


//  check addon alive status
try {
	$sql = "SELECT * FROM rooms_addons WHERE enabled ='1'";
	foreach ($configdb->query($sql) as $row) {
		if(($row['lastCheck']+60) < $time) { continue; }
	
		$rooms_addonsid = $row['rooms_addonsid'];
		$statusorig = $row['device_alive'];
		if($row['ip'] != '') {
			$disallowed = array('http://', 'https://');
			foreach($disallowed as $d) {
				if(strpos($row['ip'], $d) === 0) {
				   $thisip = strtok(str_replace($d, '', $row['ip']),':');
				}
			}
			if(strpos($thisip, "/") != false) {
				$thisip = substr($thisip, 0, strpos($thisip, "/"));
			}
			if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
				$pingresult = exec("ping -n 1 -w 1 $thisip", $output, $status);
				// echo 'This is a server using Windows!';
			} else {
				$pingresult = exec("/bin/ping -c1 -w1 $thisip", $outcome, $status);
				// echo 'This is a server not using Windows!';
			}
			if ($status == "0") {
				if($statusorig==0) {
					$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 1 WHERE rooms_addonsid = '$rooms_addonsid';");
				}
				//$status = "alive";
			} else {
				if($statusorig==1) {
					$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
				}
				//$status = "dead";
			}
		} else {
			if($statusorig!=0) {			
				$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
			}
		}
	}
} catch(PDOException $e)
	{
	$log->LogError("$e->getMessage()" . basename(__FILE__));
	}




//////  Cron items end

try {
	$execquery = $configdb->exec("INSERT OR REPLACE INTO controlcenter (CCid, CCsetting, CCvalue) VALUES (3,'lastcrontime','$time')");
} catch(PDOException $e)
	{
	$log->LogError("$e->getMessage()" . basename(__FILE__));
	}

?>