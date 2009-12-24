<?
class OsimoTheme extends OsimoModule{
	public $page_type;
	protected $theme,$title;
	private $theme_path,$theme_file;
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
		
		$this->add_javascript(SITE_URL.'os-includes/js/jquery.js');
		$this->add_javascript(SITE_URL.'os-includes/js/jquery-ui.js');
		$this->add_javascript(SITE_URL.'os_includes/js/osimo_editor/osimo_editor.js');
	}
	
	/* Theme loading functions */
	public function load($file){
		$osimo = $this->osimo;
		if(!is_file($this->theme_path."$file.php")){
			$this->osimo->debug->error("OsimoTheme: unable to locate file '$file'",__LINE__,__FUNCTION__,__FILE__,true);
			return false;
		}
		
		$this->auto_set_page_type($file);
		
		$this->theme_file = $this->theme_path."/$file.php";
		include($this->theme_file);
	}
	
	public function include_theme_file($file,$echo=true){
		$osimo = $this->osimo;
		if(!is_file($this->theme_path.$file)){
			$this->osimo->debug->error("OsimoTheme: unable to locate file '$file'",__LINE__,__FUNCTION__,__FILE__,true);
			return false;
		}
		
		if($echo){
			include($this->theme_path.$file);
			return true;
		}
		else{
			return $this->include_contents($this->theme_path.$file);
		}
	}
	
	public function include_header($echo=true){
		$this->include_theme_file('header.php',$echo);
	}
	
	public function include_footer($echo=true){
		$this->include_theme_file('footer.php',$echo);
	}

	/* Theme altering functions */

	public function set_title($title){
		$this->title = $title;
		
		return $this;
	}
	
	public function get_title(){
		if(!isset($this->title)){
			$this->auto_title();
		}
		
		return $this->title;
	}
	
	public function auto_title(){
		if($this->page_type == 'index'){
			$this->set_title(OS_SITE_TITLE);
		}
		elseif($this->page_type == 'forum'){
			$this->set_title(OS_SITE_TITLE);
		}
		
		return $this;
	}
	
	public function site_title($echo=true){
		if($echo){ echo OS_SITE_TITLE; } else{ return OS_SITE_TITLE; }
	}
	
	public function site_description($echo=true){
		if($echo){ echo OS_SITE_DESC; } else { return OS_SITE_DESC; }
	}
	
	public function add_stylesheet($url){
		if(!in_array($url,$this->css)){
			$this->css[] = URL_THEME.$url;
		}
		
		return $this;
	}
	
	public function add_javascript($url){
		if(!in_array($url,$this->js)){
			$this->js[] = URL_THEME.$url;
		}
		
		return $this;
	}
	
	public function get_header($echo = true){
		if(!$this->title){ $this->auto_title(); }
		
		$html = "<title>".$this->title." - Powered by Osimo</title>\n";
		foreach($this->js as $js){
			$html .= "<script src=\"".$js."\"></script>\n";
		}
		foreach($this->css as $css){
			$html .= "<link rel=\"stylesheet\" href=\"".$css."\" type=\"text/css\" media=\"screen\" />\n";
		}

		if($echo){ echo $html; }
		return $html;
	}
	
	public function set_page_type($type){
		$this->page_type = strtolower(str_replace(" ","_",$type));
	}
	
	private function auto_set_page_type($page){
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
	
	/* Utility Functions */
	public function is_index(){
		if($this->page_type == 'index'){ return true; }
		return false;
	}
	
	public function is_forum(){
		if($this->page_type == 'forum'){ return true; }
		return false;
	}
	
	public function is_thread(){
		if($this->page_type == 'thread'){ return true; }
		return false;
	}
	
	public function include_contents($filename){
		$osimo = $this->osimo;

		if (is_file($filename)) {
			ob_start();
			include $filename;
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
    	}
    	
    	return false;
	}
}
?>