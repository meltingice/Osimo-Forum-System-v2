<?
class Osimo{
	public $user;
	public $db,$cache,$paths,$theme,$debug;
	private $defaults,$allowOptMod;
	private $cacheOptions,$dbOptions,$debugOptions;
	
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
	
	public static function loadIncludes($siteFolder){
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/osimo_module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/debug.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/paths.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/db2.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/cache.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/theme.module.php');
	}
	
	private function init(){
		if(isset($_SESSION['user']['id'])){
			$this->user = $_SESSION['user'];
		}
		else{
			$this->user = false;
		}
		
		$this->debug = new OsimoDebug($this->debugOptions);
		$this->cache = new OsimoCache($this->cacheOptions);
		$this->db = new OsimoDB($this->dbOptions);
		$this->theme = new OsimoTheme();
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
}
?>