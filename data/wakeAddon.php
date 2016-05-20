<?php 
	require_once 'startsession.php';
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
	} else {
		echo "failed";
		$log->LogWarn("No user session data from " . basename(__FILE__));
		exit;
	}
	$log->LogDebug("User " . $_SESSION['username'] . " loaded " . basename(__FILE__) . " from " . $_SERVER['SCRIPT_FILENAME']);

///////////////////////////////////////////////////////////
// The following are the only parameters you need to set //
// manually                                              //
///////////////////////////////////////////////////////////
$g_mac="xx.xx.xx.xx.xx.xx";
if(!$mac) {
	$mac=$_GET['m'];
}
if(!$mac) {
	$log->LogWARN("WOL FAILED no mac set from " . basename(__FILE__));
	print "failed";
	exit;
}
$g_mac=$mac;

$ip_addy = '255.255.255.255';

WakeOnLan($ip_addy, $g_mac);

flush();
function WakeOnLan($addr, $mac) {
  $socket_number = "7";
  $addr_byte = explode(':', $mac);
  $hw_addr = '';
  for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
  $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
  for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;
  // send it to the broadcast address using UDP
  // SQL_BROADCAST option isn't help!!
  $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  if ($s == false) {
    echo "Error creating socket!\n";
    echo "Error code is '".socket_last_error($s)."' - " . socket_strerror(socket_last_error($s));
    return FALSE;
    }
  else {
    // setting a broadcast option to socket:
	socket_set_option($s, SOL_SOCKET, SO_BROADCAST, 1);
    if($opt_ret <0) {
      echo "setsockopt() failed, error: " . strerror($opt_ret) . "\n";
      return FALSE;
      }
    if(socket_sendto($s, $msg, strlen($msg), 0, $addr, $socket_number)) {
      echo "Magic Packet sent successfully!";
      socket_close($s);
      return TRUE;
      }
    else {
      echo "Magic packet failed!";
      return FALSE;
      }
    }
  }
exit;
	
	
	
?>