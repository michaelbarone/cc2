<?php

/*
 * simple Amazon FireTV remote for any system running php (webserver)
 * needs: a PHP environment and adb installed
 * date: 2016-08-28
 * contact: heyer@linuxnutzer.de
 * idea from: http://www.aftvnews.com/how-to-remotely-control-an-amazon-fire-tv-or-fire-tv-stick-via-adb/
 * 
 * instructions:
 * - install adb
 * - copy this script to your web-directory (e.g. /home/user/public_html/firetv)
 * - optional: place a .htaccess file allowing only local connections (e.g. /home/user/public_html/firetv/.htaccess)
 * - browse & set your bookmarks to e.g. http://localhost/~user/firetv/index.php?p=supersecret
 */


/*
 * configure this:
 */
 $default_password = "s";
 $default_ip = "192.168.3.226";








/*
 * do not touch this:
 */


// error_reporting(E_ALL);
 error_reporting(E_ALL ^ E_NOTICE);
 ini_set("display_errors", 1);


// password
 $pass = "";
 if (isset($_POST['pass'])) {
  $pass = $_POST['pass'];
 }

 if (isset($_GET['p'])) {
  $pass = $_GET['p'];
 }
  
 $correct_password = FALSE;
 if ($pass == $default_password) {
  $correct_password = TRUE;
 }




// ip
 $ip = $default_ip;
 if (isset($_POST['ip'])) {
  $ip = $_POST['ip'];
 }
 if (isset($_POST['ip_new'])) {
  $ip = $_POST['ip_new'];
 }



  
//firetv commands as on http://developer.android.com/reference/android/view/KeyEvent.html
 $firetv_command = array(
	 array("id"=>100,	"name"=>"CONNECT",	"value"=>0,	"y"=>0,	"x"=>1),
	 array("id"=>102,	"name"=>"START-SERVER",	"value"=>0,	"y"=>0,	"x"=>0),

	 array("id"=>1,	"name"=>"/\\",		"value"=>19,	"y"=>2,	"x"=>1),
	 array("id"=>2,	"name"=>"<",		"value"=>21,	"y"=>3,	"x"=>0),
	 array("id"=>3,	"name"=>"O",	"value"=>66,	"y"=>3,	"x"=>1),
	 array("id"=>4,	"name"=>">",	"value"=>22,	"y"=>3,	"x"=>2),
	 array("id"=>5,	"name"=>"\\/",		"value"=>20,	"y"=>4,	"x"=>1),
	 
	 array("id"=>6,	"name"=>"BACK",		"value"=>04,	"y"=>6,	"x"=>0),
	 array("id"=>7,	"name"=>"HOME",		"value"=>03,	"y"=>6,	"x"=>1),
	 array("id"=>8,	"name"=>"MENU",		"value"=>01,	"y"=>6,	"x"=>2),
	 
	 array("id"=>9,	"name"=>"PREVIOUS",	"value"=>88,		"y"=>7,	"x"=>0),
	 array("id"=>10,	"name"=>"PLAY/PAUSE",	"value"=>85,	"y"=>7,	"x"=>1),
	 array("id"=>11,	"name"=>"NEXT",		"value"=>87,		"y"=>7,	"x"=>2),

	 array("id"=>101,	"name"=>"DISCONNECT",	"value"=>0,	"y"=>9,	"x"=>1),
	 array("id"=>103,	"name"=>"STOP-SERVER",	"value"=>0,	"y"=>9,	"x"=>2)
 );

 $user_command_id = "";
 if (isset($_POST['u'])) {
  $user_command_id = key($_POST['u']);	//  _debug($user_command_id);

  $user_command = 0;
	foreach ((array) $firetv_command as $key => $val) {
		if ( ($val['id']==$user_command_id)) {
			$user_command	= $key;
		}
	}
  _execute_command($firetv_command[$user_command], $ip);
 }



?> 

<HTML>

<HEAD>
<TITLE>fireTV remote</TITLE>
</HEAD>
<BODY BGCOLOR="#ffffff" TEXT="#000000" LINK="#000000" ALINK="#000000" VLINK="#000000" TOPMARGIN=0 LEFTMARGIN=0 MARGINWIDTH=0 MARGINHEIGHT=0>
<CENTER><TABLE BORDER=0 WIDTH="100%" HEIGHT="100%" BGCOLOR="#ffffff"><TR><TD><CENTER>
<CENTER><TABLE BORDER=0 BGCOLOR="#aaccff"><TR><TD><CENTER>
<H1>fireTV remote</H1>

<?php
 if ( $correct_password == FALSE) {
  echo "wrong password";
  exit(1);
 }

 echo "<FORM ACTION=\"".$_SERVER['PHP_SELF']."\" enctype=\"multipart/form-data\" method=\"POST\">\n";
 echo "<input type=\"hidden\" name=\"pass\" value=\"".$pass."\">\n"; 
 echo "<input type=\"hidden\" name=\"ip\" value=\"".$ip."\">\n"; 
 echo "fireTV ip: <input type=\"input\" name=\"ip_new\" value=\"".$ip."\"><BR>\n"; 

 $output = shell_exec("adb connect ".$ip); _debug($output);
// if you want you can list all connected devices:
// $output = shell_exec("adb devices"); _debug($output);
 
 _show_grid($firetv_command);	// I know I should use CSS here, but I am oldsk00l

?>

</FORM>

</CENTER></TD></TR></TABLE></CENTER>
</CENTER></TD></TR></TABLE></CENTER>
</BODY>
</HTML>

<?php


function _execute_command (
	$command,
	$ip
) {
//	_debug($command);

//http://php.net/manual/de/function.shell-exec.php
	
	switch ($command['id']) {
		case 100:
			$output = shell_exec("adb connect ".$ip);
		//	_debug($output);
			break;
		case 101:
			$output = shell_exec("adb disconnect ".$ip);
		//	_debug($output);
			break;
		case 102:
			$output = shell_exec("adb start-server");
		//	_debug($output);
			break;
		case 103:
			$output = shell_exec("adb kill-server");
		//	_debug($output);
			break;
		case ($command['id']<100):
		//	_debug('normal: '.$command['name']);
			//$output = shell_exec("adb connect ".$ip);
			$output = shell_exec("adb shell input keyevent ".$command['value']);
		//	_debug($output);
			break;
			
		default:
			_debug('unknown command');
			break;

	}	// switch
	return 0;
}

function _debug (
	$my_array
) {	
	echo "<pre>\n";
	print_r($my_array);
	echo "</pre>\n";
	return 0;
}


function _show_grid (
	$my_array
) {
	$HEIGHT = 10;
	$WIDTH = 3;

	echo "<TABLE BORDER=0 WIDTH=100% COLS=".$WIDTH." ROWS=".$HEIGHT.">\n";

	for ($y=0; $y<$HEIGHT; $y++) {
		echo "<TR>\n";
		for ($x=0; $x<$WIDTH; $x++) {
			$value = "&nbsp;";
			foreach ((array) $my_array as $key => $val) {
				if ( ($val['y']==$y) &&  ($val['x']==$x)) {
					$value = "<input type=\"submit\" name=\"u[".$val['id']."]\" value=\"".$val['name']."\">\n";
				}
			}
			echo "<TD><CENTER>".$value."</CENTER></TD>\n";
		}	// for x
		echo "</TR>\n";
	}	// for y
	echo "</TABLE>\n";
	return 0;
}

?>