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
			if((isset($_SESSION['user']) && $_SESSION['user']['id'] != $mgr->activeUser->id) || !isset($_SESSION['user'])) {
				$_SESSION['user']['id'] = $mgr->activeUser->id;
				$_SESSION['user']['username'] = $mgr->activeUser->username;
				$_SESSION['user']['email'] = $mgr->activeUser->email;
				$_SESSION['user']['time_format'] = $mgr->activeUser->time_format;
				$_SESSION['user']['ip_address'] = $mgr->activeUser->ip_address;
			}
		}
	}
	
	public static function login_user($username, $password) {
		$mgr = self::instance();
		$osimo = Osimo::instance();
		
		if(strlen($username)<3||strlen($username)>24||preg_match('/[^\w]/', $username)){
			throw new OsimoException("invalid_username", "The username entered is invalid, please try again.");
		}
		
		if(strlen($password) < 3) {
			throw new OsimoException("invalid_password", "The password entered is invalid, please try again.");
		}
		
		$password = self::hash_password($password);
		
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
				throw new OsimoException("wrong_password", "The password entered is incorrect, please try again.");
			}
		} else {
			throw new OsimoException("user_not_found", "The username entered could not be found.");
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
	
	public static function register_user($username, $password, $email, $autologin = true) {
		$username = OsimoDB::escape($username);
		$email = OsimoDB::escape($email);
		$time_joined = OsimoDB::formatDateForDB();
		
		/* Error checking */
		if(strlen($username)<3||strlen($username)>24||preg_match('/[^\w]/', $username)){
			throw new OsimoException('invalid_username', "The username given is invalid, please choose a different one");
		}
		
		if(!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', $email)) {
			throw new OsimoException('invalid_email', "The email address given is not valid.");
		}
		
		if(strlen($password) < 3) {
			throw new OsimoException('password_too_short', "The password entered is too short, please choose a different one");
		}
		
		$password = self::hash_password($password);
		
		if(self::user_exists($username)) {
			throw new OsimoException('username_exists', "The username given already exists, please login or choose a different username.");
		}
		
		/* Create the new user */
		$query = "
			INSERT INTO users (
				username,
				email,
				password,
				ip_address,
				time_joined
			) VALUES (
				'$username',
				'$email',
				'$password',
				'{$_SERVER['REMOTE_ADDR']}',
				'$time_joined'
			)";
		$result = get('db')->query($query)->insert($userID);
		if($result) {
			if($autologin) {
				self::set_logged_in_user(new OsimoUser($userID));
			}
			
			return $userID;
		} else {
			throw new OsimoException('fail', "There was an error registering your username, please try again");
		}
	}
	
	public static function update_user_info($user, $info) {
		if(!is_array($info)) {
			throw new OsimoException(OSIMO_EXPECTED_ARRAY, "Expected array for second argument to update_user_info");
		}
		
		$userID = 0;
		if(is_numeric($user)) {
			$userID = $user;
		} elseif(is_object($user) && $user instanceof OsimoUser) {
			$userID = $user->id;
		}
		
		$info = OsimoDB::escape($info, true);
		$result = get('db')->update('users')->set($info)->where("id = %d", $userID)->update();
		if(!$result) {
			throw new OsimoException(USER_UPDATE_FAIL, "There was an error updating the information for this user");
		}
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