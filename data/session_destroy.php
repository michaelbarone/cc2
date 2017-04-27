<?php 
	require_once 'startsession.php';
	if(isset($_SESSION['username'])) {
		$log->LogInfo("User " . $_SESSION['username'] . " LOGGED OUT");
	} else {
		$log->LogInfo("User LOGGED OUT");
	}
	session_unset();
	session_regenerate_id();
	session_destroy();
?>