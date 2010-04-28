<?
class OsimoException extends Exception {
	protected $type;
	
	public function OsimoException($type, $msg) {
		$this->type = $type;
		$this->message = $msg;
	}
	
	public function getExceptionType() {
		return $this->type;
	}
}
?>