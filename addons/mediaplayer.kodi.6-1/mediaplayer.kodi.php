<?php
class kodi {

	function AddonInfo(){
		$info = array();
		$info['type']="mediaplayer";
		$info['name']="kodi";
		$info['version']="6-1";  // version matches the 3rd section of the folder name. first number relates to compatibility with connecting api (will be updates when that app updates).  second number is the revision for this class.
		$info['info']="Addon that supports Kodi JSON-RPC v6 for Frodo, Gotham, Helix, Isengard, and Jarvis.";  // brief description and list compatible versions of the connecting app
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
		} else {
			$pingresult = exec("/bin/ping -c1 -w1 $thisip", $outcome, $status);
		}
		if ($status == "0") {
			return "alive";	
		} else {
			return "dead";
		}
	}
	
	
	function PingApp($ip) {	
		$pingurl = "$this->IP/jsonrpc?request={%22jsonrpc%22%3A%20%222.0%22%2C%20%22method%22%3A%20%22JSONRPC.Ping%22%2C%22id%22%3A%201}";
		//$pingurl = "$this->IP";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, "$pingurl");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
		$output = curl_exec($ch);
		if($output === FALSE) {
			return "dead";
		} else {
			return "alive";
		}
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
	
	function GetPlayingItemInfo() {
		$activeplayerid = $this->GetActivePlayer();
		$nowplayingarray = Array();
		if($activeplayerid=="0") {
			$filetype='';
			$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetItem\", \"params\": { \"properties\": [\"art\",\"album\",\"artist\",\"director\",\"writer\",\"tagline\",\"episode\",\"file\",\"title\",\"showtitle\",\"season\",\"genre\",\"year\",\"rating\",\"runtime\",\"firstaired\",\"plot\",\"fanart\",\"thumbnail\",\"tvshowid\"], \"playerid\": 0 }, \"id\": \"1\"");
			$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
			$output = $this->Curl($jsoncontents);
			$jsonnowplaying = json_decode($output,true);
			return $jsonnowplaying;
		} elseif($activeplayerid=="1") {
				
				
				
			// $therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetItem\", \"params\": { \"properties\": [\"art\",\"director\",\"writer\",\"tagline\",\"episode\",\"file\",\"title\",\"showtitle\",\"season\",\"genre\",\"year\",\"rating\",\"runtime\",\"firstaired\",\"plot\",\"fanart\",\"thumbnail\",\"tvshowid\"], \"playerid\": 1 }, \"id\": \"1\"");
			
			
			
			$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["title","artist","albumartist","genre","year","rating","album","track","duration","comment","lyrics","musicbrainztrackid","musicbrainzartistid","musicbrainzalbumid","musicbrainzalbumartistid","playcount","fanart","director","trailer","tagline","plot","plotoutline","originaltitle","lastplayed","writer","studio","mpaa","cast","country","imdbnumber","premiered","productioncode","runtime","set","showlink","streamdetails","top250","votes","firstaired","season","episode","showtitle","thumbnail","file","resume","artistid","albumid","tvshowid","setid","watchedepisodes","disc","tag","art","genreid","displayartist","albumartistid","description","theme","mood","style","albumlabel","sorttitle","episodeguide","uniqueid","dateadded","channel","channeltype","hidden","locked","channelnumber","starttime","endtime"], "playerid": 1 }, "id": "1"');
			
			$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
			$output = $this->Curl($jsoncontents);
			$jsonnowplaying = json_decode($output,true);
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
					$nowplayingarray[$item] = "$this->IP/image/".urlencode($value);
				} elseif($item == "cast"){
					if(empty($value)) { continue; }
					foreach($value as $key => $item){
	
						foreach($item as $thiskey => $thisitem){
							if (!is_array($thisitem) && substr($thisitem, 0, 8) == 'image://') {
								$nowplayingarray['cast'][$key][$thiskey] = "$this->IP/image/".urlencode($thisitem);
							} else {
								$nowplayingarray['cast'][$key][$thiskey] = $thisitem;
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
								$nowplayingarray['images']['fanart'][$key] = "$this->IP/image/".urlencode($item);
							} else {
								$nowplayingarray['images'][$key] = "$this->IP/image/".urlencode($item);
							}
						} else {
							$nowplayingarray[$key] = $item;
						}
					}
					
					/*
					$nowplayingarray[$item] = implode(",",$value);
					*/
					
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
				$movieneedles = array('(19','(20','[19','[20');				
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
	
	
	
	
	
	
	function GetPlayingTimeInfo() {
		$activeplayerid = $this->GetActivePlayer();
		$therequest = urlencode("\"jsonrpc\": \"2.0\", \"method\": \"Player.GetProperties\", \"params\": { \"properties\": [\"time\",\"totaltime\",\"position\",\"speed\"], \"playerid\": $activeplayerid }, \"id\": \"1\"");
		$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
		$output = $this->Curl($jsoncontents);
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
				
				$therequest = '"jsonrpc": "2.0", "id": "'.$activeplayerid.'", "method": "Player.Open", "params": { "item": {"file":"'.urlencode($video).'"}}';
				$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
				
				echo "<br><br>";
				print_r($jsoncontents);
				
				if($playerpercentage!=false){
					$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.Seek", "params": { "playerid": "'.$activeplayerid.'", "value": "'.$playerpercentage.'"}');
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
			print_r($jsonnowplaying);
			if(isset($jsonnowplaying['error'])) { echo "parse error";return; }
			$nowplayingarray['file']=urlencode($jsonnowplaying['result']['item']['file']);
			$nowplayingarray['activeplayerid']=$activeplayerid;
			
			$playingtime=$this->GetPlayingTimeInfo();
			$nowplayingarray['playerpercentage']=$playingtime['playerpercentage'];
			
			if($sendtype=="clone"){
			} else {
				$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.Stop", "params": { "playerid": "$activeplayerid"}');
				$jsoncontents = "$this->IP/jsonrpc?request={".$therequest."}";
				$output = $this->Curl($jsoncontents);
			}
			
			
			return $nowplayingarray;
		}
			
			
			
	}
	
	
	
	function SendMediaOLD($to,$from=false,$sendtype=false,$video=false){
		
		
		if($sendtype=="youtube") {
			$therequest = urlencode('"jsonrpc": "2.0", "method": "Playlist.Clear", "params": { "playlistid": 1 }');
			$jsoncontents = "$to/jsonrpc?request={".$therequest."}";

			$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.Open", "params": { "item": {"file":"plugin://plugin.video.youtube/?action=play_video%26videoid=$video"}, "id": "1" }');
			$jsoncontents .= "$to/jsonrpc?request={".$therequest."}";


			// $jsoncontents .= "====$to/jsonrpc?request={\"jsonrpc\":\"2.0\",\"method\":\"Player.Open\",\"params\":{\"item\":{\"file\":\"plugin://plugin.video.youtube/?action=play_video%26videoid=$video\"}},\"id\":\"1\"}";
		} else {



			$activeplayerid = $this->GetActivePlayer();
		

		
			$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["file"], "playerid": 1 }, "id": "1"');
			$jsoncontents = "$from/jsonrpc?request={".$therequest."}";
			$output = $this->Curl($jsoncontents);
			$jsonnowplaying = json_decode($output,true);
			$filepath = $jsonnowplaying['file'];
		
			$playingtime=$this->GetPlayingTimeInfo();
			$playerpercentage=$playingtime['playerpercentage'];

			$jsoncontents = '';	
			
			if($activeplayerid==0) {
				
			} elseif($activeplayerid==1) {
				//if($thelabel !in playlist array || !isset(playlist array)) {
				
				$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.Open", "params": { "item": {"file":"$filepath"}, "id": "1" }');
				$jsoncontents .= "$to/jsonrpc?request={".$therequest."}";

				//$jsoncontents = "$to/jsonrpc?request=%7B%22jsonrpc%22:%222.0%22,%22id%22:%221%22,%22method%22:%22Player.Open%22,%22params%22:%7B%22item%22:%7B%22file%22:%22$filepath%22%7D%7D%7D";
				if($sendtype=="clone") {

					$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.Seek", "params": { "playerid": 1, "value": "$playerpercentage"}');
					$jsoncontents .= "$to/jsonrpc?request={".$therequest."}";


					//$jsoncontents .= "====$to/jsonrpc?request=%7B%22jsonrpc%22:%222.0%22,%22id%22:1,%22method%22:%22Player.Seek%22,%22params%22:%7B%22playerid%22:1,%22value%22:$playerpercentage%7D%7D";
				} else {
					
					$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.Seek", "params": { "playerid": "1", "value": "$playerpercentage"}');
					$jsoncontents .= "$to/jsonrpc?request={".$therequest."}";
					
					$therequest = urlencode('"jsonrpc": "2.0", "method": "Player.Stop", "params": { "playerid": "1"}');
					$jsoncontents .= "$to/jsonrpc?request={".$therequest."}";					


					//$jsoncontents .= "====$to/jsonrpc?request=%7B%22jsonrpc%22:%222.0%22,%22id%22:1,%22method%22:%22Player.Seek%22,%22params%22:%7B%22playerid%22:1,%22value%22:$playerpercentage%7D%7D";
					//$jsoncontents .= "====$from/jsonrpc?request=%7B%22jsonrpc%22:%222.0%22,%22id%22:1,%22method%22:%22Player.Stop%22,%22params%22:%7B%22playerid%22:1%7D%7D";
				}
				
				//} elseif(currentlyplayingtitle != playlist[0]title) {
				//		
				
				//} else {
				
				
				//}
			} elseif($activeplayerid==2) {
				
			}

/*
http://192.168.3.226:8080/jsonrpc?request=%7B%22jsonrpc%22:%222.0%22,%22id%22:%221%22,%22method%22:%22Player.Open%22,%22params%22:%7B%22item%22:%7B%22file%22:%22plugin://plugin.video.emby.movies/?dbid=14&mode=play&id=8f02d225f46c0aaf7bd9e199f2c955a2&filename=21+Jump+Street+%282012%29.mkv%22%7D%7D%7D"
	

http://192.168.3.226:8080/jsonrpc?request={"jsonrpc":"2.0","id":"1","method":"Player.Open","params":{"item":{"file":"plugin://plugin.video.emby.movies/?dbid=14&mode=play&id=8f02d225f46c0aaf7bd9e199f2c955a2&filename=21+Jump+Street+%282012%29.mkv"}}}
*/	
				
		}
		
		$contents = explode("====",$jsoncontents);
		foreach($contents as $content) {
			$output = $this->Curl($content);
		}
		
		
		
	}
	
	
	
	
}



?>