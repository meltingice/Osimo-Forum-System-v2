<?
if(!isset($_POST)) {
	header('Location: index.php');
} elseif(!is_numeric($_POST['step'])) {
	exit;
}

require '../os-includes/osimo_module.php';
require '../os-includes/modules/db2.module.php';
require '../os-includes/managers/config.manager.php';

session_start();

$step = $_POST['step'];

if($step == 1) {
	json_return(recordConfig());
} elseif ($step == 2) {
	json_return(writeConfigToDisk());
}

/**
 * Saves the configuration to a session variable and
 * does some data checking/parsing to clean it up.
 */
function recordConfig() {
	$config = $_POST['config'];
	
	# parse and record filepath
	$path = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
	if($path[strlen($path)-1] == "/") {
		$path = substr($path, 0, -6);
	} else {
		$path = substr($path, 0, -5);
	}
	
	$config['site_folder'] = $path;
	
	# database config validation
	foreach($config['database'] as $key=>$val) {
		if($val == "") {
			return false;
		}
	}
	
	# format the memcached addresses
	if(trim($config['cache']['addresses']) != "") {
		$addresses = explode(",",$config['cache']['addresses']);
		for($i = 0; $i < count($addresses); $i++) {
			$addresses[$i] = trim($addresses[$i]);
			if(strpos($addresses[$i], ":") === false) {
				$addresses[$i] .= ":11211"; //automatically add default port to the address
			}
		}
		
		$config['cache']['addresses'] = $addresses;
	}
	
	# save config to session variable
	$_SESSION['installConfig'] = $config;
	
	return true;
}

function writeConfigToDisk() {
	try {
		$path = realpath("../os-includes/");
		ConfigManager::write_config_to_disk($_SESSION['installConfig'], $path);
	} catch (Exception $e) {
		echo json_encode(array("error"=>$e->getMessage())); exit;
	}
	
	return true;
}

function json_return($success) {
	if($success) {
		echo json_encode(array("success"=>"true")); exit;
	} else {
		echo json_encode(array("error"=>"true")); exit;
	}
}
?>