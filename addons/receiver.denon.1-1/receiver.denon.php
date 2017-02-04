<?php
class denon {
		
	private $IP;

	function AddonInfo(){
		$info = array();
		$info['type']="receiver";
		$info['name']="denon";
		$info['version']="1-1";  // version matches the 3rd section of the folder name. first number relates to compatibility with connecting api (will be updates when that app updates).  second number is the revision for this class.
		$info['info']="Addon that supports Denon Receivers.";  // brief description and list compatible versions of the connecting app
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

	private function stripIp($ip){
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
		return $thisip;
	}

	private function Curl($content){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, "$content");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
		$output = curl_exec($ch);
		return $output;
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
		// get current channel assignment (game/media/etc and zone info) to return as addonInfo
		// this should be in PingApp()..  should be pulled out like in mediaplayer.kodi.php
		return $this->PingApp();
	}

	function PingApp($ip='') {
		if($ip==''){
			$thisip = $this->stripIp($this->IP);
		}else{
			$thisip = $this->stripIp($ip);
		}
		$curlThis = "$thisip/goform/formMainZone_MainZoneXml.xml";
		$output = $this->Curl($curlThis);
		$items = simplexml_load_string($output);
		$json = json_encode($items);
		$items = json_decode($json, true);

		$returnArray=array();
		$returnArray['data']=$json;
		$returnArray['pingApp']=0;
		$returnArray['title']=$items['InputFuncSelect']['value'];
		//$returnArray['info']='receiver';
		
		$returnArray['status']="dead";
		if($items['Power']['value']==="ON"){
			//$status = "alive";
			$returnArray['status']="alive";
		} else {
			//$status = "dead";
			$returnArray['status']="dead";
		}
		//$returnArray = $this->returnJSON($returnArray);
		return $returnArray;
	}


	function PowerOn(){
		$thisip = $this->stripIp($this->IP);
		//$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutSystem_OnStandby%2FON&cmd1=aspMainZone_WebUpdateStatus%2F";
		$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutZone_OnOff%2FON";
		$output = $this->Curl($curlThis);
	}

	function PowerOff(){
		$thisip = $this->stripIp($this->IP);
		//$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutSystem_OnStandby%2FSTANDBY&cmd1=aspMainZone_WebUpdateStatus%2F";
		$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutZone_OnOff%2FOFF";
		$output = $this->Curl($curlThis);
	}
	
	function VolumeUp(){
		$thisip = $this->stripIp($this->IP);
		$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutMasterVolumeBtn%2F%3E";
		$output = $this->Curl($curlThis);		
	}

	function VolumeDown(){
		$thisip = $this->stripIp($this->IP);
		$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutMasterVolumeBtn%2F%3C";
		$output = $this->Curl($curlThis);	
	}	

	function VolumeSet($newvolume){
		$thisip = $this->stripIp($this->IP);
		$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutMasterVolumeSet/$newvolume";
		$output = $this->Curl($curlThis);		
	}
	
	function VolumeMute(){
		$thisip = $this->stripIp($this->IP);
		$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutVolumeMute/on";
		$output = $this->Curl($curlThis);		
	}
	
	/*
	//todo:
	function SetVideoInput(){
		
	}
	
	function SetZone(zoneid,setto) {
		
	}
	
	
	
	*/
}	
?>