<?
if(!isset($_POST)) {
	header('Location: index.php');
} elseif(!is_numeric($_POST['step'])) {
	exit;
}

require '../os-includes/osimo_module.php';
require '../os-includes/errors/osimo.errors.php';
require '../os-includes/errors/osimo.exception.php';
require '../os-includes/modules/db2.module.php';
require '../os-includes/managers/config.manager.php';
require '../os-includes/managers/user.manager.php';
require 'dummy_debug.php';

session_start();

$step = $_POST['step'];

if($step == 1) {
	json_return(recordConfig());
} elseif ($step == 2) {
	json_return(writeConfigToDisk());
} elseif ($step == 3) {
	json_return(checkDatabaseConnection());
} elseif ($step == 4) {
	json_return(createDatabaseTables());
} elseif ($step == 5) {
	json_return(writeConfigToDatabase());
} elseif ($step == 6) {
	json_return(createAdminAccount());
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

function checkDatabaseConnection() {
	ConfigManager::instance()->register_user_config($_SESSION['installConfig']);
	$db_config = ConfigManager::instance()->get('database');
	
	# check for MySQL server without connecting
	$fp = @fsockopen($db_config['host'], 3306);
	if(!$fp) {
		echo json_encode(array("error" => "Unable to connect to MySQL", "type" => 0)); exit;
	}

	OsimoDB::instance()->init(
		array(
			'autoconnect' => false
		)
	);
	
	try {
		OsimoDB::instance()->connect();
	} catch (OsimoException $e) {
		echo json_encode(array("error"=>$e->getMessage(), "type" => $e->getExceptionType())); exit;
	}
	
	return true;
}

function createDatabaseTables() {
	$sql = include_file_contents('sql/tables.sql');
	$queries = explode(";", $sql);
	if(count($queries) > 0) {
		ConfigManager::instance()->register_user_config($_SESSION['installConfig']);
		OsimoDB::instance()->init();
		foreach($queries as $query) {
			$result = mysql_query($query);
		}
	}
	
	return true;
}

function writeConfigToDatabase() {
	$sql = include_file_contents('sql/config.sql');
	$queries = explode(";", $sql);
	if(count($queries) > 0) {
		ConfigManager::instance()->register_user_config($_SESSION['installConfig']);
		OsimoDB::instance()->init();
		foreach($queries as $query) {
			$result = mysql_query($query);
		}
	}
	
	return true;
}

function createAdminAccount() {
	ConfigManager::instance()->register_user_config($_SESSION['installConfig']);
	OsimoDB::instance()->init();
	
	try {
		$userID = UserManager::register_user($_POST['user']['username'], $_POST['user']['password'], $_POST['user']['email'], false);
		UserManager::update_user_info($userID, array('is_admin' => 1, 'is_confirmed' => 1));
		
		return true;
	} catch (OsimoException $e) {
		echo json_encode(array("error" => $e->getMessage())); exit;
	}
}

function json_return($success) {
	if($success) {
		echo json_encode(array("success"=>"true")); exit;
	} else {
		echo json_encode(array("error"=>"true")); exit;
	}
}

function get($name) {
	if($name == 'debug') {
		return new DummyDebug();
	} elseif($name == 'db') {
		return OsimoDB::instance();
	}
}

function include_file_contents($filename) {
	if (is_file($filename)) {
		ob_start();
	    include $filename;
	    $contents = ob_get_contents();
	    ob_end_clean();
	    return $contents;
    }
    	
    return false;
}
?>