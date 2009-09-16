<?
class OsimoTheme extends OsimoModule{
	protected $theme,$title;
	private $css,$js;
	
	function OsimoTheme($options=false){
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
		
		$this->addJavascript(SITE_URL.'os-includes/js/jquery.js');
		$this->addJavascript(SITE_URL.'os-includes/js/jquery-ui.js');
	}
	
	public function setTitle($title){
		$this->title = $title.' - Powered by Osimo';
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
}
?>