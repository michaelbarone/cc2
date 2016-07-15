<?php 
	require 'startsession.php';
	
	
	//  will require better auth checking
	
	
	
	if( isset($_SESSION['uid']) ) {
		print 'passedAuth';
	} else if( isset($_SESSION['firstrun']) ) {
		print 'firstrun';
	} else {
		print 'failedAuth';
	}
?>