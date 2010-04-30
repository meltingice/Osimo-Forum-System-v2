<?
/**
 * Loads and manages the sites configuration options.
 *
 * @author Ryan LeFevre
 */
class ConfigManager {
	private static $INSTANCE;
	
	private $config, $userConfig;
	
	private function ConfigManager() {
		$this->config = array();
		$this->userConfig = array();
	}
	
	/**
	 * Gets the singleton instance of the ConfigManager.
	 */
	public static function instance() {
		if(is_null(self::$INSTANCE)) {
			self::$INSTANCE = new ConfigManager();
		}
		
		return self::$INSTANCE;
	}
	
	/**
	 * Loads the site configuration from the database's config table
	 * and stores it in the class $config array.
	 */
	public function load() {
		if (!isset($_SESSION['config']) || !is_array($_SESSION['config']) || count($_SESSION['config']) == 0) {
			get('debug')->logMsg('Osimo', 'events', "Loading site config from database...");
			$data = get('db')->select('*')->from('config')->rows(true);
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
	 * Returns all configuration options that are currently loaded.
	 */
	public function getAll() {
		return array_merge($this->config, $this->userConfig);
	}
	
	/**
	 * Returns the configuration item specified by name.
	 *
	 * @param $name
	 *		The name of the configuration item to return.
	 */
	public function get($name) {
		if(isset($this->config[$name])) {
			return $this->config[$name];
		} elseif(isset($this->userConfig[$name])){
			return $this->userConfig[$name];
		} else {
			throw new Exception("Config variable $name not set.");
		}
	}
	
	public function register_user_config($config) {
		$this->userConfig = $config;
	}
	
	private function format_config_for_write($config) {
		$final = "";
		$lines = explode("\n", $config);
		for($i = 0; $i < count($lines); $i++) {
			$line = explode("=>", $lines[$i]);
			if(count($line) > 1) {
				$line[0] = str_replace(array('[',']'), "'", $line[0]);
				$final .= $line[0]."=>'".$line[1]."'\n";
			} else {
				$final .= $lines[$i]."\n";
			}
		}
		
		return $final;
	}

}
?>