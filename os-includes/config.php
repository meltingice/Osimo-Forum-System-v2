<?
error_reporting(E_ALL);

/* User defined settings */
define('SITE_FOLDER','/dev/osimo2/');

/* Database settings */
$dbHost = 'localhost';
$dbUser = 'osimo';
$dbPass = 'password';
$dbName = 'osimo';

/* Cache settings */
define('CACHE_TYPE',false); // Possible values: 'memcache',false
$memcachePrefix = '';
$memcacheAddr = array(
	'localhost'
);
$memcachePort = 11211;

include($_SERVER['DOCUMENT_ROOT'].SITE_FOLDER.'/os-includes/classes/osimo.class.php');

$dbOptions = array(
	'db_host'=>$dbHost,
	'db_user'=>$dbUser,
	'db_pass'=>$dbPass,
	'db_name'=>$dbName,
	'error_type'=>'stdout'
);

$cacheOptions = array(
	'prefix'=>$memcachePrefix,
	'cache_addr'=>$memcacheAddr,
	'cache_port'=>$memcachePort,
	'debug'=>false
);

if(CACHE_TYPE=='memcache'){
	$session_save_path = "tcp://{$memcacheAddr[0]}:$memcachePort?persistent=1&weight=2&timeout=2&retry_interval=10,  ,tcp://{$memcacheAddr[0]}:$memcachePort  ";
	ini_set('session.save_handler', 'memcache');
	ini_set('session.save_path', $session_save_path);
}

session_start();

$osimo = new Osimo(
	array(
		"dbOptions"=>$dbOptions,
		"cacheOptions"=>$cacheOptions
	)
);
?>