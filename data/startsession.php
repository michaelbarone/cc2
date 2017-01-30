<?php
if(!isset($PRIVATE_DATA)) {
	$found = false;
	$path = './private';
	while(!$found){
		if(file_exists($path)){ 
			$found = true;
			$PRIVATE_DATA = $path;
		}
		else{ $path = '../'.$path; }
	}
}

require_once "../lib/php/KLogger.php";
$date = date('Y-m-d');
// klogger options: DEBUG, INFO, WARN, ERROR, FATAL, OFF
$log = new KLogger ( $PRIVATE_DATA."/logs/log-$date.log" , KLogger::INFO );

// Do database work that throws an exception
//$log->LogError("An exception was thrown in ThisFunction()");
 
// Print out some information
//$log->LogInfo("Internal Query Time: $time_ms milliseconds");
 
// Print out the value of some variables
//$log->LogDebug("Loaded Somethings from " . $_SERVER['SCRIPT_FILENAME']);
if(!isset($cronaddon)) {
	$cronaddon=0;
}

if(!isset($_SESSION) && $cronaddon==0){
	//disable top 3 for production
	ini_set('display_errors', 'On');
	ini_set('display_startup_errors', 'On');
	ini_set('html_errors', 'On');
	ini_set('log_errors', 'On');
	ini_set('error_log', "$PRIVATE_DATA/logs/PHP_errors.log");
	ini_set('session.gc_maxlifetime', 604800);     //  604800    >>  24 hours = 86400 sec
	ini_set('session.gc_probability', 1);
	ini_set('session.gc_divisor', 100	);
	ini_set('session.save_path', $PRIVATE_DATA . "/sessions");
	ini_set('session.cookie_lifetime', 604800);
	session_start();
}

if(!isset($configdb)) {
	// first run/no db found
	$filename = $PRIVATE_DATA . '/db/config.db';
	if(!file_exists($filename)) {
		// echo "No Db found";
		require("$PRIVATE_DATA/db/dbcreate.php");
	}
	try {
		$configdb = new PDO('sqlite:' . $PRIVATE_DATA . '/db/config.db');
		$configdb->exec("pragma synchronous = off;");
		} catch (PDOException $e) {
		$log->LogFatal("Fatal: User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
}
?>