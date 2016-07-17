<?php
$log->LogWarn("Database: stats.db not found, trying to create now");	
try {
	$configdbcreate = new PDO('sqlite:' . $PRIVATE_DATA . '/db/config.db');
} catch (PDOException $e) {
	$log->LogError("Database: config.db could not be created: $e->getMessage()");
	echo "Fatal: User could not open DB: $e->getMessage()";
	exit;
}	


$query = "CREATE TABLE IF NOT EXISTS users (
`userid`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
`username`	text NOT NULL,
`password`	text,
`passwordv`	INTEGER DEFAULT 0,
`avatar`	INTEGER DEFAULT 0,
`navgroupaccess`	string,
`homeRoom`	integer,
`roomGroupAccess`	INTEGER,
`roomAccess`	string,
`settingsAccess`	INTEGER NOT NULL DEFAULT 0,
`wanAccess`	INTEGER NOT NULL DEFAULT 0,
`userlevel`	INTEGER NOT NULL DEFAULT 0,
`passwordreset`	INTEGER NOT NULL DEFAULT 0,
`lastaccess`	INTEGER DEFAULT 0
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS settings (settingid integer PRIMARY KEY AUTOINCREMENT, setting text UNIQUE, description text, settingvalue1type text, settingvalue1 text)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS rooms_addons_info (
`rooms_addonsid`	INTEGER NOT NULL,
`info`	TEXT,
`infoType`	TEXT,
`thumbnail`	TEXT,
`fanart`	TEXT,
PRIMARY KEY(rooms_addonsid)
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS rooms_addons_global_settings (
`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
`addonid`	TEXT NOT NULL,
`globalDisable`	INTEGER NOT NULL DEFAULT 0,
`controlWindow`	INTEGER NOT NULL DEFAULT 0
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS rooms_addons (
`rooms_addonsid`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
`roomid`	INTEGER NOT NULL,
`addonid`	TEXT NOT NULL,
`addonTitle`	TEXT,
`ip`	TEXT,
`ipw`	TEXT,
`mac`	TEXT,
`device_alive`	INTEGER,
`enabled`	INTEGER NOT NULL DEFAULT 1,
`lastCheck`	INTEGER,
`roomRequiresAlive`	INTEGER DEFAULT 0,
`PowerOptions`	INTEGER DEFAULT 0
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS rooms (
`roomId`	integer PRIMARY KEY AUTOINCREMENT,
`roomName`	text NOT NULL UNIQUE
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS roomgroups (
`roomGroupId`	integer PRIMARY KEY AUTOINCREMENT,
`roomGroupName`	text UNIQUE,
`roomAccess`	string
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS preferences_options (
`prefoptionid`	INTEGER PRIMARY KEY AUTOINCREMENT,
`preftitle`	TEXT NOT NULL,
`preferences`	TEXT
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS `preferences` (
`prefid`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
`prefoptionid`	INTEGER NOT NULL,
`userid`	INTEGER NOT NULL,
`preference`	TEXT NOT NULL
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS `navigationgroups` (
`navgroupid` INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
`navgroupname` TEXT  NOT NULL,
`navitems` TEXT  NOT NULL
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS navigation (
`navid`	INTEGER,
`navname`	text,
`navip`	text,
`navipw`	text,
`mobilew`	text DEFAULT 0,
`mobile`	text DEFAULT 0,
`persistent`	integer,
`autorefresh`	integer NOT NULL DEFAULT '0',
PRIMARY KEY(navid)
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();




$query = "CREATE TABLE IF NOT EXISTS controlcenter (CCid integer PRIMARY KEY AUTOINCREMENT UNIQUE, CCsetting TEXT UNIQUE, CCvalue TEXT)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "INSERT INTO `controlcenter` (CCid,CCsetting,CCvalue) VALUES (1,'ccversion','1.0.0'),
(2,'dbversion','0.0.1'),
(3,'lastcrontime','1468539538')";
$statement = $configdbcreate->prepare($query);
$statement->execute();




$query = "CREATE TABLE IF NOT EXISTS chat_viewed (
`chatViewedId`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
`userId`	INTEGER NOT NULL DEFAULT '0',
`chatId`	INTEGER NOT NULL DEFAULT '0',
`viewed`	INTEGER NOT NULL DEFAULT 0
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();

$query = "CREATE TABLE IF NOT EXISTS chat (
`chatId`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
`sentTo`	INTEGER NOT NULL DEFAULT '0',
`sendType`	TEXT,
`message`	TEXT NOT NULL,
`fromUserId`	INTEGER NOT NULL DEFAULT '0',
`created`	INTEGER NOT NULL DEFAULT 0
)";
$statement = $configdbcreate->prepare($query);
$statement->execute();





$configdbcreate->close();

?>