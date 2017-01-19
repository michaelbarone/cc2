<?php 
	require 'startsession.php';
	if(isset($_SESSION['userid'])) {
		$userid=$_SESSION['userid'];
		if(isset($_GET['mobile']) && $_GET['mobile'] != '') {
			$mobile=$_GET['mobile'];
		} else {
			$mobile=$_SESSION['mobile'];
		}
	} else {
		echo "failed";
		$log->LogWarn("No user session data from " . basename(__FILE__));
		exit;
	}
	try {
		$linkArray = array();
		foreach ($configdb->query("SELECT navgroupaccess FROM users WHERE userid = $userid LIMIT 1") as $row) {
			$navgroups=$row['navgroupaccess'];
		}
		$navgroups = explode(',', $navgroups);
		foreach($navgroups as $x) {
			if(!isset($x) || $x == '' || is_array($x)) { continue; }
			$sql = "SELECT * FROM navigationgroups WHERE navgroupid = $x LIMIT 1";
			foreach ($configdb->query($sql) as $row) {
				$thistemp = $row['navitems'];
				$thistemp = explode(',', $thistemp);
				$navgroupname=$row['navgroupname'];
				$i=0;
				foreach ($thistemp as $xx) {
					$sql = "SELECT navname,navip,mobile FROM navigation WHERE navid = $xx LIMIT 1";
					foreach ($configdb->query($sql) as $row) {
						if($mobile==='1' && $row['mobile']==='0') { continue; }
						$i++;
						$linkArray["$navgroupname"]["$i"]['navname']=$row['navname'];
						if($mobile==='1' && $row['mobile']!='1') {
							$linkArray["$navgroupname"]["$i"]['navip']=$row['mobile'];
						} else {
							$linkArray["$navgroupname"]["$i"]['navip']=$row['navip'];
						}
					}
				}
			}			
		}
		$result = $linkArray;
		
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo ")]}',\n"."[".$json."]";
?>