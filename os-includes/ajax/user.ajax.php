<?
require "../config.php";
require "ajax.class.php";

class OsimoAjaxUser extends OsimoAjax {
	
	function OsimoAjaxUser() {
		parent::OsimoAjax();
		$this->register(
			array(
				"checkUsernameAvailable" => "check_username_available"
			)
		);
	}
	
	protected function check_username_available() {
		$username = get('osimo')->POST['username'];
		
		$exists = OsimoUser::user_exists($username);
		$this->json_return(array("status"=>$exists));
	}
}

new OsimoAjaxUser();
?>