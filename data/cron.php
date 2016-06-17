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
		echo "failed";
		$log->LogError("$e->getMessage()" . basename(__FILE__));
	}

$echoTakeover=0;
if($lastcron < ($time - 30)) {
	echo "takeover";
	$echoTakeover=1;
	$log->LogInfo("Cron taken over by user " . $_SESSION['username']);
} else if(($lastcron + 4) > $time) {
	echo "release";
	exit;
}
//////   Cron items

function curl_post_async($url, $params)
{
	foreach ($params as $key => &$val) {
		if (is_array($val)) $val = implode(‘,’, $val);
		$post_params[] = $key . "=" . urlencode($val);
	}
	$post_string = implode("&", $post_params);
	
	$parts=parse_url($url);
	
	$fp = fsockopen($parts['host'],
	isset($parts['port'])?$parts['port']:80,
	$errno, $errstr, 30);
	
	//pete_assert(($fp!=0), "Couldn’t open a socket to ".$url." (".$errstr.")");
	
	$out = "POST ".$parts['path']." HTTP/1.1\r\n";
	$out.= "Host: ".$parts['host']."\r\n";
	$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$out.= "Content-Length: ".strlen($post_string)."\r\n";
	$out.= "Connection: Close\r\n\r\n";
	if (isset($post_string)) $out.= $post_string;
	
	fwrite($fp, $out);
	fclose($fp);
}



//  check addon alive status for all enabled users' addons that have been checked recently
$URL = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
$URL = $URL."/cron-addon.php";
try {
	$addons=array();
	$sql = "SELECT addons.*,settings.globalDisable,settings.controlWindow,info.* FROM rooms_addons as addons 
			LEFT JOIN rooms_addons_global_settings as settings ON addons.addonid = settings.addonid 
			LEFT JOIN rooms_addons_info as info ON addons.rooms_addonsid = info.rooms_addonsid
			WHERE addons.enabled ='1' AND settings.globalDisable='0';";
	foreach ($configdb->query($sql) as $row) {
		if(($row['lastCheck']+60) < $time && $row['lastCheck']!='') { continue; }
		curl_post_async($URL, $row);
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

if($echoTakeover===0) {
	echo "completed";
}
?>