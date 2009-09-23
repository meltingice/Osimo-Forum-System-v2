<?
class OsimoTheme extends OsimoModule{
	public $page_type;
	protected $theme,$title;
	private $theme_path,$cache_file,$view;
	private $css,$js;
	public $classes;
	
	function OsimoTheme($options=false){
		parent::OsimoModule();
		$this->defaults = array(
			"theme"=>"default",
			"title"=>"Osimo Forum System"
		);
		
		$this->parseOptions($options);
		$this->init();
	}
	
	private function init(){
		$this->css = array();
		$this->js = array();
		
		$this->theme_path = ABS_THEMES.$this->theme.'/';
		define('ABS_THEME',$this->theme_path);
		define('URL_THEME',URL_THEMES.$this->theme.'/');
		
		$this->addJavascript(SITE_URL.'os-includes/js/jquery.js');
		$this->addJavascript(SITE_URL.'os-includes/js/jquery-ui.js');
		$this->addJavascript(SITE_URL.'os_includes/js/osimo_editor/osimo_editor.js');
	}
	
	public function setTitle($title){
		$this->title = $title.' - Powered by Osimo';
	}
	
	public function autoTitle(){
		if($this->page_type == 'index'){
			$this->setTitle(OS_SITE_TITLE);
		}
		elseif($this->page_type == 'forum'){
			$this->setTitle(OS_SITE_TITLE);
		}
	}
	
	public function addStylesheet($url){
		if(!in_array($url,$this->css)){
			$this->css[] = $url;
		}
	}
	
	public function addJavascript($url){
		if(!in_array($url,$this->js)){
			$this->js[] = $url;
		}
	}
	
	public function get_header($echo = true){
		$html = "<title>".$this->title."</title>\n";
		foreach($this->js as $js){
			$html .= "<script src=\"".$js."\"></script>\n";
		}
		foreach($this->css as $css){
			$html .= "<link rel=\"stylesheet\" href=\"".$css."\" type=\"text/css\" media=\"screen\" />\n";
		}

		if($echo){ echo $html; }
		return $html;
	}
	
	public function setPageType($type){
		$this->page_type = strtolower(str_replace(" ","_",$type));
	}
	
	private function autoSetPageType($page){
		$page = strtolower(str_replace(" ","_",$page));
		$types = array(
			'index','forum',
			'thread','profile'
		);
		
		if(in_array($page,$types)){
			$this->page_type = $page;
		}
		else{
			$this->page_type = $other;
		}
	}
	
	/* Theme parsing functions */
	public function load($file){
		if(!is_file($this->theme_path."views/$file.html")){
			$this->osimo->debug->error("OsimoTheme: unable to locate view '$file'",true);
			return false;
		}
		
		$this->autoSetPageType($file);
		
		$this->view = $this->theme_path."views/$file.html";
		$this->cache_file = $this->theme_path."cache/$file.php";
		
		if($this->validate_cache()){
			include($this->cache_file);
		}
		else{
			$this->osimo->debug->error("OsimoTheme: unable to load cache file",true);
		}
	}
	
	public function include_file($file){
		$this->view = $this->theme_path."includes/$file.html";
		$this->cache_file = $this->theme_path."cache/$file.inc.php";
		if($this->validate_cache()){
			include($this->cache_file);
			return true;
		}
	}
	
	private function validate_cache(){
		if(!is_dir($this->theme_path.'cache/')){
			if(!mkdir($this->theme_path.'cache/')){
				$this->osimo->debug->error("OsimoTheme: unable to create theme cache directory, please check folder permissions!",true);
				return false;
			}
		}
		
		if(is_file($this->cache_file)){
			if(filemtime($this->view) > filemtime($this->cache_file)){
				if(!unlink($this->cache_file)){
					$this->osimo->debug->error("OsimoTheme: unable to regenerate cache for '".$this->view."'.",true);
					return false;
				}
				
				return $this->cache_view();
			}
		}
		else{
			return $this->cache_view();
		}
		
		return true;
	}
	
	private function cache_view(){
		$view_p = fopen($this->view,'r');
		if(!$view_p){
			$this->osimo->debug->error("OsimoTheme: unable to read theme view file.",true);
			return false;
		}
		
		$html = fread($view_p,filesize($this->view));
		fclose($view_p);
		
		$html = $this->parse_view($html);
		
		$cache_p = fopen($this->cache_file,'w');
		if(!$cache_p){
			$this->osimo->debug->error("OsimoTheme: unable to create cache file, check folder permissions!",true);
			return false;
		}
		
		if(!fwrite($cache_p,$html)){
			$this->osimo->debug->error("OsimoTheme: unable to write to cache file",true);
			return false;
		}
		
		fclose($cache_p);
		
		return true;
	}
	
	private function parse_view($html){
	    $html = preg_replace(
	    	array(
	    		"/\{using ([^}]+)\}/i",
	    		"/\{include ([^}]+)\}/i",
	    		"/\{func ([A-Za-z_]+)->([^}]+)\(([^\)]*)\)\}/i",
	    		"/\{echo ([A-Za-z_]+)->([A-Za-z_]+)\}/i",
	    		"/\{var ([A-Za-z_]+)->([A-Za-z_]+)\}/i",
	    		"/\{echo (db|cache|paths|theme|debug|user)\.([A-Za-z_]+)\}/i",
	    		"/\{var (db|cache|paths|theme|debug|user)\.([A-Za-z_]+)\}/i"
	    	),
	    	array(
	    		"<? 
	    			include_once('".$this->theme_path.'models/$1.php\');
	    			get("theme")->classes["$1"] = new $1();
	    		?>',
	    		'<? get("theme")->include_file("$1"); ?>',
	    		'<? get("theme")->classes["$1"]->$2($3); ?>',
	    		'<? echo get("theme")->classes["$1"]->$2; ?>',
	    		'get("theme")->classes["$1"]->$2',
	    		'<? echo get("$1")->$2; ?>',
	    		'get("$1")->$2'
	    	)
	    	,$html);
	    
	    return $html;
	}
}
?>