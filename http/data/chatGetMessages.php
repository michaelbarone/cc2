<?php 
require 'startsession.php';

if(!isset($_SESSION['userid']){
	$log->LogWarn("No Userid set in session " . basename(__FILE__));
	return "failed";
	exit;
}
$timenow = time();
try {
	$sql = "SELECT * FROM chat WHERE sentTo = '$_SESSION['userid']' ORDER BY chatId DSC LIMIT 100";
	foreach ($configdb->query($sql) as $thischat) {
		if($thischat['sendType']==='user'){
			// deal with user based messages
			
			
			
		} elseif($thischat['sendType']==='room'){
			// deal with room based messages
			
		} else {
			return "failed";
		}
	}
} catch(PDOException $e)
	{
		$log->LogError("$e->getMessage()" . basename(__FILE__));
		return "failed";
	}

	
	
	
	
/*
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo ")]}',\n"."[".$json."]";
*/	
	
	
	
	
	
	
	


//return message_array
?>