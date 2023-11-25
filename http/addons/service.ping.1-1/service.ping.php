<?php
class ping {
		
	private $IP;

	function AddonInfo(){
		$info = array();
		$info['type']="service";
		$info['name']="ping";
		$info['version']="1-1";  // version matches the 3rd section of the folder name. first number relates to compatibility with connecting api (will be updates when that app updates).  second number is the revision for this class.
		$info['info']="Pings an ip and reports status.";  // brief description and list compatible versions of the connecting app
		$info=$this->returnJSON($info);
		return $info;
	}

	private function returnJSON($returnArray){
		header('Content-Type: application/json');
		$json=json_encode($returnArray);
		return $json;
	}
	
	function SetVariables($vars){
		$this->IP = $vars['ip'];
	}
	
	function setIp($ip) {
		$this->IP = $ip;
	}

	function GetAddonInfo() {
		$returnArray=array();
		$returnArray['status']="ping";
		return $returnArray;		/* if returns ['status']=ping, count +2 as online check in cron-addon */
	}	

	function PowerOn(){
		return "false";
	}
	function PowerOff(){
		return "false";
	}	
	
}
$included_files = get_included_files();
if($included_files[0]==__FILE__){
	$thisclass = new ping();
	$addoninfo = $thisclass->AddonInfo();
	echo $addoninfo;
}
?>