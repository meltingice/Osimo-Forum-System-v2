<?php

/**
 * The main class that controls the entire forum
 * and provides many useful utility functions.
 * This is where the magic begins :).
 *
 * @author Ryan LeFevre
 */
class Osimo {
	private static $INSTANCE;
	
	public $user, $config, $ajax_mode;
	private $modules;
	private $defaults, $allowOptMod, $globals;
	private $cacheOptions, $dbOptions, $debugOptions, $themeOptions, $disableDebug, $debugVisibility;
	public $GET, $POST;

	/**
	 * Class constructor.
	 */
	private function Osimo() {
		
	}
	
	/**
	 * Returns the singleton instance of the Osimo object.  
	 * If it hasn't been instantiated yet, it is first created 
	 * then returned.
	 */
	public static function getInstance() {
		if(is_null(self::$INSTANCE)){
			self::$INSTANCE = new Osimo();
		}
		
		return self::$INSTANCE;
	}

	/**
	 * This function instantiates all the other core objects required to make the forum run.
	 * It also sets all options specified in config.php.
	 * 
	 * @param Array $options (optional)
	 *		Array of options set in config.php.
	 * @param String $siteFolder (optional)
	 *		Site folder relative to the URL root set in config.php.
	 */
	public function init($options=false, $siteFolder=false) {
		if (!defined('SITE_FOLDER') && (!$siteFolder || empty($siteFolder))) {
			die("You must specify a site root!");
		}
		elseif (!defined('SITE_FOLDER') && $siteFolder!=false) {
			define('SITE_FOLDER', $siteFolder);
		}

		if(!defined('IS_ADMIN_PAGE')){
			define('IS_ADMIN_PAGE',false);
		}
		
		$this->loadIncludes(SITE_FOLDER);

		$this->add_module('paths', new OsimoPaths(SITE_FOLDER));

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
	
		$this->add_module('debug', new OsimoDebug($this->debugOptions, $this->disableDebug, $this->debugVisibility));
		$this->add_module('cache', new OsimoCache($this->cacheOptions));
		$this->add_module('db', new OsimoDB($this->dbOptions));

		$this->loadConfig();

		$this->add_module('user', new OsimoUser());
		$this->add_module('theme', new OsimoTheme($this->themeOptions));
		$this->add_module('data', new OsimoData());
		$this->add_module('bbparser', new OsimoBBParser());
		
		if(IS_ADMIN_PAGE == true){
			$this->add_module('admin', new OsimoAdmin());
		}
	}
	
	/**
	 * Adds a module to the Osimo system. Modules include
	 * such things as the database interaction class, the
	 * BBCode parser, the user class, etc.
	 */
	public function add_module($name, $mod_obj){
		if(!is_object($mod_obj)){
			trigger_error("OsimoCore: Module $name is not a valid object.", E_USER_ERROR); exit;
		}
		
		$this->modules[$name] = $mod_obj;
	}
	
	/**
	 * Retrieves a module that has already been loaded
	 * using add_module(). The preferred way to use this function
	 * is by using the classless get() function at the bottom
	 * of this file.
	 */
	public function get_module($name){
		if(!isset($this->modules[$name])){
			get('debug')->logError('Osimo', 'events', "Error loading $name module."); exit;
		}
		
		return $this->modules[$name];
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
			$data = get('db')->select('*')->from('config')->rows();
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
			if (get('theme')->page_type == 'index' || !$redirect) {
				get('debug')->error("OsimoCore: missing parameter '$id'", __LINE__, __FUNCTION__, __FILE__, true);
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
					get('debug')->error("OsimoCore: invalid parameter '$id'", __LINE__, __FUNCTION__, __FILE__, true);
				}

				return false;
			}
		}

		if ($numeric) {
			$this->GET[$id] = $_GET[$id];
		}
		else {
			$this->GET[$id] = get('db')->escape($_GET[$id]);
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
			if (get('theme')->page_type == 'index' || !$redirect) {
				get('debug')->error("OsimoCore: missing parameter '$id'", __LINE__, __FUNCTION__, __FILE__, true);
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
					get('debug')->error("OsimoCore: invalid parameter '$id'", __LINE__, __FUNCTION__, __FILE__, true);
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
		
		if(IS_ADMIN_PAGE){
			require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-admin/includes/classes/admin.class.php';
			require $_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-admin/includes/classes/upgrade.class.php';
		}
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
		elseif($class=='config') {
			return (object)$osimo->config;
		}

		return $osimo->get_module($class);
	}

	return false;
}
?>