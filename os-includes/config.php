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

/* Database settings */
$dbHost = 'localhost';
$dbUser = 'user';
$dbPass = 'password';
$dbName = 'osimo';

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

/* Debugging options */
$debugOptions = array(
	"OsimoDB"=>array(
		'events'=>true,
		'queries'=>true,
		'benchmarking'=>true
	),
	"OsimoCache"=>array(
		'events'=>true
	),
	"OsimoData"=>array(
		'events'=>true
	),
	"OsimoBBParser"=>array(
		'events'=>false,
		'benchmarking'=>true
	),
	"OsimoUser"=>array(
		'events'=>true,
		'benchmarking'=>true
	)
);

/*
 * END USER DEFINED SETTINGS
 */
 
include('loader.php');
?>