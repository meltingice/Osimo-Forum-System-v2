<?
class Osimo{
	public $user,$config;
	public $db,$cache,$paths,$theme,$debug;
	private $defaults,$allowOptMod;
	private $cacheOptions,$dbOptions,$debugOptions,$themeOptions;
	
	public function Osimo($options=false,$siteFolder=false){
		if(!defined('SITE_FOLDER') && (!$siteFolder || empty($siteFolder))){
			die("You must specify a site root!");
		}
		elseif(!defined('SITE_FOLDER') && $siteFolder!=false){
			define('SITE_FOLDER',$siteFolder);
		}
		
		$this->loadIncludes(SITE_FOLDER);
		
		$this->paths = new OsimoPaths(SITE_FOLDER);
		
		$this->defaults = array(
			
		);
		
		$this->allowOptMod = array(
			"cacheOptions",
			"dbOptions",
			"debugOptions"
		);
		
		$this->parseOptions($options);
		
		$this->init();
	}
	
	private function init(){
		$this->user = new OsimoUser();
		$this->debug = new OsimoDebug($this->debugOptions);
		$this->cache = new OsimoCache($this->cacheOptions);
		$this->db = new OsimoDB($this->dbOptions);
		$this->db->osimo = $this;
		$this->theme = new OsimoTheme($this->themeOptions);
		$this->theme->osimo = $this;
		
		$this->loadConfig();
	}
	
	private function parseOptions($options){
		foreach($this->defaults as $key=>$val){
			$this->$key = $val;
		}
		
		if(!is_array($options)){ return true; }
		
		foreach($options as $key=>$val){
			if(in_array($key,$this->allowOptMod)){
				$this->$key = $val;
			}
		}
	}
	
	private function loadConfig(){
		if(!isset($_SESSION['config'])){
			$data = $this->db->select('*')->from('config')->rows();
			$_SESSION['config'] = $data;
		}
		else{
			$data = $_SESSION['config'];
		}
		
		foreach($data as $conf){
			$this->config[$conf['name']] = $conf['value'];
		}
		
		define('OS_SITE_TITLE',$this->config['site_title']);
	}
	
	public function requireGET($id,$numeric=false,$redirect=true){
		if(!isset($_GET[$id])){
			if($this->theme->page_type == 'index' || !$redirect){
				$this->debug->error("OsimoCore: missing parameter '$id'",true);
				return false;
			}
			
			header('Location: index.php');
			return false;
		}
		else{
			if($numeric && !is_numeric($_GET[$id])){
				if($redirect){
					header('Location: index.php');
				}
				else{
					$this->debug->error("OsimoCore: invalid parameter '$id'",true);
				}
				
				return false;
			}
		}
		
		return true;
	}
	
	public function options($module){
		if(!$module || empty($module)){ $module = 'osimo'; }
		$optName = $module.'Options';
		$args = func_get_args();
		for($i=1;$i<func_num_args();$i++){
			if(isset($this->$module) && in_array($optName,$this->allowOptMod)){
				$this->$module->options($args[$i]);
			}
		}
	}
	
	public static function loadIncludes($siteFolder){
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/osimo_module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/osimo_dynamic.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/classes/user.class.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/classes/osimomodel.class.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/debug.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/paths.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/db2.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/cache.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/theme.module.php');
	}
	
	/* Dynamicly loaded objects */
	public function forum($args=false){
		if(!isset($args['id']) || !is_numeric($args['id'])){
			$args['id'] = $_GET['id'];
		}
		
		return $this->initDynamicObj($args,'OsimoForum','forum','forum');
	}
	
	private function initDynamicObj($args,$Class,$var,$file){
		if(!class_exists($Class) && file_exists(ABS_INC_CLASSES.$file.'.class.php')){
			include_once(ABS_INC_CLASSES.$file.'.class.php');
		}
		else{
			$this->debug->error("OsimoCore: unable to locate class file '$file.class.php'",true);
			return false;
		}
		
		if(!isset($this->$var) || !is_object($this->var)){
			$this->$var = new $Class($args);
		}
		
		return $this->$var;
	}
}

function get($class){
	global $osimo;
	if(isset($osimo) && is_object($osimo)){
		if($class=='osimo'){
			return $osimo;
		}
		
		if(is_object($osimo->$class)){
			return $osimo->$class;
		}
	}
	
	return false;
}
?>