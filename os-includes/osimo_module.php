<?
class OsimoModule{
	protected $defaults;
	
	function OsimoModule(){
		
	}
	
	public function options($option){
		foreach($option as $key=>$val){
			if(!isset($this->defaults[$key])){ continue; }
			$this->$key = $val;
		}

		return true;
	}
	
	protected function parseOptions($options){
		foreach($this->defaults as $key=>$val){
			if(is_array($options) && isset($options[$key]) && !empty($options[$key])){
				$this->$key = $options[$key];
			}
			else{
				$this->$key = $val;
			}
		}
	}
}
?>