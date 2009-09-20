<?
class Forum extends OsimoModel{
	public $id;
	
	function Forum(){
		parent::OsimoModel();
		$this->id = $_GET['id'];
	}
	
	public function title(){
		echo get("osimo")->forum()->title;
	}
	
	public function dump(){
		print_r(get("osimo")->forum());
	}
}
?>