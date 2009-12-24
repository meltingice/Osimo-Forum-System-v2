<?
class OsimoDebug extends OsimoModule{
	protected $debugLevel;
	private $scriptStart;
	private $timers_start;
	private $timer_desc;
	private $timer_duration;
	private $msgs;
	private $errors;
	private $error_backtrace;
	
	function OsimoDebug($options=false){
		parent::OsimoModule();
		$this->defaults = array(
			'debugLevel'=>0
		);
		$this->parseOptions($options);
		
		$this->scriptStart = microtime(true);
	}
	
	public function timerStart($name,$desc=false){
		if(!isset($this->timers_start[$name])){
			$this->timers_start[$name] = microtime(true);
			$this->timer_desc[$name] = $desc;
		}
	}
	
	public function timerEnd($name){
		if(isset($this->timers_start[$name])){
			$this->timer_duration[$name] = microtime(true) - $this->timers_start[$name];
		}
	}
	
	public function logError($error,$fatal=false){
		$type = str_replace(" ","_",strtolower($type));
		
		$this->errors[md5($error)] = $error;
		if($this->debugLevel>0){
			$this->error_backtrace[md5($error)] = debug_backtrace();
		}
	}
	
	public function logMsg($msg){
		$this->msgs[] = $msg;
	}
	
	public function error($msg,$line,$function,$file,$fatal=false){
		$msg .= " in $file::$function(), line $line";
		$fatal ? trigger_error($msg,E_USER_ERROR) : trigger_error($msg);
	}
	
	public function dump(){
		echo "Errors:<br />";
		foreach($this->errors as $hash=>$error){
			echo $error."<br />";
			if($debugLevel > 0 && isset($this->error_backtrace[$hash])){
				echo "Debug Backtrace:<br />".$this->error_backtrace[$hash];
			}
		}
	}
}
?>