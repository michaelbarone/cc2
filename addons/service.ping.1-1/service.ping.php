<?php
class ping {
		
	private $IP;

	function AddonInfo(){
		$info = array();
		$info['type']="service";
		$info['name']="ping";
		$info['version']="1-1";  // version matches the 3rd section of the folder name. first number relates to compatibility with connecting api (will be updates when that app updates).  second number is the revision for this class.
		$info['info']="Addon that pings an ip and reports status.";  // brief description and list compatible versions of the connecting app
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
		//return $this->Ping();
		return;
	}
	
	function PingApp($ip){
		//return $this->Ping($ip,1);
		return;
	}	

	function PowerOn(){
		return "false";
	}
	function PowerOff(){
		return "false";
	}	
	
}
?>