<?
class Common extends OsimoModel{
	function Common($osimo){
		parent::OsimoModel($osimo);
	}
	
	public function header($title){
		$this->osimo->theme->setTitle($title);
		$this->osimo->theme->get_header(true);
	}
}
?>