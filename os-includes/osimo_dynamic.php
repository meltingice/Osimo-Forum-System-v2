<?
class OsimoDynamic{
	public $osimo;
	protected $allowOpts;
	
	function OsimoDynamic($allowOpts){
		$this->osimo = get("osimo");
		if(!is_array($allowOpts)){
			$this->allowOpts = explode(',',$allowOpts);
		}
		else{
			$this->allowOpts = $allowOpts;
		}
	}
	
	protected function parseOptions($options){
		foreach($this->allowOpts as $opt){
			isset($options[$opt]) ? $this->$opt = $options[$opt] : $this->$opt = NULL;
		}
	}
	
	protected function buildWhere(){
		$whereOpts = array();
		foreach($this->allowOpts as $opt){
			if(!empty($opt)){
				$whereOpts[] = $opt.'='.get('db')->escape($this->$opt,true);
			}
		}
		
		return implode(",",$whereOpts);
	}
}
?>