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
		$navgroups='';	
		$row = $configdb->query("SELECT navgroupaccess FROM users WHERE userid = $userid LIMIT 1");
		$row = $row->fetch(PDO::FETCH_ASSOC);
		$navgroups=$row['navgroupaccess'];
		if(!empty($navgroups) && $navgroups!=0 && $navgroups!=null){
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
						$sql = "SELECT navname,navip,mobile,globalDisable FROM navigation WHERE navid = $xx LIMIT 1";
						foreach ($configdb->query($sql) as $row) {
							if($mobile==='1' && $row['mobile']==='0') { continue; }
							if($row['globalDisable']=='1') { continue; }
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
		}else{
			$result = '';
		}
		
	} catch(PDOException $e) {
		$log->LogFatal("User could not open DB: $e->getMessage().  from " . basename(__FILE__));
	}
	header('Content-Type: application/json');
	$json=json_encode($result);
	echo ")]}',\n"."[".$json."]";
?>