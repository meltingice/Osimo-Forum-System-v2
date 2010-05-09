<?
/**
 * Custom exception class for Osimo that allows for
 * an "exception type" as well in order to make determining
 * the exact cause of exceptions elsewhere in code very
 * simple.
 */
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