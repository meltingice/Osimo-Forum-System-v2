<?
class OsimoDebug extends OsimoModule{
	private $modules;
	private $scriptStart;
	private $timers_start;
	private $timer_desc;
	private $timer_duration;
	private $msgs;
	private $errors;
	private $error_backtrace;
	private $override;
	
	function OsimoDebug($options=false,$override=true){
		parent::OsimoModule();
		$this->modules = $options;
		$this->override = $override;
		$this->scriptStart = microtime(true);
	}
	
	public function register($module,$defaults){
		if(isset($this->modules[$module])){
			foreach($defaults as $option=>$val){
				if(!isset($this->modules[$module][$option])){
					$this->modules[$module][$option] = $val;
				}
			}
		}
		else{
			$this->modules[$module] = $defaults;
		}
	}
	
	public function enabled($module,$type){
		if($this->override == true){ return false; }
		if(isset($this->modules[$module][$type]) && $this->modules[$module][$type] == true){
			return true;
		}
		
		return false;
	}
	
	public function timerStart($module,$name){
		if(!$this->enabled($module,'benchmarking')){ return true; }
		
		if(!isset($this->timers_start[$module][$name])){
			$this->timers_start[$module][$name] = microtime(true);
		}
	}
	
	public function timerEnd($module,$name,$prepend=false){
		if(isset($this->timers_start[$module][$name])){
			$duration = microtime(true) - $this->timers_start[$module][$name];
			if($prepend){
				$this->logMsg($module,'benchmarking',$prepend . (microtime(true) - $this->timers_start[$module][$name])." seconds");
			}
			else{
				$this->logMsg($module,'benchmarking',(microtime(true) - $this->timers_start[$module][$name])." seconds");
			}
			
			return $duration;
		}
		
		return false;
	}
	
	public function logError($module,$type,$error){
		if($this->enabled($module,$type)){
			$this->errors[] = "$module - $type: $error";
		}
	}
	
	public function logMsg($module,$type,$msg){
		if($this->enabled($module,$type)){
			$this->msgs[] = "$module - $type: $msg";
		}
	}
	
	public function output_log(){
		if(is_array($this->msgs)){
			echo "<strong>Messages</strong><br />";
			foreach($this->msgs as $msg){
				echo $msg.'<br />';
			}
		}
		if(is_array($this->errors)){
			echo "<strong>Errors</strong><br />";
			foreach($this->errors as $error){
				echo $error.'<br />';
			}
		}
	}
	
	/*
	 * error() will always output a PHP error because it uses trigger_error().
	 * If you wish to log an issue but possibly not output it, or output it at a
	 * specific time in the script, then use the logError() function above.
	 */
	public function error($msg,$line,$function,$file,$fatal=false){
		$msg .= " in $file::$function(), line $line";
		$fatal ? trigger_error($msg,E_USER_ERROR) : trigger_error($msg);
	}
	
	public function disabled(){
		return $this->override;
	}
}
?>