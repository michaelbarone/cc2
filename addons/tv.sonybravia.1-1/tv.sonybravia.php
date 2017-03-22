<?php
class sonybravia {
		
	private $IP;

	function AddonInfo(){
		$info = array();
		$info['type']="tv";
		$info['name']="sonybravia";
		$info['version']="1-1";  // version matches the 3rd section of the folder name. first number relates to compatibility with connecting api (will be updates when that app updates).  second number is the revision for this class.
		$info['info']="Supports Sony Bravia TVs.";  // brief description and list compatible versions of the connecting app
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
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'X-Auth-PSK: sony',
						'Content-Type:application/json',
						'SonyBraviaAuthHeader:X-Auth-PSK'
						));
		$output = curl_exec($ch);
		return $output;
	}

	function GetAddonInfo() {
		$returnArray=array();
		$returnArray['status']="ping";
		return $returnArray;
	}

	/*
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
	*/


	function PowerOn(){
		return "wol";
	}




	function PowerOff(){
		$therequest = urlencode('\\"id\\":2,\\"method\\":\\"setPowerStatus\\",\\"version\\":\\"1.0\\",\\"params\\":[{ \\"status\\" : false}]');
		$jsoncontents = "$this->IP/sony/system?{".$therequest."}";
		$this->Curl($jsoncontents);
		
		
		/*
		$thisip = $this->stripIp($this->IP);
		$therequest = urlencode("<?xml version='1.0' encoding='utf-8'?><s:Envelope xmlns:s='http://schemas.xmlsoap.org/soap/envelope/' s:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'><s:Body><u:X_SendIRCC xmlns:u='urn:schemas-sony-com:service:IRCC:1'><IRCCCode>AAAAAQAAAAEAAAAvAw==</IRCCCode></u:X_SendIRCC></s:Body></s:Envelope>");
		//$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutSystem_OnStandby%2FSTANDBY&cmd1=aspMainZone_WebUpdateStatus%2F";
		$curlThis = "$thisip/sony/ircc?$therequest";
		$output = $this->Curl($curlThis);
		*/
	}

	
/*	
	function PowerOff(){
		$thisip = $this->stripIp($this->IP);
		$therequest = urlencode("<?xml version='1.0' encoding='utf-8'?><s:Envelope xmlns:s='http://schemas.xmlsoap.org/soap/envelope/' s:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'><s:Body><u:X_SendIRCC xmlns:u='urn:schemas-sony-com:service:IRCC:1'><IRCCCode>AAAAAQAAAAEAAAAvAw==</IRCCCode></u:X_SendIRCC></s:Body></s:Envelope>");
		//$curlThis = "$thisip/MainZone/index.put.asp?cmd0=PutSystem_OnStandby%2FSTANDBY&cmd1=aspMainZone_WebUpdateStatus%2F";
		$curlThis = "$thisip/sony/ircc?$therequest";
		$output = $this->Curl($curlThis);
		//return $curlThis;
	}	
	*/

	
	/*
	//todo:

	
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

	function SetVideoInput(){
		
	}
	
	
	
	
	*/
}
$included_files = get_included_files();
if($included_files[0]==__FILE__){
	$thisclass = new sonybravia();
	$addoninfo = $thisclass->AddonInfo();
	echo $addoninfo;
}
?>