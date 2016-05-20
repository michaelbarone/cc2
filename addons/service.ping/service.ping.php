<?php
class ping {

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
}

?>