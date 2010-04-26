<?php

/**
 * Contains all interaction related to users.
 *
 * @author Ryan LeFevre
 */
class OsimoUser {
	public $id, $username, $email, $time_format, $ip_address;
	private $is_loaded, $is_guest, $is_viewer;

	/**
	 * Class constructor.
	 *
	 * @param mixed $id        (optional)
	 *		If false, the system tries to load the user from session variables. Otherwise,
	 *		the system will load the user with the ID specified.
	 * @param boolean $is_viewer (optional)
	 *		Is the user this class is loading the actual user that is browsing the forum?
	 */
	function OsimoUser($args=array(), $is_viewer=true, $autoload=true) {
		get('debug')->register('OsimoUser', array(
				'events'=>false,
				'benchmarking'=>false
			));
	
		if(is_array($args)) {
			foreach($args as $key=>$val) {
				$this->$key = $val;
			}
		} elseif(is_numeric($args)) {
			$this->id = $args;
		}

		if ($this->id && !is_numeric($this->id)) {
			throw new Exception("OsimoUser: invalid user ID specified");
		}
		
		$this->is_viewer = $is_viewer;
		$this->is_loaded = false;
		
		if($autoload) {
			$this->load();
		}
	}
	
	public function load() {
		if (!$this->is_viewer) {
			$this->loadBasicInfo();
		} elseif (isset($_SESSION['user']) && ($this->id == false || $_SESSION['user']['id'] == $this->id)) {
			$this->loadFromSession();
			$this->is_guest = 0;
		} elseif ($this->id != 0) {
			$this->loadFromDB($this->id);
			$this->is_guest = 0;
		} else {
			$this->loadAsGuest();
		}
		
		$this->is_loaded = true;

		$this->init();
	}
	
	public function is_loaded() {
		return $this->is_loaded;
	}

	private function loadFromSession() {
		get('debug')->logMsg('OsimoUser', 'events', 'Loading user from saved session.');
		foreach ($_SESSION['user'] as $key=>$val) {
			$this->$key = $val;
		}
	}

	private function loadFromDB($id) {
		get('debug')->logMsg('OsimoUser', 'events', 'Loading user from database.');
		$user = get("db")->select('id,username,email,ip_address,time_format')->from('users')->where('id=%d', $id)->row(true, 300);
		foreach ($user as $key=>$val) {
			$this->$key = $val;
		}
	}

	private function loadAsGuest() {
		get('debug')->logMsg('OsimoUser', 'events', 'Setting user as guest.');
		$this->id = 0;
		$this->username = 'Guest';
		$this->email = false;
		$this->is_guest = true;
		$this->ip_address = $_SERVER['REMOTE_ADDR'];
		$this->time_format = 'M j, Y';
	}

	private function loadBasicInfo() {
		get('debug')->logMsg('OsimoUser', 'events', 'Loading information for user ID #'.$this->id);
		$loadInfo = array(
			'id', 'username',
			'email', 'ip_address',
			'signature', 'posts',
			'is_admin', 'is_global_mod',
			'time_joined'
		);
		$user = get('db')->select($loadInfo)->from('users')->where('id=%d', $this->id)->row(true, 86400);
		foreach ($user as $key=>$val) {
			$this->$key = $val;
		}

		$this->time_joined = date(get('user')->time_format, strtotime($this->time_joined));
	}

	private function init() {
		if (!$this->ip_check()) {
			return false;
		}
	}

	private function ip_check() {
		if ($this->is_guest) { return true; }
		if ($this->ip_address != $_SERVER['REMOTE_ADDR']) {
			get('debug')->logMsg('OsimoUsers', 'events', 'User logging in from new IP, updating database...');
			get('db')->update('users')
			->set(array('ip_address'=>"'".$_SERVER['REMOTE_ADDR']."'"))
			->where('id=%d', $this->id)
			->limit(1)
			->update();
		}

		return true;
	}

	/**
	 * Updates the database with the user's latest
	 * visit time and visit location.
	 *
	 * @return Boolean for the success of the update.
	 */
	public function update_user_stats() {
		if ($this->is_guest) {
			return true;
		}
		
		$last_page = get('paths')->getCurrentPage();
		$last_visit = get('db')->formatDateForDB();
		$page_type = get('theme')->page_type;

		get('debug')->logMsg('OsimoUser', 'events', "Updating user browsing information. Page: $last_page, page type: $page_type, visit time: $last_visit");

		$result = get('db')->update('users')
		->set(array(
				'last_page'=>"'$last_page'",
				'last_page_type'=>"'$page_type'",
				'time_last_visit'=>"'$last_visit'"
			))
		->where('id=%d', $this->id)
		->limit(1)
		->update();

		if ($result) { return true; }
		return false;
	}

	/**
	 * Increases this user's post count by one in the database.
	 *
	 * @return Boolean result of the database update.
	 */
	public function increase_post_count() {
		$result = get('db')->update('users')->set(array('posts'=>'posts+1'))->where('id=%d', $this->id)->limit(1)->update();
		if ($result) { return true; }
		return false;
	}

	/**
	 * Retrieves the URL to this user's avatar.
	 *
	 * @return The link to the avatar.
	 */
	public function avatar() {
		if (file_exists(ABS_AVATARS.$this->id.'.png')) {
			return URL_AVATARS.$this->id.'.png';
		}
		elseif (file_exists(ABS_AVATARS.$this->id.'.jpg')) {
			return URL_AVATARS.$this->id.'.jpg';
		}
		elseif (file_exists(ABS_AVATARS.$this->id.'.gif')) {
			return URL_AVATARS.$this->id.'.gif';
		}
		else {
			return URL_AVATARS.'noavatar.jpg';
		}
	}

	/**
	 * Generates a link to this user's profile page.
	 *
	 * @return A link to the user's profile.
	 */
	public function profile_link() {
		/* This will automatically format for classes/groups later */
		return '<a href="'.SITE_URL.'profile.php?id='.$this->id.'">'.$this->username.'</a>';
	}

	/**
	 * Is this user logged in?
	 *
	 * @return Boolean representing if the user is logged in or not.
	 */
	public function is_logged_in() {
		return $this->id != 0;
	}

	/**
	 * Formats a date based on this user's preferences.
	 *
	 * @param mixed $date
	 *		Can be in timestamp or already formatted date format.
	 * @param boolean $inc_time (optional)
	 *		Should the returned date include the time as well?
	 * @return The newly formatted date.
	 */
	public function date_format($date, $inc_time=false) {
		if (!is_numeric($date)) { $date = strtotime($date); }
		if ($inc_time) { return date($this->time_format.' g:ia', $date); }
		else { return date($this->time_format, $date); }
	}
}
?>