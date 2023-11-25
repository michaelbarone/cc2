<?php 
if(!isset($_POST)) { exit; }
require 'startsession.php';
$to = $_POST['to'];
$from = $_POST['from'];
$message = $_POST['message'];
$type = $_POST['type'];

///////  sanitize $message



if($_SESSION['userid'] !== $from){
	$log->LogWarn($_SESSION['userid'] . " tried to send a message ($message) to user " . $to . " posing as user ". $from . " " . basename(__FILE__));
	exit;
}
$timenow = time();
switch ($type) {
	case 'user':
		try {
			$configdb->exec("INSERT INTO chat (sentTo,sendType,message,fromUserId,created) VALUES ($to,'user',\"$message\",$from,$timenow)");
		} catch(PDOException $e)
			{
				$log->LogError("$e->getMessage()" . basename(__FILE__));
			}
		break;

	case 'group':
		try {
			$configdb->exec("INSERT INTO chat (sentTo,sendType,message,fromUserId,created) VALUES ($to,'group',\"$message\",$from,$timenow)");
		} catch(PDOException $e)
			{
				$log->LogError("$e->getMessage()" . basename(__FILE__));
			}
		break;

	case 'room':
		try {
			$configdb->exec("INSERT INTO chat (sentTo,sendType,message,fromUserId,created) VALUES ($to,'room',\"$message\",$from,$timenow)");
		} catch(PDOException $e)
			{
				$log->LogError("$e->getMessage()" . basename(__FILE__));
			}
		break;
}
?>