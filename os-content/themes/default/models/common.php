<?
class Common extends OsimoModel{
	function Common(){
		parent::OsimoModel();
	}
	
	public function header(){
		$this->osimo->theme->autoTitle();
		$this->osimo->theme->get_header(true);
	}
}
?>