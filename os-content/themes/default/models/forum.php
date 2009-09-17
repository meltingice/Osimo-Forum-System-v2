<?
class Forum extends OsimoModule{
	public $id;
	
	function Forum($osimo){
		parent::OsimoModule($osimo);
	}
	
	public function dump(){
		print_r(get('db')->select('*')->from('forum')->rows());
	}
}
?>