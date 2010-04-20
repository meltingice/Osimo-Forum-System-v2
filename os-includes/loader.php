<?

include($_SERVER['DOCUMENT_ROOT'].SITE_FOLDER.'/os-includes/classes/osimo.class.php');

/* Set DB options */
$dbOptions = array(
	'db_host'=>$dbHost,
	'db_user'=>$dbUser,
	'db_pass'=>$dbPass,
	'db_name'=>$dbName,
	'error_type'=>'stdout',
	'log_level'=>array(
		'events'=>true,
		'queries'=>true,
		'benchmarking'=>true
	)
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

global $osimo;
$osimo = Osimo::getInstance();
$osimo->init(
	array(
		"dbOptions"=>$dbOptions,
		"cacheOptions"=>$cacheOptions,
		"debugOptions"=>$debugOptions,
		"debugVisibility"=>true,
		"disableDebug"=>false
	)
);
?>