<?php
if(false){
?>
PHP Failed.
<?php
}
$redirect = true;
$version = phpversion();

$result = [];
$result['phpversion']=$version;
$result['failed']=0;

$result['libxml']['info']="Required for XML interpretation";
$result['libxml']['name']="libxml";
$result['pdo_sqlite']['info']="Required for the database";
$result['pdo_sqlite']['name']="PDO sqlite";
$result['curl']['info']="Required to send curl commands";
$result['curl']['name']="curl";
$result['json']['info']="Required for JSON interpretation";
$result['json']['name']="json";
$result['sockets']['info']="Required for WOL packet sending";
$result['sockets']['name']="php_sockets";


if(extension_loaded('libxml')){
	$result['libxml']['status']="pass";
}else{
	$result['libxml']['status']="fail";
	$result['failed']++;
}
if(extension_loaded('pdo_sqlite')){
	$result['pdo_sqlite']['status']="pass";
}else{
	$result['pdo_sqlite']['status']="fail";
	$result['failed']++;
}
if(extension_loaded('curl')){
	$result['curl']['status']="pass";
}else{
	$result['curl']['status']="fail";
	$result['failed']++;
}
if(extension_loaded('json')){
	$result['json']['status']="pass";
}else{
	$result['json']['status']="fail";
	$result['failed']++;
}
if(extension_loaded('sockets')){
	$result['sockets']['status']="pass";
}else{
	$result['sockets']['status']="fail";
	$result['failed']++;
}



/*
 * check for writable directory and other permissions
 * 
 */






header('Content-Type: application/json');
$json=json_encode($result);
echo "[".$json."]";
?>