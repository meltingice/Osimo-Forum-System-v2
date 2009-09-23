<?
class Common extends OsimoModel{
	public $five = 5;
	
	function Common(){
		parent::OsimoModel();
	}
	
	public function header(){
		$this->osimo->theme->autoTitle();
		$this->osimo->theme->addJavascript(URL_THEME.'js/backend.js');
		$this->osimo->theme->addStylesheet(URL_THEME.'css/styles.css');
		$this->osimo->theme->get_header(true);
	}
	
	public function test($id){
		echo "Number given is: $id";
	}
}
?>