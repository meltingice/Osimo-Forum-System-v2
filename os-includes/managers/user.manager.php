<?
/**
 * Used for working with users, especially if it involves the
 * currently logged in user.
 *
 * @author Ryan LeFevre
 */
class UserManager {
	private static $INSTANCE;
	
	/**
	 * The currently logged in user.
	 * @type OsimoUser
	 */
	public $activeUser;
	
	private function UserManager() {
	}
	
	/**
	 * Gets the singleton instance of the UserManager
	 */
	public static function instance() {
		if(is_null(self::$INSTANCE)) {
			self::$INSTANCE = new UserManager();
		}
		
		return self::$INSTANCE;
	}
	
	/*
	 * Static functions for retrieving data 
	 */
	 
	/**
	 * Gets the currently logged in user.
	 *
	 * @return User object representing the logged in user, 
	 * null if no user is logged in.
	 */
	public static function get_logged_in_user() {
		$mgr = self::instance();
		return $mgr->activeUser;
	}
	
	public static function set_logged_in_user(OsimoUser $user=null) {
		$mgr = self::instance();
		
		if(is_null($user)) {
			$mgr->activeUser = new OsimoUser();
		} else {
			$mgr->activeUser = $user;
		}
		
		if(!$mgr->activeUser->is_loaded()) {
			$mgr->activeUser->load();
		}
		
		if($mgr->activeUser->is_logged_in()) {
			$_SESSION['user']['id'] = $mgr->activeUser->id;
			$_SESSION['user']['username'] = $mgr->activeUser->username;
			$_SESSION['user']['email'] = $mgr->activeUser->email;
			$_SESSION['user']['time_format'] = $mgr->activeUser->time_format;
			$_SESSION['user']['ip_address'] = $mgr->activeUser->ip_address;
		}
	}
	
	public static function login_user($username, $password) {
		$mgr = self::instance();
		$osimo = Osimo::instance();
		
		if(!$osimo->requirePOST('osimo_username',false) || !$osimo->requirePOST('osimo_password',false)){
			throw new Exception("missing_data");
		}
		
		$username = $osimo->POST['osimo_username'];
		$password = self::hash_password($osimo->POST['osimo_password']);
		
		if(strlen($username)<3||strlen($username)>24||preg_match('/[^\w]/', $username)||strlen($password)==0){
			throw new Exception("invalid_username");
		}
		
		$user = get('db')->
			select('id,password')->
			from('users')->
			where('username=%s',$username,$password)->
			row();
		
		if($user) {
			if($user['password'] == $password) {
				self::set_logged_in_user(new OsimoUser($user['id']));
			}
			else {
				throw new Exception("wrong_password");
			}
		} else {
			throw new Exception("user_not_found");
		}
	}
	
	public static function logout_user() {
		$mgr = self::instance();
		if(is_null($mgr->activeUser)) {
			return false;
		}
		
		$mgr->activeUser = null;
		session_destroy();
		
		return true;
	}
	
	/**
	 * Generates a link to any user's profile page.
	 *
	 * @param int $id
	 *		The ID of the user whose profile you want to link to.
	 * @param String $username
	 *		The username of the user whose profile you want to link to.
	 * @return A link to the user's profile.
	 */
	public static function profile_link($id, $username) {
		return '<a href="'.SITE_URL.'profile.php?id='.$id.'">'.$username.'</a>';
	}
	
	public static function user_exists($username) {
		$result = get('db')->select('COUNT(*)')->from('users')->where('username=%s', $username)->limit(1)->cell();
		if($result > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function hash_password($password) {
		return sha1($password);
	}
}
?>