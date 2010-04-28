<?
class ErrorManager {

	public static function error_redirect($type, $data, $location) {
		$_SESSION['error']['data'] = $data;
		$_SESSION['error']['type'] = $type;
		$_SESSION['error']['is_checked'] = false;
		
		header('Location: '.$location);
	}
	
	public static function is_error() {
		if(!isset($_SESSION['error']) || $_SESSION['error']['is_checked']) {
			return false;
		}
		
		return true;
	}
	
	public static function get_error() {
		if(self::is_error()) {
			$_SESSION['error']['is_checked'] = true;
			return array("msg"=>$_SESSION['error']['data'], "type"=>$_SESSION['error']['type']);
		}
		
		return "";
	}
}
?>