<?
/**
 * This is a little gross, but it gets rid of errors
 * that were coming up because of calls to get('debug')
 * inside of classes used by the install script which are
 * loaded individually instead of loading the whole osimo
 * system.
 */
class DummyDebug{
	public function register() {
		return true;
	}

	public function logMsg() {
		return true;
	}
	
	public function timerStart() {
		return true;
	}
	
	public function timerEnd() {
		return true;
	}
}
?>