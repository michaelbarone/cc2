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

	function Ping($ip='',$pingApp=0) {	
		if($ip==''){
			$pingurl = $this->IP;
		}else{
			$pingurl = $ip;
		}
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
			echo "linux";
			if(isset($output[5])){
				$exoutput = explode(',',$output[5]);
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
		
		//$json = json_encode($output);
		//$result = "[".preg_replace('/\[.*?\,"",/', '', $json);
		$returnArray['data']=$result;
		$returnArray['pingApp']=$pingApp;
		$returnArray['type']="ping";
		if ($status == "0") {
			//$status = "alive";
			$returnArray['status']="alive";
		} else {
			//$status = "dead";
			$returnArray['status']="dead";
		}
		$return = $this->returnJSON($returnArray);
		return $return;
	}

	function GetAddonInfo() {
		return $this->Ping();
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