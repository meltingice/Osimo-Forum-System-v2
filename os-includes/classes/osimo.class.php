<?
class Osimo{
	public $user,$config;
	public $db,$cache,$paths,$theme,$debug,$bbparser;
	private $defaults,$allowOptMod;
	private $cacheOptions,$dbOptions,$debugOptions,$themeOptions,$disableDebug,$debugVisibility;
	public $GET,$POST;
	
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
	
	public function init(){
		$this->debug = new OsimoDebug($this->debugOptions,$this->disableDebug,$this->debugVisibility);
		$this->cache = new OsimoCache($this->cacheOptions);
		$this->db = new OsimoDB($this->dbOptions);
		$this->db->osimo = $this;
		$this->user = new OsimoUser();
		$this->theme = new OsimoTheme($this->themeOptions);
		$this->data = new OsimoData();
		$this->theme->osimo = $this;
		$this->bbparser = new OsimoBBParser();
		
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
			foreach($data as $conf){
				$this->config[$conf['name']] = $conf['value'];
			}
			$_SESSION['config'] = $this->config;
		}
		else{
			$this->config = $_SESSION['config'];
		}
		
		define('OS_SITE_TITLE',$this->config['site_title']);
		define('OS_SITE_DESC',$this->config['site_description']);
	}
	
	public function requireGET($id,$numeric=false,$redirect='index.php'){
		if(!isset($_GET[$id])){
			if($this->theme->page_type == 'index' || !$redirect){
				$this->debug->error("OsimoCore: missing parameter '$id'",__LINE__,__FUNCTION__,__FILE__,true);
				return false;
			}
			
			header('Location: '.$redirect); exit;
			return false;
		}
		else{
			if($numeric && !is_numeric($_GET[$id])){
				if($redirect){
					header('Location: '.$redirect); exit;
				}
				else{
					$this->debug->error("OsimoCore: invalid parameter '$id'",__LINE__,__FUNCTION__,__FILE__,true);
				}
				
				return false;
			}
		}
		
		if($numeric){
			$this->GET[$id] = $_GET[$id];
		}
		else{
			$this->GET[$id] = $this->db->escape($_GET[$id]);		
		}

		return true;
	}
	
	public function requirePOST($id,$numeric=false,$redirect='index.php'){
		if(!isset($_POST[$id])){
			if($this->theme->page_type == 'index' || !$redirect){
				$this->debug->error("OsimoCore: missing parameter '$id'",__LINE__,__FUNCTION__,__FILE__,true);
				return false;
			}
			
			header('Location: '.$redirect); exit;
			return false;
		}
		else{
			if($numeric && !is_numeric($_POST[$id])){
				if($redirect){
					header('Location: '.$redirect); exit;
				}
				else{
					$this->debug->error("OsimoCore: invalid parameter '$id'",__LINE__,__FUNCTION__,__FILE__,true);
				}
				
				return false;
			}
		}
		
		if($numeric){
			$this->POST[$id] = $_POST[$id];
		}
		else{
			$this->POST[$id] = $this->db->escape($_POST[$id]);		
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
	
	public function debug($switch){
		if($switch){
			$this->debug = true;
		}
		else{
			$this->debug = false;
		}
	}
	
	public function debugMsg($type,$data){
		if($this->debug){
			$this->log[] = ucwords($type).': '.$data;
		}
	}
	
	public function output_log($echo=true){
		if($echo){
			foreach($this->log as $log){
				echo $log;
			}
		}
		else{
			return $this->log;
		}
	}
	
	public static function loadIncludes($siteFolder){
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/osimo_module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/osimo_dynamic.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/classes/user.class.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/classes/osimomodel.class.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/classes/bbparser.class.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/debug.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/paths.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/db2.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/cache.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/theme.module.php');
		include_once($_SERVER['DOCUMENT_ROOT'].$siteFolder.'/os-includes/modules/data.module.php');
	}
	
	/* Dynamicly loaded objects */
	public function forum($args=false){
		return $this->initDynamicObj($args,'OsimoForum','forum','forum');
	}
	
	public function category($args=false){
		return $this->initDynamicObj($args,'OsimoCategory','category','category');
	}
	
	private function initDynamicObj($args,$Class,$var,$file){
		if(!isset($this->$var) && !class_exists($Class) && file_exists(ABS_INC_CLASSES.$file.'.class.php')){
			include_once(ABS_INC_CLASSES.$file.'.class.php');
		}
		else{
			$this->debug->error("OsimoCore: unable to locate class file '$file.class.php'",__LINE__,__FUNCTION__,__FILE__,true);
			return false;
		}
		
		if(!isset($this->$var) || !is_object($this->var)){
			$this->$var = new $Class($args);
		}
		
		return $this->$var;
	}
	
	public static function validateOQLArgs($args,$allowed,$escape=false){
		$data = explode('&',$args);
		$final = array();
		foreach($data as $arg){
			$temp = explode('=',$arg);
			if(array_key_exists($temp[0],$allowed)){
				if(
					$allowed[$temp[0]] == 'any' || 
					($allowed[$temp[0]] == 'numeric' && is_numeric($temp[1])) || 
					($allowed[$temp[0]] == 'string' && is_string($temp[1]))
				  )
				{
					if($escape){
						$temp[1] = get('db')->escape($temp[1],true);
					}
				  	else{
				  		$temp[1] = "'".$temp[1]."'";
				  	}
					
					$final[] = $temp[0].'='.$temp[1];
				}
			}
		}
		
		return $final;
	}
	
	public static function getPageLimits($page,$num){
		return array(
			"start"=>($page-1)*$num,
			"num"=>$num
		);
	}
}

/* Singleton class retriever */
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