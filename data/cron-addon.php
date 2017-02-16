<?php
//if(!isset($_POST['rooms_addonsid']){ exit; }
$cronaddon=1;
if(!isset($configdb)) {
	require "startsession.php";
}
$rooms_addonsid=$_POST['rooms_addonsid'];
$addonid=$_POST['addonid'];
$addonparts = explode(".",$addonid);
$addonName=$addonparts[1];
$addonType=$addonparts[0];
$ip=$_POST['ip'];
$mac=$_POST['mac'];
$statusorig=$_POST['device_alive'];
$infoorig=$_POST['info'];

function Ping($ip) {
	$pingurl = $ip;
	$disallowed = array('http://', 'https://');
	foreach($disallowed as $d) {
		if(strpos($pingurl, $d) === 0) {
		   $thisip = strtok(str_replace($d, '', $pingurl),':');
		}
	}
	if(!isset($thisip)){ $thisip = $pingurl; }
	if(strpos($thisip, "/") != false) {
		$thisip = substr($thisip, 0, strpos($thisip, "/"));
	}
	if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
		$pingresult = exec("ping -n 2 -w 2 $thisip", $output, $status);
		// echo 'This is a server using Windows!';
	} else {
		$pingresult = exec("/bin/ping -c2 -w2 $thisip", $output, $status);
		// echo 'This is a server not using Windows!';
	}
	$returnArray=Array();
	$newping = array();
	$sent = 0;
	$lost = 0;
	$timeMax = 0;
	$timeAve = 0;
	if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
		if(isset($output[6])){
			$exoutput = explode(',',$output[6]);
			$sent = preg_replace('/\D/', '', $exoutput[0]);
			$lost = $sent - preg_replace('/\D/', '', $exoutput[1]);
		}
		if(isset($output[8])){
			$exoutput = explode(',',$output[8]);
			$timeMax = preg_replace('/\D/', '', $exoutput[1]);
			$timeAve = preg_replace('/\D/', '', $exoutput[2]);
		}
	} else {
		if(isset($output[5])){
			$exoutput = explode(',',$output[5]);
			$sent = preg_replace('/\D/', '', $exoutput[0]);
			$lost = $sent - preg_replace('/\D/', '', $exoutput[1]);
		}elseif(isset($output[3])){
			$exoutput = explode(',',$output[3]);
			$sent = preg_replace('/\D/', '', $exoutput[0]);
			$lost = $sent - preg_replace('/\D/', '', $exoutput[1]);
		}			
		if(isset($output[6])){
			$exoutput = explode('=',$output[6]);
			$exoutput = explode('/',$exoutput[1]);
			$timeMax = round($exoutput[2]);
			$timeAve = round($exoutput[1]);
		}			
	}
	$newping['sent']=$sent;
	$newping['lost']=$lost;
	$newping['timeMax']=$timeMax;
	$newping['timeAve']=$timeAve;
	$lastUpdate = time();
	$newping['lastUpdate']=$lastUpdate;
	$json = json_encode($newping);
	$result = $json;		
	$returnArray['data']=$result;
	if ($status == "0") {
		$returnArray['status']="alive";
	} else {
		$returnArray['status']="dead";
	}
	header('Content-Type: application/json');
	$return=json_encode($returnArray);	
	return $return;
}

if(file_exists("../addons/$addonid/$addonType.$addonName.php") && $ip !='') {
	if(!isset(${$addonid})) {
		include "../addons/$addonid/$addonType.$addonName.php";
		${$addonid} = new $addonName();
	}
	$count=0;
	$countneg=0;
	$vars = array();
	$vars['ip']=$ip;
	$vars['mac']=$mac;
	${$addonid}->SetVariables($vars);


	

	checkfalseneg3:
	if($addonName!='ping'){
		$addoninfo=array();
		$alivevalue = 0;
		$addoninfo = ${$addonid}->GetAddonInfo();
		if((isset($addoninfo['status']) && $addoninfo['status']=='alive')){
			$alivevalue = 2;
		}
	}

	checkfalseneg:
	$devicealive=array();
	$devicealive=json_decode(Ping($ip), true);
	if(isset($devicealive['status']) && $devicealive['status']=='alive') {
		if($alivevalue==2) { $alivevalue = 3; } else { $alivevalue = 1; }
		if($addonName=='ping' && $alivevalue<3) { $alivevalue = 3; }
	} else {
		$alivevalue=0;
	}
	
	
	
	// need to update this section for error checking

	$fanart="";
	$title="";
	$thumbnail="";
	$time="";
	$type="";	
	if ($alivevalue>0){
		if($count==0){
			$count++;		
			if($alivevalue!=$statusorig) {
				if($alivevalue>2 && $statusorig>0){
					goto checkfalseneg3;
				}else{
					goto checkfalseneg;
				}
			}
		}
		
		if(isset($addoninfo['title']) && $addoninfo['title']!='') {
			$title = $addoninfo['title'];
			if(isset($addoninfo['showtitle']) && $addoninfo['showtitle']!='') {
				$episode = "";
				if(isset($addoninfo['season']) && $addoninfo['season']!='' && isset($addoninfo['episode']) && $addoninfo['episode']!='') {
					$episode = " " . $addoninfo['season'] . "x" . $addoninfo['episode'];
				}
				$title = $addoninfo['showtitle'] . $episode . " - " . $addoninfo['title'];
			} elseif(isset($addoninfo['year']) && $addoninfo['year']!='') {
				$title = $addoninfo['title'] . " (" . $addoninfo['year'] . ")";
			}
			if(isset($addoninfo['thumbnail']) && $addoninfo['thumbnail']!='') {
				$thumbnail = $addoninfo['thumbnail'];
			}
			if(isset($addoninfo['fanart']) && $addoninfo['fanart']!='') {
				$fanart = $addoninfo['fanart'];
			}
		}
		if(isset($addoninfo['type'])){ $type = $addoninfo['type']; }
		if(isset($addoninfo['time'])){ $time = $addoninfo['time']; }
		
		
		// place some checks for known fails before goto writeme;
		goto writeme;
		
		
		
		if($alivevalue!=$statusorig){
			goto writeme;
			//$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = ".$alivevalue." WHERE rooms_addonsid = '$rooms_addonsid';");
		}
		if($title!=""){
			goto writeme;
		}
	} else {
		if($statusorig>0) {
			if($countneg==0){
				$countneg++;
				goto checkfalseneg;
			}
		}
		//if($alivevalue!=$statusorig){
			goto writeme;
			//$execquery = $configdb->exec("UPDATE rooms_addons SET device_alive = 0 WHERE rooms_addonsid = '$rooms_addonsid';");
		//}
	}
	
	
	
	goto skipme;	
	writeme:
	
	$statement = $configdb->prepare("UPDATE rooms_addons SET info = ?, infoType = ?, thumbnail = ?, fanart = ?, time = ?, ping = ?, device_alive = ?
			WHERE rooms_addonsid = ?");
	try {
		$statement->execute([$title, $type, $thumbnail, $fanart, $time, $devicealive['data'], $alivevalue, $rooms_addonsid]);
	} catch(PDOException $e) {
		return "Statement failed: " . $e->getMessage();
	}
	
	skipme:
}
?>