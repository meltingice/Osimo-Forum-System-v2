<?
class Forum extends OsimoModule{
	public $id;
	
	function Forum(){
		parent::OsimoModule();
		$this->id = $_GET['id'];
	}
	
	public function dump(){
		print_r(get('db')->select('*')->from('forum')->where('id=%d',$this->id)->row());
	}
}
?>