<?
error_reporting(E_ALL);

/* BEGIN USER DEFINED SETTINGS */

# What is the folder Osimo is placed in relative to the site URL?
# *** INCLUDE TRAILING SLASH ***
# i.e. if your site is at http://getosimo.com/forums/
#      then this value should be '/forums/'
#      ~ OR ~
#      if the forums are in the root URL directory, just put '/'
define('SITE_FOLDER','/dev/osimo2');

/* MySQL Database settings */
$dbHost = 'localhost'; // address of the MySQL server
$dbUser = 'user'; // MySQL username
$dbPass = 'password'; // MySQL password
$dbName = 'osimo'; // MySQL database name

/* OsimoCache settings */
# Currently Osimo only supports memcached as a caching method.
#
# If you do not have memcached installed with the memcache PEAR module, then set this to false.
# Otherwise set it to 'memcache'
define('CACHE_TYPE',false); // Possible values: 'memcache',false

# Set this to any unique value if you are using more than 1 Osimo installation on the same
# memcached server.  You might also consider setting this if this server is running other scripts
# that use memcached, although the possibility of cross-script interference is extremely low.
$memcachePrefix = '';

# Enter the address of every memcached server you want Osimo to use in the array.
# The value must be an array, even if you're only using 1 memcached server.
$memcacheAddr = array(
	'localhost'
);

# The port memcached is running on. The default is 11211.
$memcachePort = 11211;

/*
 * END USER DEFINED SETTINGS
 * DO NOT EDIT BELOW THIS UNLESS YOU KNOW WHAT YOU ARE DOING!
 */

include($_SERVER['DOCUMENT_ROOT'].SITE_FOLDER.'/os-includes/classes/osimo.class.php');

/* Set DB options */
$dbOptions = array(
	'db_host'=>$dbHost,
	'db_user'=>$dbUser,
	'db_pass'=>$dbPass,
	'db_name'=>$dbName,
	'error_type'=>'stdout'
);

/* Set cache options */
if(CACHE_TYPE=='memcache'){
	$cacheOptions = array(
		'prefix'=>$memcachePrefix,
		'cache_addr'=>$memcacheAddr,
		'cache_port'=>$memcachePort,
		'debug'=>false
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
$osimo = new Osimo(
	array(
		"dbOptions"=>$dbOptions,
		"cacheOptions"=>$cacheOptions
	)
);
?>