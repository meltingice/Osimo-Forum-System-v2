<?
class OsimoForum extends OsimoDynamic{
	public $id;
	
	function OsimoForum($options){
		parent::OsimoDynamic(array('id'));
		$this->parseOptions($options);
		
		$this->init();
	}
	
	private function init(){
		if(!$this->id){
			$this->id = $_GET['id']; //guaranteed to exist by requreGET
		}
		
		$this->loadData();
	}
	
	private function loadData(){
		$data = get('db')->select('*')->from('forum')->where($this->buildWhere())->row();
		foreach($data as $key=>$val){
			$this->$key = $val;
		}
	}
	
	public function data($var){
		if(!isset($this->$var)){
			return false;
		}
		
		return $this->$var;
	}
}
?>