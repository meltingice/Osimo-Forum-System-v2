<?php

/**
 * The main class that controls the entire forum
 * and provides many useful utility functions.
 * This is where the magic begins :).
 *
 * @author Ryan LeFevre
 */
class Osimo {
	public $user, $config, $ajax_mode;
	public $db, $cache, $paths, $theme, $debug, $bbparser;
	private $defaults, $allowOptMod, $globals;
	private $cacheOptions, $dbOptions, $debugOptions, $themeOptions, $disableDebug, $debugVisibility;
	public $GET, $POST;

	/**
	 * Class constructor.
	 * All of the parameters are set in config.php
	 *
	 * @param Array $options (optional)
	 *		Array of options set in config.php.
	 * @param String $siteFolder (optional)
	 *		Site folder relative to the URL root set in config.php.
	 */
	public function Osimo($options=false, $siteFolder=false) {
		if (!defined('SITE_FOLDER') && (!$siteFolder || empty($siteFolder))) {
			die("You must specify a site root!");
		}
		elseif (!defined('SITE_FOLDER') && $siteFolder!=false) {
			define('SITE_FOLDER', $siteFolder);
		}

		$this->loadIncludes(SITE_FOLDER);

		$this->paths = new OsimoPaths(SITE_FOLDER);

		$this->defaults = array(
			"debugVisibility"=>array(),
			"disableDebug"=>true
		);

		$this->allowOptMod = array(
			"cacheOptions",
			"dbOptions",
			"debugOptions",
			"debugVisibility",
			"disableDebug"
		);

		$this->parseOptions($options);
	}

	/**
	 * Due to PHP's instantiation method, this init function
	 * must be called after the Osimo object is created (which
	 * is done so in loader.php. This function instantiates all
	 * the other core objects required to make the forum run.
	 */
	public function init() {
		$this->debug = new OsimoDebug($this->debugOptions, $this->disableDebug, $this->debugVisibility);
		$this->cache = new OsimoCache($this->cacheOptions);
		$this->db = new OsimoDB($this->dbOptions);
		$this->db->osimo = $this;

		$this->loadConfig();

		$this->user = new OsimoUser();
		$this->theme = new OsimoTheme($this->themeOptions);
		$this->data = new OsimoData();
		$this->theme->osimo = $this;
		$this->bbparser = new OsimoBBParser();
	}

	private function parseOptions($options) {
		foreach ($this->defaults as $key=>$val) {
			$this->$key = $val;
		}

		if (!is_array($options)) { return true; }

		foreach ($options as $key=>$val) {
			if (in_array($key, $this->allowOptMod)) {
				$this->$key = $val;
			}
		}
	}

	private function loadConfig() {
		get('debug')->register('Osimo', array(
				'events'=>false,
				'object_creation'=>false
			));

		if (!isset($_SESSION['config'])) {
			get('debug')->logMsg('Osimo', 'events', "Loading site config from database...");
			$data = $this->db->select('*')->from('config')->rows();
			foreach ($data as $conf) {
				$this->config[$conf['name']] = $conf['value'];
			}
			$_SESSION['config'] = $this->config;
		}
		else {
			get('debug')->logMsg('Osimo', 'events', "Loading site config from saved session.");
			$this->config = $_SESSION['config'];
		}

		get('debug')->logMsg('Osimo', 'events', "Site config: ".print_r($this->config, true));

		define('OS_SITE_TITLE', $this->config['site_title']);
		define('OS_SITE_DESC', $this->config['site_description']);
	}

	/**
	 * Used to require the presence of specified GET variables
	 * and to check their data type if needed. If the GET variable
	 * is present, it is pulled into the scope of this class after
	 * the data checking is done.
	 *
	 * @param String $id
	 *		The name of the GET variable.
	 * @param boolean $numeric  (optional)
	 *		Is this GET variable strictly a number?
	 * @param String $redirect (optional)
	 *		If GET variable is missing, where should we redirect? If
	 *		false, then halt script execution.
	 * @return Boolean representing the validity of the GET variable.
	 */
	public function requireGET($id, $numeric=false, $redirect='index.php') {
		if (!isset($_GET[$id])) {
			if ($this->theme->page_type == 'index' || !$redirect) {
				$this->debug->error("OsimoCore: missing parameter '$id'", __LINE__, __FUNCTION__, __FILE__, true);
				return false;
			}

			header('Location: '.$redirect); exit;
			return false;
		}
		else {
			if ($numeric && !is_numeric($_GET[$id])) {
				if ($redirect) {
					header('Location: '.$redirect); exit;
				}
				else {
					$this->debug->error("OsimoCore: invalid parameter '$id'", __LINE__, __FUNCTION__, __FILE__, true);
				}

				return false;
			}
		}

		if ($numeric) {
			$this->GET[$id] = $_GET[$id];
		}
		else {
			$this->GET[$id] = $this->db->escape($_GET[$id]);
		}

		return true;
	}

	/**
	 * Used to specify an optional GET variable while
	 * still doing data checking if needed. If the GET
	 * variable is present, it is pulled into the scope of
	 * this class after data checking is done.
	 *
	 * @param String $id
	 *		The name of the GET variable.
	 * @param boolean $numeric (optional)
	 *		Is this GET variable strictly a number?
	 * @return Boolean representing the presence and validity of the
	 * GET variable.
	 */
	public function optionalGET($id, $numeric=false) {
		if (isset($_GET[$id])) {
			return $this->requireGET($id, $numeric);
		}

		return false;
	}

	/**
	 * Used to require the presence of specified POST variables
	 * and to check their data type if needed. If the POST variable
	 * is present, it is pulled into the scope of this class after
	 * the data checking is done.
	 *
	 * @param String $id
	 *		The name of the POST variable.
	 * @param boolean $numeric  (optional)
	 *		Is this POST variable strictly a number?
	 * @param String $redirect (optional)
	 *		If POST variable is missing, where should we redirect? If
	 *		false, then halt script execution.
	 * @return Boolean representing the validity of the POST variable.
	 */
	public function requirePOST($id, $numeric=false, $redirect='index.php') {
		if (!isset($_POST[$id])) {
			if ($this->theme->page_type == 'index' || !$redirect) {
				$this->debug->error("OsimoCore: missing parameter '$id'", __LINE__, __FUNCTION__, __FILE__, true);
				return false;
			}

			header('Location: '.$redirect); exit;
			return false;
		}
		else {
			if ($numeric && !is_numeric($_POST[$id])) {
				if ($redirect) {
					header('Location: '.$redirect); exit;
				}
				else {
					$this->debug->error("OsimoCore: invalid parameter '$id'", __LINE__, __FUNCTION__, __FILE__, true);
				}

				return false;
			}
		}

		if ($numeric) {
			$this->POST[$id] = $_POST[$id];
		}
		else {
			if (get_magic_quotes_gpc()) {
				$this->POST[$id] = stripslashes($_POST[$id]);
			}
			else {
				$this->POST[$id] = $_POST[$id];
			}
		}

		return true;
	}

	/**
	 * Used to specify an optional POST variable while
	 * still doing data checking if needed. If the POST
	 * variable is present, it is pulled into the scope of
	 * this class after data checking is done.
	 *
	 * @param String $id
	 *		The name of the POST variable.
	 * @param boolean $numeric (optional)
	 *		Is this POST variable strictly a number?
	 * @return Boolean representing the presence and validity of the
	 * POST variable.
	 */
	public function optionalPOST($id, $numeric=false) {
		if (isset($_POST[$id])) {
			return $this->requirePOST($id, $numeric);
		}

		return false;
	}

	/**
	 * Used to pass options to modules.
	 *
	 * @depreciated This function is no longer in use by
	 * any Osimo module and will probably be removed in
	 * the near future. Instead, options are passed when the
	 * module is instantiated.
	 *
	 * @param String $module
	 */
	public function options($module) {
		if (!$module || empty($module)) { $module = 'osimo'; }
		$optName = $module.'Options';
		$args = func_get_args();
		for ($i=1;$i<func_num_args();$i++) {
			if (isset($this->$module) && in_array($optName, $this->allowOptMod)) {
				$this->$module->options($args[$i]);
			}
		}
	}

	/**
	 * Loads all of the required includes for all of the modules.
	 *
	 * @param String $siteFolder
	 */
	public static function loadIncludes($siteFolder) {
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/osimo_module.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/osimo_dynamic.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/classes/user.class.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/classes/bbparser.class.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/debug.module.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/paths.module.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/db2.module.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/cache.module.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/theme.module.php';
		require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/data.module.php';
	}

	/**
	 * Dynamically loads an OsimoCategory object with the
	 * specified information.
	 *
	 * @see OsimoCategory
	 * @param Array $args (optional)
	 *		All of the information pertaining to the category.
	 * @return new OsimoCategory object.
	 */
	public function category($args=false) {
		return $this->initDynamicObj($args, 'OsimoCategory', 'category');
	}

	/**
	 * Dynamically loads an OsimoForum object with the
	 * specified information.
	 *
	 * @see OsimoForum
	 * @param Array $args (optional)
	 *		All of the information pertaining to the forum.
	 * @return new OsimoForum object.
	 */
	public function forum($args=false) {
		return $this->initDynamicObj($args, 'OsimoForum', 'forum');
	}

	/**
	 * Dynamically loads an OsimoThread object with the
	 * specified information.
	 *
	 * @see OsimoThread
	 * @param Array $args (optional)
	 *		All of the information pertaining to the thread.
	 * @return new OsimoThread object.
	 */
	public function thread($args=false) {
		return $this->initDynamicObj($args, 'OsimoThread', 'thread');
	}

	/**
	 * Dynamically loads an OsimoPost object with the
	 * specified information.
	 *
	 * @see OsimoPost
	 * @param Array $args (optional)
	 *		All of the information pertaining to the post.
	 * @return new OsimoPost object.
	 */
	public function post($args=false) {
		return $this->initDynamicObj($args, 'OsimoPost', 'post');
	}

	private function initDynamicObj($args, $Class, $file) {
		if (!class_exists($Class) && file_exists(ABS_INC_CLASSES.$file.'.class.php')) {
			get('debug')->logMsg('Osimo', 'object_creation', "Including {$file}.class.php in order to create $Class object.");
			include ABS_INC_CLASSES.$file.'.class.php';
		}
		elseif (!file_exists(ABS_INC_CLASSES.$file.'.class.php')) {
			get('debug')->error("OsimoCore: unable to locate class file '$file.class.php'", __LINE__, __FUNCTION__, __FILE__, true);
			return false;
		}

		get('debug')->logMsg('Osimo', 'object_creation', "Dynamically creating object $Class with arguments: \n".print_r($args, true));
		return new $Class($args);
	}



	/**
	 * A pseudo-global data manager, this function
	 * is most useful from within ajax-capabletheme files.
	 * Used to store data within the Osimo object that can be
	 * retrieved from anywhere without ambiguity or scope issues.
	 *
	 * @param String $name
	 *		The name/index to use for the data.
	 * @param mixed $set  (optional)
	 *		If null, return the data at the specified index.
	 *		Otherwise, set the data at the specified index to this value.
	 * @return The specified data (if retrieving)
	 */
	public function globals($name, $set=null) {
		if (is_null($set)) {
			if (isset($this->globals[$name])) {
				return $this->globals[$name];
			}

			return null;
		}
		else {
			$this->globals[$name] = $set;
			return true;
		}
	}

	/**
	 * Used to validate the arguments used in an
	 * OQL statement. Returns the formatted OQL array.
	 *
	 * @param String $args
	 *		The arguments written in OQL format.
	 * @param Array $allowed
	 *		Which parameters are allowed to be set.
	 * @param boolean $escape  (optional)
	 *		Whether or not the arguments should be escaped.
	 * @return The OQL formatted array.
	 */
	public static function validateOQLArgs($args, $allowed, $escape=false) {
		$data = explode('&', $args);
		$final = array();
		foreach ($data as $arg) {
			$temp = explode('=', $arg);
			if (array_key_exists($temp[0], $allowed)) {
				if (
					$allowed[$temp[0]] == 'any' ||
					($allowed[$temp[0]] == 'numeric' && is_numeric($temp[1])) ||
					($allowed[$temp[0]] == 'string' && is_string($temp[1]))
				) {
					if ($escape) {
						$temp[1] = get('db')->escape($temp[1], true);
					}
					else {
						$temp[1] = "'".$temp[1]."'";
					}

					$final[] = $temp[0].'='.$temp[1];
				}
			}
		}

		return $final;
	}

	/**
	 * Utility function used to get the numbers
	 * for a SQL LIMIT clause in order to restrict
	 * data to a certain page i.e. page 4 of a thread only.
	 *
	 * @param int $page
	 *		The page number to be viewed.
	 * @param int $num
	 *		How many items there are per page.
	 * @return Array containing the SQL limits.
	 */
	public static function getPageLimits($page, $num) {
		return array(
			"start"=>($page-1)*$num,
			"num"=>$num
		);
	}


}

/**
 * Somewhat of a singleton class retriever,
 * it does not actually instantiate the class but
 * instead retrieves the single instance of the
 * specified class. This function overcomes strange
 * scope issues by being outside of all classes.
 *
 * @param String $class
 *		The name of the object to return.
 * @return The specified object.
 */
function get($class) {
	global $osimo;

	if (isset($osimo) && is_object($osimo)) {
		if ($class=='osimo') {
			return $osimo;
		}

		if (is_object($osimo->$class)) {
			return $osimo->$class;
		}
	}

	return false;
}


/*
function __autoload($class){
	$classes = array(
		'OsimoBBParser'=>'bbparser',
		'OsimoForum'=>'forum',
		'OsimoModel'=>'osimomodel',
		'OsimoPost'=>'post',
		'OsimoThread'=>'thread',
		'OsimoUser'=>'user'
	);
	$modules = array(
		'OsimoCache'=>'cache',
		'OsimoData'=>'data',
		'OsimoDB'=>'db2',
		'OsimoDebug'=>'debug',
		'OsimoPaths'=>'paths',
		'OsimoTheme'=>'theme'
	);

	if(array_key_exists($class,$classes)){
		$inc = ABS_INC_CLASSES.$classes[$class].'.class.php';
	}
	elseif(array_key_exists($class,$modules)){
		$inc = ABS_INC_MODULES.$modules[$class].'.module.php';
	}
	else{
		$inc = ABS_INC.strtolower(str_replace(' ','_',$class)).'.php';
	}

	include_once($inc);
}
*/
?>