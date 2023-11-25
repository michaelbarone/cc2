<?php
class kodi {
		
	private $IP;
	private $MAC;

	function AddonInfo(){
		$info = array();
		$info['type']="mediaplayer";
		$info['name']="kodi";
		$info['version']="6-1";  // version matches the 3rd section of the folder name. first number relates to compatibility with connecting api (will be updates when that app updates).  second number is the revision for this class.
		$info['info']="Supports Kodi JSON-RPC v6 for Frodo, Gotham, Helix, Isengard, and Jarvis.";  // brief description and list compatible versions of the connecting app
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
		$this->MAC = $vars['mac'];
	}
	
	function setIp($ip) {
		$this->IP = $ip;
	}

	private function Curl($content){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, "$content");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
		$output = curl_exec($ch);
		return $output;
	}

	function PowerOn(){
		return "wol";
	}
	
	function PowerOff(){
		$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"System.Shutdown\", \"id\": \"1\"");
		$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
		$this->Curl($jsoncontents);
	}

	function GetActivePlayer() {
		$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetActivePlayers\", \"id\": \"1\"");
		$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
		$output = $this->Curl($jsoncontents);
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

	function GetAddonInfo() {
		return $this->GetPlayingItemInfo();
	}
	
	function GetPlayingItemInfo() {
		$activeplayerid = $this->GetActivePlayer();
		$nowplayingarray = Array();
		if($activeplayerid=="0") {
			$filetype='';
			$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetItem\", \"params\": { \"properties\": [\"art\",\"album\",\"artist\",\"director\",\"writer\",\"tagline\",\"episode\",\"file\",\"title\",\"showtitle\",\"season\",\"genre\",\"year\",\"rating\",\"runtime\",\"firstaired\",\"plot\",\"fanart\",\"thumbnail\",\"tvshowid\"], \"playerid\": 0 }, \"id\": \"1\"");
			$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
			$output = $this->Curl($jsoncontents);
			$jsonnowplaying = json_decode($output,true);
			$jsonnowplaying['status']='alive';
			return $jsonnowplaying;
		} elseif($activeplayerid=="1") {
			$playingTime = $this->GetPlayingTimeInfo($activeplayerid);
			$nowplayingarray['time'] = json_encode($playingTime);
				
				
			// $therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetItem\", \"params\": { \"properties\": [\"art\",\"director\",\"writer\",\"tagline\",\"episode\",\"file\",\"title\",\"showtitle\",\"season\",\"genre\",\"year\",\"rating\",\"runtime\",\"firstaired\",\"plot\",\"fanart\",\"thumbnail\",\"tvshowid\"], \"playerid\": 1 }, \"id\": \"1\"");
			
			// 			$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["title","artist","albumartist","genre","year","rating","album","track","duration","comment","lyrics","musicbrainztrackid","musicbrainzartistid","musicbrainzalbumid","musicbrainzalbumartistid","playcount","fanart","director","trailer","tagline","plot","plotoutline","originaltitle","lastplayed","writer","studio","mpaa","cast","country","imdbnumber","premiered","productioncode","runtime","set","showlink","streamdetails","top250","votes","firstaired","season","episode","showtitle","thumbnail","file","resume","artistid","albumid","tvshowid","setid","watchedepisodes","disc","tag","art","genreid","displayartist","albumartistid","description","theme","mood","style","albumlabel","sorttitle","episodeguide","uniqueid","dateadded","channel","channeltype","hidden","locked","channelnumber","starttime","endtime"], "playerid": 1 }, "id": "1"');
			
			$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["title","genre","year","rating","duration","comment","playcount","fanart","director","trailer","tagline","plot","plotoutline","lastplayed","writer","studio","mpaa","cast","country","imdbnumber","premiered","runtime","set","showlink","streamdetails","firstaired","season","episode","showtitle","thumbnail","file","resume","tvshowid","watchedepisodes","disc","tag","art","genreid","albumartistid","description","theme","sorttitle","episodeguide","dateadded","channel","channeltype","channelnumber","starttime","endtime"], "playerid": 1 }, "id": "1"');
			
			$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
			$output = $this->Curl($jsoncontents);
			$jsonnowplaying = json_decode($output,true);
			if(is_array($jsonnowplaying['result']['item'])) {
				foreach($jsonnowplaying['result']['item'] as $item=>$value) {
					if($value == "" || $value == "0" || $item =="0") { continue; }
					
					//  special cases
					if($item == "fanart" || $item == "thumbnail") {
						if($value == '') { continue; }
						/*if($jsonnowplaying['result']['item']['type']=="movie") {
							if($item=="thumbnail"){
								$item="fanart";
							}else {
								$item="thumbnail";
							}
						}*/
						$nowplayingarray['displayInfo'][$item] = "$this->IP/image/".urlencode($value);
					} elseif($item == "cast"){
						if(empty($value)) { continue; }
						foreach($value as $key => $item){
		
							foreach($item as $thiskey => $thisitem){
								if (!is_array($thisitem) && substr($thisitem, 0, 8) == 'image://') {
									$nowplayingarray['displayInfo']['cast'][$key][$thiskey] = "$this->IP/image/".urlencode($thisitem);
								} else {
									$nowplayingarray['displayInfo']['cast'][$key][$thiskey] = $thisitem;
								}	
							}
		
		
							

						}


					} elseif(is_array($value)) {
						if(empty($value)) { continue; }
						foreach($value as $key => $item){
								
							if (!is_array($item) && substr($item, 0, 8) === 'image://') {
								if(substr($key, 0, 7) === 'tvshow.') {
									$key = ltrim($key, 'tvshow.');
								}
								//if (strpos($item, 'image://') !== false) {
								//	$item = ltrim($item, 'image://');
									//$item = urldecode($item);
								//}
								if(substr($key, 0, 6) === 'fanart') {
									$nowplayingarray['displayInfo']['images']['fanart'][$key] = "$this->IP/image/".urlencode($item);
								} else {
									$nowplayingarray['displayInfo']['images'][$key] = "$this->IP/image/".urlencode($item);
								}
							} else {
								$nowplayingarray['displayInfo'][$key] = $item;
							}
						}
						
						/*
						$nowplayingarray[$item] = implode(",",$value);
						*/
						
					} else {  // normal cases, everything else
						$nowplayingarray['displayInfo'][$item] = $value;
					}
				}
		
				if($jsonnowplaying['result']['item']['type'] == "unknown") {
					$tvshowneedles = array('1x','2x','3x','4x','5x','6x','7x','8x','9x','0x','s0','S0','00E','00e','e0','E0','e1','E1');
					$ext = pathinfo($jsonnowplaying['result']['item']['file'], PATHINFO_EXTENSION);
					$file = basename($jsonnowplaying['result']['item']['file'], ".".$ext);
					$needles = $tvshowneedles;
					foreach($needles as $needle) {
						if (strpos($file,$needle) !== false) {
							$nowplayingarray['displayInfo']['type'] = "tv";
							$nowplayingarray['displayInfo']['title'] = "$file";							
							break;
						}
					}
					$movieneedles = array('(19','(20','[19','[20');				
					$needles = $movieneedles;
					foreach($needles as $needle) {
						if (strpos($file,$needle) !== false) {
							$nowplayingarray['displayInfo']['type'] = "movie";
							$nowplayingarray['displayInfo']['title'] = "$file";
							break;
						}
					}
				}
					
					
				if($jsonnowplaying['result']['item']['type'] == "channel") {
					$nowplayingarray['displayInfo']['type'] = "tv";
					$nowplayingarray['displayInfo']['channel'] = $jsonnowplaying['result']['item']['label'];
					$nowplayingarray['displayInfo']['runtime'] = $nowplayingarray['displayInfo']['runtime'] . " minutes";
					$nowplayingarray['displayInfo']['info'] = $jsonnowplaying['result']['item']['title'];
				} else {
					if(isset($nowplayingarray['displayInfo']['runtime']) && $nowplayingarray['displayInfo']['runtime'] != '') {
						$nowplayingarray['displayInfo']['runtime'] = round($nowplayingarray['displayInfo']['runtime']/60) . " minutes";
					}
				}
				
				
				if(isset($nowplayingarray['displayInfo']['title']) && $nowplayingarray['displayInfo']['title']!='') {
					if(isset($nowplayingarray['displayInfo']['showtitle']) && $nowplayingarray['displayInfo']['showtitle']!='') {
						$episode = "";
						if(isset($nowplayingarray['displayInfo']['season']) && $nowplayingarray['displayInfo']['season']!='' && isset($nowplayingarray['displayInfo']['episode']) && $nowplayingarray['displayInfo']['episode']!='') {
							$episode = " " . $nowplayingarray['displayInfo']['season'] . "x" . $nowplayingarray['displayInfo']['episode'];
						}
						$nowplayingarray['displayInfo']['info'] = $nowplayingarray['displayInfo']['showtitle'] . $episode . " - " . $nowplayingarray['displayInfo']['title'];
					} elseif(isset($nowplayingarray['displayInfo']['year']) && $nowplayingarray['displayInfo']['year']!='') {
						$nowplayingarray['displayInfo']['info'] = $nowplayingarray['displayInfo']['title'] . " (" . $nowplayingarray['displayInfo']['year'] . ")";
					}
				}				
				
				
				
				
				$nowplayingarray['displayInfo']=json_encode($nowplayingarray['displayInfo']);
				
				
				$nowplayingarray['status']="alive";
				return $nowplayingarray;
			}

		} elseif($activeplayerid=="2") {
			echo "pics";
			//return $jsonnowplaying;
		} elseif($activeplayerid=="none" || $activeplayerid=="-1") {
			$nowplayingarray['status']="alive";
			return $nowplayingarray;
		} else {
			return "failed";
		}
	}


	
	function GetPlaylistInfo() {
		$activeplayerid = $this->GetActivePlayer();
		if($activeplayerid==0) {
			$jsoncontents = "$this->IP/jsonrpc?request={%22jsonrpc%22:%20%222.0%22,%20%22method%22:%20%22Playlist.GetItems%22,%20%22params%22:%20{%20%22playlistid%22:%200%20},%20%22id%22:%20%221%22}";
			$output = $this->Curl($jsoncontents);
			$jsonplaylist = json_decode($output,true);								
		} elseif($activeplayerid==1) {
			$jsoncontents = "$this->IP/jsonrpc?request={%22jsonrpc%22:%20%222.0%22,%20%22method%22:%20%22Playlist.GetItems%22,%20%22params%22:%20{%20%22playlistid%22:%201%20},%20%22id%22:%20%221%22}";
			$output = $this->Curl($jsoncontents);
			$jsonplaylist = json_decode($output,true);
			//print_r($jsonplaylist);
			//$test = in_array("$thelabel",$jsonplaylist);
		/*	echo $thelabel;
			if(in_array_like("$thelabel",$jsonplaylist)){
			echo "in the array";
			} else { echo "not in array"; }*/

		} elseif($activeplayerid==2) {
			echo "pics";
			//return $jsonnowplaying;
		}
	}
	
	
	
	
	
	
	function GetPlayingTimeInfo($activeplayerid) {
		//$activeplayerid = $this->GetActivePlayer();
		$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetProperties\", \"params\": { \"properties\": [\"time\",\"totaltime\",\"position\",\"speed\"], \"playerid\": $activeplayerid }, \"id\": \"1\"");
		$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
		$output = $this->Curl($jsoncontents);
		$jsonnowplayingtime = json_decode($output,true);
		
		if(!isset($jsonnowplayingtime['result'])) {
			//if($showtime!=0) {
				return "Playback Ended";
			//}
		}
		$thecurtime = implode(', ', $jsonnowplayingtime['result']['time']);
		$thetotaltime = implode(', ', $jsonnowplayingtime['result']['totaltime']);
		$thecurtime = explode(',',$thecurtime);
		$thetotaltime = explode(',',$thetotaltime);
		
		$PlayingTime = Array();

		// this needs to get pulled into user preferences some how
		date_default_timezone_set('America/Los_Angeles');
		
		$PlayingTime['currenttimesec'] = ($thecurtime[0]*3600)+($thecurtime[2]*60)+$thecurtime[3];
		$PlayingTime['thetotaltimesec'] = ($thetotaltime[0]*3600)+($thetotaltime[2]*60)+$thetotaltime[3];
		$PlayingTime['timeleft'] = $PlayingTime['thetotaltimesec'] - $PlayingTime['currenttimesec'];
		$PlayingTime['endtime'] = date("h:i a", time() + $PlayingTime['timeleft']);
		$PlayingTime['timenow'] = date("h:i a", time());
		$PlayingTime['currenttimesec'] +=1.55;
		$PlayingTime['playerpercentage'] = round($PlayingTime['currenttimesec'] / $PlayingTime['thetotaltimesec'] * 100,1, PHP_ROUND_HALF_UP);
		
		return $PlayingTime;
	}

	
	
	function PlayMedia($video,$activeplayerid='1',$playtype=false,$playerpercentage=false){
		
		if($playtype=="youtube") {
			$therequest = urlencode('"jsonrpc": "2.0", "method": "Playlist.Clear", "params": { "playlistid": 1 }');
			$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
			
			$therequest = urlencode('"jsonrpc": "2.0", "id": "'.$activeplayerid.'", "method": "Player.Open", "params": { "item": {"file":"plugin://plugin.video.youtube/?action=play_video%26videoid='.$video.'"}}');
			$jsoncontents .= "====$this->IP/jsonrpc?request={".$therequest."}";
			
			
			// $jsoncontents .= "====$to/jsonrpc?request={\"jsonrpc\":\"2.0\",\"method\":\"Player.Open\",\"params\":{\"item\":{\"file\":\"plugin://plugin.video.youtube/?action=play_video%26videoid=$video\"}},\"id\":\"1\"}";
		} else {
			
			
/*			
			$activeplayerid = $this->GetActivePlayer();
			
			
			
			$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["file"], "playerid": 1 }, "id": "1"');
			$jsoncontents = "$from/jsonrpc?request={".$therequest."}";
			$output = $this->Curl($jsoncontents);
			$jsonnowplaying = json_decode($output,true);
			$filepath = $jsonnowplaying['file'];
			
			$playingtime=$this->GetPlayingTimeInfo();
			$playerpercentage=$playingtime['playerpercentage'];
*/			
			$jsoncontents = '';
			
			//if($activeplayerid==0) {
			//} elseif($activeplayerid==1) {
				//if($thelabel !in playlist array || !isset(playlist array)) {
				
				$therequest = urlencode('"jsonrpc": "2.0", "id": "'.$activeplayerid.'", "method": "Player.Open", "params": { "item": {"file":"'.$video.'"}}');
				$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";

				
				if($playerpercentage!=false && $playtype!="play"){   // {"jsonrpc":"2.0","method":"Player.Seek","params":{ "playerid":1,"value":"smallforward"},"id":1}
					$therequest = urlencode('"jsonrpc": "2.0", "id": "'.$activeplayerid.'", "method": "Player.Seek", "params": { "playerid": '.$activeplayerid.', "value": '.$playerpercentage.'}');
					$jsoncontents .= "====$this->IP/jsonrpc?request={".$therequest."}";
				}

				
			//} elseif($activeplayerid==2) {
				
			//}
		}
		
		$contents = explode("====",$jsoncontents);
		foreach($contents as $content) {
			$output = $this->Curl($content);
		}
				
				
				
				
	}
	
	
	function SendMedia($sendtype=false){
		$activeplayerid = $this->GetActivePlayer();
		if($activeplayerid<=2 && $activeplayerid>=0){
			$nowplayingarray=Array();
			
			$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["file"], "playerid": 1 }, "id": 1');
			$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
			$output = $this->Curl($jsoncontents);
			$jsonnowplaying = json_decode($output,true);
			if(isset($jsonnowplaying['error'])) { echo "parse error";return; }
			$nowplayingarray['file']=$jsonnowplaying['result']['item']['file'];
			$nowplayingarray['activeplayerid']=$activeplayerid;
			if($nowplayingarray['activeplayerid']=="none") {

			} else {
				if($sendtype!="play"){
					$playingtime=$this->GetPlayingTimeInfo($activeplayerid);
					$nowplayingarray['playerpercentage']=$playingtime['playerpercentage'];
				}
				
				if($sendtype=="clone" || $sendtype=="play"){
				} else {
					$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.Stop", "params": { "playerid": '.$activeplayerid.'}');
					$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
					$output = $this->Curl($jsoncontents);
				}
			}
			
			return $nowplayingarray;
		}
			
			
			
	}

}
$included_files = get_included_files();
if($included_files[0]==__FILE__){
	$thisclass = new kodi();
	$addoninfo = $thisclass->AddonInfo();
	echo $addoninfo;
}
?>