<?
class OsimoPaths extends OsimoModule{
	private $siteFolder;
	private $siteRoot;
	private $siteURL;
	
	public function OsimoPaths($siteFolder){
		parent::OsimoModule();
		$this->siteFolder = $this->parseFolderPath($siteFolder);

		$this->siteRoot = $_SERVER['DOCUMENT_ROOT'].$this->siteFolder;
		
		$this->init();
	}
	
	private function init(){
		$this->determineURL();
		
		/* Define absolute paths */
		define('ABS_ROOT',$this->siteRoot);
		define('ABS_INC',ABS_ROOT.'os-includes/');
		define('ABS_INC_CLASSES',ABS_INC.'classes/');
		define('ABS_INC_MODULES',ABS_INC.'modules/');
		define('ABS_THEMES',ABS_ROOT.'os-content/themes/');
		define('ABS_AVATARS',ABS_ROOT.'os-content/avatars/');
		define('ABS_DEFAULT_CONTENT',ABS_INC.'default_content/');
		
		/* Define relative paths */
		define('SITE_URL',$this->siteURL);
		define('URL_THEMES',SITE_URL.'os-content/themes/');
		define('URL_AVATARS',SITE_URL.'os-content/avatars/');
		define('URL_JS',SITE_URL.'os-includes/js/');
		define('URL_DEFAULT_CONTENT',SITE_URL.'os-includes/default_content/');
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
		$this->siteURL = 'http://'.$_SERVER['SERVER_NAME'].$this->siteFolder;
	}
	
	public function path($path,$abs=true){
		if($abs){
			return $this->siteRoot.$this->parseFolderPath($path,false,true);
		}
		else{
			return $this->siteURL.$this->parseFolderPath($path,false,true);
		}
	}
	
	public function getCurrentPage(){
		$temp = explode($this->siteFolder,$_SERVER['REQUEST_URI']);
		return $temp[1];
	}
}
?>