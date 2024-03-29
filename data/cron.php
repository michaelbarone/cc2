<?php
$testing = 0;
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
$cron = array();
try {
	$sql = "select CCsetting,CCvalue from controlcenter";
	foreach ($configdb->query($sql) as $c) {
		$s = $c['CCsetting'];
		$cron[$s] = $c['CCvalue'];
	}
} catch(PDOException $e)
	{
		echo "failed";
		$log->LogError("$e->getMessage()" . basename(__FILE__));
		exit;
	}
try {
	$execquery = $configdb->exec("UPDATE users SET lastaccess=$time WHERE userid = '$userid';");
} catch(PDOException $e)
{
	$log->LogError("$e->getMessage()" . basename(__FILE__));
}
$echoTakeover=0;
if($cron['lastcrontime'] < ($time - 15)) {
	$cron['status']="takeover";
	$echoTakeover=1;
	$log->LogInfo("Cron taken over by user " . $_SESSION['username']);
} else if(($cron['lastcrontime'] + 3) > $time) {
	$cron['status']="completed";
	//goto writeme;
	goto skiptoend;
}
//////   Cron items

function curl_post_async($url, $params, $testing)
{
	foreach ($params as $key => &$val) {
		if (is_array($val)) $val = implode(‘,’, $val);
		$post_params[] = $key . "=" . urlencode($val);
		if($testing==1){
			$_POST[$key]=$val;
		}
	}
	if($testing==1) {
		include "./cron-addon.php";
		
	} else {
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
}



//  check addon alive status for all enabled users' addons that have been checked recently
$URL = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
$URL = $URL."/cron-addon.php";
try {
	$addons=array();
	$sql = "SELECT addons.*,settings.globalDisable,settings.controlWindow FROM rooms_addons as addons 
			LEFT JOIN addons as settings ON addons.addonid = settings.addonid
			WHERE addons.enabled ='1' AND settings.globalDisable='0';";
	foreach ($configdb->query($sql) as $row) {
		if(($row['lastCheck']+60) < $time && $row['lastCheck']!='') { continue; }
		curl_post_async($URL, $row, $testing);
	}
} catch(PDOException $e)
	{
	$log->LogError("$e->getMessage()" . basename(__FILE__));
	}

//////  Cron items end
//writeme:
try {
	$execquery = $configdb->exec("INSERT OR REPLACE INTO controlcenter (CCid, CCsetting, CCvalue) VALUES (3,'lastcrontime','$time')");
} catch(PDOException $e)
	{
	$log->LogError("$e->getMessage()" . basename(__FILE__));
	}

if($echoTakeover===0) {
	$cron['status']="Cron Master";
}
skiptoend:
header('Content-Type: application/json');
$json=json_encode($cron);
echo ")]}',\n"."[".$json."]";
?>