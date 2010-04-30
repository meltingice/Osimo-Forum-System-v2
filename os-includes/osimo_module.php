<?
class OsimoModule{
	protected $defaults;
	public $osimo;
	
	function OsimoModule($options=array()){
		if(!is_array($options) || count($options) == 0) { return false; }
		
		$this->set_options($options);
	}
	
	protected function set_options($options) {
		if(!is_array($options)) { return false; }
		
		foreach($options as $key=>$val){
			$this->$key = $val;
		}
	}
}
?>