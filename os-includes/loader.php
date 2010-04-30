<?
include('classes/osimo.class.php');

/* Set DB options */
$dbOptions = array(
	'db_host'=>$dbHost,
	'db_user'=>$dbUser,
	'db_pass'=>$dbPass,
	'db_name'=>$dbName
);

/* Set cache options */
if(CACHE_TYPE=='memcache'){
	$cacheOptions = array(
		'prefix'=>$memcachePrefix,
		'cache_addr'=>$memcacheAddr,
		'cache_port'=>$memcachePort,
		'debug'=>true
	);
	
	$session_save_path = "tcp://{$memcacheAddr[0]}:$memcachePort?persistent=1&weight=2&timeout=2&retry_interval=10,  ,tcp://{$memcacheAddr[0]}:$memcachePort  ";
	ini_set('session.save_handler', 'memcache');
	ini_set('session.save_path', $session_save_path);
}
else{
	$cacheOptions = array(
		'enabled'=>false
	);
}

session_start();

$osimo = Osimo::instance();
$osimo->init($config);

set_exception_handler(array("OsimoDebug", "exception_handler"));
?>