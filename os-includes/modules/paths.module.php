<?
class OsimoPaths extends OsimoModule{
	private $siteFolder;
	private $siteRoot;
	private $siteURL;
	
	public function OsimoPaths($siteFolder){
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
		
		/* Define relative paths */
		define('SITE_URL',$this->siteURL);
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
}
?>