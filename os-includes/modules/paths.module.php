<?
class OsimoPaths extends OsimoModule{
	protected $site_folder;
	private $site_root;
	private $site_url;
	
	public function OsimoPaths(){
		parent::OsimoModule();
		$config = array(
			'site_folder' => ConfigManager::instance()->get('site_folder')
		);
		$this->set_options($config);
		
		$this->site_folder = $this->parseFolderPath($this->site_folder);

		$this->site_root = $_SERVER['DOCUMENT_ROOT'].$this->site_folder;
		
		$this->init();
	}
	
	private function init(){
		$this->determineURL();
		
		/* Define absolute paths */
		define('ABS_ROOT',$this->site_root);
		define('ABS_INC',ABS_ROOT.'os-includes/');
		define('ABS_INC_CLASSES',ABS_INC.'classes/');
		define('ABS_INC_MODULES',ABS_INC.'modules/');
		define('ABS_CONFIG', ABS_INC.'CONFIG');
		define('ABS_THEMES',ABS_ROOT.'os-content/themes/');
		define('ABS_AVATARS',ABS_ROOT.'os-content/avatars/');
		define('ABS_DEFAULT_CONTENT',ABS_INC.'default_content/');
		
		/* Define relative paths */
		define('SITE_URL',$this->site_url);
		define('URL_THEMES',SITE_URL.'os-content/themes/');
		define('URL_AVATARS',SITE_URL.'os-content/avatars/');
		define('URL_JS',SITE_URL.'os-includes/js/');
		define('URL_DEFAULT_CONTENT',SITE_URL.'os-includes/default_content/');
		
		/* If we are in the admin panel */
		if(IS_ADMIN_PAGE == true){
			define('ABS_ADMIN', ABS_ROOT.'os-admin/');
			define('ABS_ADMIN_INC', ABS_ADMIN.'includes/');
			define('ABS_ADMIN_CLASSES', ABS_ADMIN_INC.'classes/');
		}
	}
	
	private function parseFolderPath($folder,$s_slash=true,$e_slash=true){
		if($s_slash && $folder[0]!='/'){
			$folder = '/'.$folder;
		}
		elseif(!$s_slash){
			if($folder[0]=='/'){
				$folder = substr($folder,1);
			}
		}
		
		if($e_slash && $folder[strlen($folder)-1]!='/'){
			$folder .= '/';
		}
		elseif(!$e_slash){
			if($folder[strlen($folder)-1]=='/'){
				$folder = substr($folder,0,-1);
			}
		}
		
		return $folder;
	}
	
	private function determineURL(){
		$this->site_url = 'http://'.$_SERVER['SERVER_NAME'].$this->site_folder;
	}
	
	public function path($path,$abs=true){
		if($abs){
			return $this->site_root.$this->parseFolderPath($path,false,true);
		}
		else{
			return $this->site_url.$this->parseFolderPath($path,false,true);
		}
	}
	
	public function getCurrentPage(){
		$temp = explode($this->site_folder,$_SERVER['REQUEST_URI']);
		return $temp[1];
	}
}
?>