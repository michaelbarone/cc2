<?php
class ping {

	function AddonInfo(){
		$info = array();
		$info['type']="service";
		$info['name']="ping";
		$info['version']="1-1";  // version matches the 3rd section of the folder name. first number relates to compatibility with connecting api (will be updates when that app updates).  second number is the revision for this class.
		$info['info']="Addon that pings an ip and reports status.";  // brief description and list compatible versions of the connecting app
	}

	function SetVariables($vars){
		$this->IP = $vars['ip'];
	}
	
	function setIp($ip) {
		$this->IP = $ip;
	}

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
			$pingresult = exec("ping -n 1 -w 1 $thisip", $output, $status);
			// echo 'This is a server using Windows!';
		} else {
			$pingresult = exec("/bin/ping -c1 -w1 $thisip", $outcome, $status);
			// echo 'This is a server not using Windows!';
		}
		if ($status == "0") {
			//$status = "alive";
			return "alive";	
		} else {
			//$status = "dead";
			return "dead";
		}
	}
	
	function PingApp($ip){
		return $this->Ping($ip);
	}	

	function PowerOn(){
		return "false";
	}
	function PowerOff(){
		return "false";
	}	
	
}
?>