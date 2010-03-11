<?
class OsimoAjax{
	protected $triggers;
	
	function OsimoAjax(){
		$this->triggers = array();
		get('osimo')->ajax_mode = true;
	}
	
	protected function register($triggers){
		if(is_array($triggers)){
			$this->triggers = $triggers;
			
			if(isset($_POST['ajax_trigger']) && isset($triggers[$_POST['ajax_trigger']])){
				$this->run_trigger($triggers[$_POST['ajax_trigger']]);
			}
		}
	}
	
	private function run_trigger($func){
		foreach($_POST as $var=>$POST){
			get('osimo')->requirePOST($var,false,false);
		}
		
		$this->$func();
	}
	
	protected function json_return($data){
		if(is_array($data)){
			echo json_encode($data);
		}
		else{
			echo json_encode(array("data"=>$data));
		}
		
		exit;
	}
	
	protected function json_error($data){
		if(is_array($data)){
			echo json_encode($data);
		}
		else{
			echo json_encode(array("error"=>$data));
		}
		
		exit;
	}
}
?>