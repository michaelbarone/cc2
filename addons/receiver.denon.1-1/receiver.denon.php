<?php
class denonAVRe400 {

	function AddonInfo(){
		$info = array();
		$info['type']="receiver";
		$info['name']="denon";
		$info['version']="1-1";  // version matches the 3rd section of the folder name. first number relates to compatibility with connecting api (will be updates when that app updates).  second number is the revision for this class.
		$info['info']="Addon that supports Denon Receivers.";  // brief description and list compatible versions of the connecting app
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
	
	function Ping($ip) {
		$thisip = $this->stripIp($this->IP);
		$curlThis = "$thisip/goform/formMainZone_MainZoneXml.xml";
		$output = $this->Curl($curlThis);
		$items = simplexml_load_string($output);
		$json = json_encode($items);
		$items = json_decode($json, true);
		if($items['Power']['value']==="ON"){
			return "alive";	
		} else {
			return "dead";
		}
	}
	
	function PingApp($ip){
		return $this->Ping($ip);
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
	
	

	
	function GetActivePlayer() {
		// get active player
		$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetActivePlayers\", \"id\": \"1\"");
		$getactiveplayer = "$this->IP/jsonrpc?request={".$therequest."}";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, "$getactiveplayer");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
		$output = curl_exec($ch);
		$jsonactiveplayer = json_decode($output,true);
		if(empty($jsonactiveplayer['result'])) {
			//echo "There is nothing currently playing.";
			return "none";
		} else {
			$activeplayerid = '-1';
			if(isset($jsonactiveplayer['result'][0]['playerid'])) {
				$activeplayerid = $jsonactiveplayer['result'][0]['playerid'];
			}
			return $activeplayerid;
		}	
	}
	
	function GetPlayingItemInfo() {
		$activeplayerid = $this->GetActivePlayer();
		$nowplayingarray = Array();
		if($activeplayerid=="0") {
			$filetype='';
			$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetItem\", \"params\": { \"properties\": [\"album\",\"artist\",\"director\",\"writer\",\"tagline\",\"episode\",\"file\",\"title\",\"showtitle\",\"season\",\"genre\",\"year\",\"rating\",\"runtime\",\"firstaired\",\"plot\",\"fanart\",\"thumbnail\",\"tvshowid\"], \"playerid\": 0 }, \"id\": \"1\"");
			$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, "$jsoncontents");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
			$output = curl_exec($ch);
			$jsonnowplaying = json_decode($output,true);
			if(isset($jsonnowplaying) && $jsonnowplaying['result']['item']['label']!='') {
				$filetype=$jsonnowplaying['result']['item']['type'];
				$theartist = $jsonnowplaying['result']['item']['artist'];
				$thealbum = $jsonnowplaying['result']['item']['album'];
				$thelabel = $jsonnowplaying['result']['item']['label'];
				$thetitle = $jsonnowplaying['result']['item']['title'];
				$theyear = $jsonnowplaying['result']['item']['year'];
				$thesongid = $jsonnowplaying['result']['item']['id'];
			}

			return $jsonnowplaying;
		} elseif($activeplayerid=="1") {
			$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetItem\", \"params\": { \"properties\": [\"director\",\"writer\",\"tagline\",\"episode\",\"file\",\"title\",\"showtitle\",\"season\",\"genre\",\"year\",\"rating\",\"runtime\",\"firstaired\",\"plot\",\"fanart\",\"thumbnail\",\"tvshowid\"], \"playerid\": 1 }, \"id\": \"1\"");
			$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, "$jsoncontents");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
			$output = curl_exec($ch);
			$jsonnowplaying = json_decode($output,true);
			foreach($jsonnowplaying['result']['item'] as $item=>$value) {
				if($value == "" || $value == "0") { continue; }
				
				//  special cases
				if($item == "fanart" || $item == "thumbnail") {
					if($value == '') { continue; }
					$nowplayingarray[$item] = "$this->IP/image/".urlencode($value);
					//$nowplayingarray[$item] = "<img src='$this->IP/image/".urlencode($value)."'/>";
				} elseif(is_array($value)) {
					if(empty($value)) { continue; }
					$nowplayingarray[$item] = implode(",",$value);

				} else {  // normal cases, everything else
					$nowplayingarray[$item] = $value;
				}
			}
	
			if($jsonnowplaying['result']['item']['type'] == "unknown") {
				$tvshowneedles = array('1x','2x','3x','4x','5x','6x','7x','8x','9x','0x','s0','S0','00E','00e','e0','E0','e1','E1');
				$ext = pathinfo($jsonnowplaying['result']['item']['file'], PATHINFO_EXTENSION);
				$file = basename($jsonnowplaying['result']['item']['file'], ".".$ext);
				$needles = $tvshowneedles;
				foreach($needles as $needle) {
					if (strpos($file,$needle) !== false) {
						$nowplayingarray['type'] = "tv";
						$nowplayingarray['title'] = "$file";							
						break;
					}
				}
			}
			if($jsonnowplaying['result']['item']['type'] == "unknown") {
				$movieneedles = array('(19','(20','[19','[20');				
				$ext = pathinfo($jsonnowplaying['result']['item']['file'], PATHINFO_EXTENSION);
				$file = basename($jsonnowplaying['result']['item']['file'], ".".$ext);
				$needles = $movieneedles;
				foreach($needles as $needle) {
					if (strpos($file,$needle) !== false) {
						$nowplayingarray['type'] = "movie";
						$nowplayingarray['title'] = "$file";
						break;
					}
				}
			}
				
				
			if($jsonnowplaying['result']['item']['type'] == "channel") {
				$nowplayingarray['type'] = "tv";
				$nowplayingarray['channel'] = $jsonnowplaying['result']['item']['label'];
				$nowplayingarray['runtime'] = $nowplayingarray['runtime'] . " minutes";
			} else {
				if(isset($nowplayingarray['runtime']) && $nowplayingarray['runtime'] != '') {
					$nowplayingarray['runtime'] = round($nowplayingarray['runtime']/60) . " minutes";
				}
			}
			

			return $nowplayingarray;		

		} elseif($activeplayerid=="2") {
			echo "pics";
			//return $jsonnowplaying;
		}
	}


	
	function GetPlaylistInfo() {
		$activeplayerid = $this->GetActivePlayer();
		if($activeplayerid==0) {
			$jsoncontents = "$this->IP/jsonrpc?request={%22jsonrpc%22:%20%222.0%22,%20%22method%22:%20%22Playlist.GetItems%22,%20%22params%22:%20{%20%22playlistid%22:%200%20},%20%22id%22:%20%221%22}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, "$jsoncontents");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
			$output = curl_exec($ch);
			$jsonplaylist = json_decode($output,true);								
		} elseif($activeplayerid==1) {
			$jsoncontents = "$this->IP/jsonrpc?request={%22jsonrpc%22:%20%222.0%22,%20%22method%22:%20%22Playlist.GetItems%22,%20%22params%22:%20{%20%22playlistid%22:%201%20},%20%22id%22:%20%221%22}";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, "$jsoncontents");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
			$output = curl_exec($ch);
			$jsonplaylist = json_decode($output,true);
			//print_r($jsonplaylist);
			//$test = in_array("$thelabel",$jsonplaylist);
		//	echo $thelabel;
		//	if(in_array_like("$thelabel",$jsonplaylist)){
		//	echo "in the array";
		//	} else { echo "not in array"; }

		} elseif($activeplayerid==2) {
			echo "pics";
			//return $jsonnowplaying;
		}
	}
	
	
	
	
	
	
	function GetPlayingTimeInfo() {
		$activeplayerid = $this->GetActivePlayer();
		$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetProperties\", \"params\": { \"properties\": [\"time\",\"totaltime\",\"position\",\"speed\"], \"playerid\": $activeplayerid }, \"id\": \"1\"");
		$jsonnowplayingtime = "$this->IP/jsonrpc?request={".$therequest."}";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, "$jsonnowplayingtime");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
		$output = curl_exec($ch);
		$jsonnowplayingtime = json_decode($output,true);
		
		if(!isset($jsonnowplayingtime['result'])) {
			if($showtime!=0) {
				return "Playback Ended";
			}
		}
		$thecurtime = implode(', ', $jsonnowplayingtime['result']['time']);
		$thetotaltime = implode(', ', $jsonnowplayingtime['result']['totaltime']);
		$thecurtime = explode(',',$thecurtime);
		$thetotaltime = explode(',',$thetotaltime);
		
		$PlayingTime = Array();

		$PlayingTime['currenttimesec'] = ($thecurtime[0]*3600)+($thecurtime[2]*60)+$thecurtime[3];
		$PlayingTime['thetotaltimesec'] = ($thetotaltime[0]*3600)+($thetotaltime[2]*60)+$thetotaltime[3];
		$PlayingTime['timeleft'] = $PlayingTime['thetotaltimesec'] - $PlayingTime['currenttimesec'];
		$PlayingTime['endtime'] = date("h:i a", time() + $PlayingTime['timeleft']);
		$PlayingTime['timenow'] = date("h:i a", time());
		$PlayingTime['currenttimesec'] +=1.55;
		$PlayingTime['playerpercentage'] = round($PlayingTime['currenttimesec'] / $PlayingTime['thetotaltimesec'] * 100,1, PHP_ROUND_HALF_UP);
		
		return $PlayingTime;
	}	*/
}








?>