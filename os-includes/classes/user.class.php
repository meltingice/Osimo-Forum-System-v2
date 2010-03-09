<?
class OsimoUser{
	public $id,$username,$email,$time_format;
	private $is_guest,$ip_address;
	
	function OsimoUser($id=false,$is_viewer=true){
		get('debug')->register('OsimoUser',array(
			'events'=>false,
			'benchmarking'=>false
		));
	
		if($id && !is_numeric($id)){
			get("debug")->error("OsimoUser: invalid user ID specified",true);
			return false;
		}

		if(!$is_viewer){
			$this->loadBasicInfo($id);
			return true;
		}
		
		if(isset($_SESSION['user']) && ($id==false || $_SESSION['user']['id']==$id)){
			$this->loadFromSession();
			$this->is_guest = 0;
		}
		elseif(isset($_SESSION['user'])){
			$this->loadFromDB($id);
			$this->is_guest = 0;
		}
		else{
			$this->loadAsGuest();
		}
		
		$this->init();
	}
	
	private function loadFromSession(){
		get('debug')->logMsg('OsimoUser','events','Loading user from saved session.');
		foreach($_SESSION['user'] as $key=>$val){
			$this->$key = $val;
		}
	}
	
	private function loadFromDB($id){
		get('debug')->logMsg('OsimoUser','events','Loading user from database.');
		$user = get("db")->select('id,username,email,ip_address,time_format')->from('users')->where('id=%d',$id)->row(true,300);
		foreach($user as $key=>$val){
			$this->$key = $val;
		}
	}
	
	private function loadAsGuest(){
		get('debug')->logMsg('OsimoUser','events','Setting user as guest.');
		$this->id = 0;
		$this->username = 'Guest';
		$this->email = false;
		$this->is_guest = true;
		$this->ip_address = $_SERVER['REMOTE_ADDR'];
		$this->time_format = 'M j, Y';
	}
	
	private function loadBasicInfo($id){
		get('debug')->logMsg('OsimoUser','events','Loading information for user ID #'.$id);
		$loadInfo = array(
			'id','username',
			'email','ip_address',
			'signature','posts',
			'is_admin','is_global_mod',
			'time_joined'
		);
		$user = get('db')->select($loadInfo)->from('users')->where('id=%d',$id)->row(true,86400);
		foreach($user as $key=>$val){
			$this->$key = $val;
		}
		
		$this->time_joined = date(get('user')->time_format,strtotime($this->time_joined));
	}
	
	private function init(){
		if(!$this->ip_check()){
			return false;
		}
	}
	
	private function ip_check(){
		if($this->is_guest){ return true; }
		if($this->ip_address != $_SERVER['REMOTE_ADDR']){
			get('debug')->logMsg('OsimoUsers','events','User logging in from new IP, updating database...');
			get('db')->update('users')
				->set(array('ip_address'=>"'".$_SERVER['REMOTE_ADDR']."'"))
				->where('id=%d',$this->id)
				->limit(1)
			->update();
		}
		
		return true;
	}
	
	public function update_user_stats(){
		if($this->is_guest){
			return true;
		}
		
		$last_page = get('paths')->getCurrentPage();
		$last_visit = get('db')->formatDateForDB();
		$page_type = get('theme')->page_type;
		
		get('debug')->logMsg('OsimoUser','events',"Updating user browsing information. Page: $last_page, page type: $page_type, visit time: $last_visit");
		
		$result = get('db')->update('users')
			->set(array(
				'last_page'=>"'$last_page'",
				'last_page_type'=>"'$page_type'",
				'time_last_visit'=>"'$last_visit'"
			))
			->where('id=%d',$this->id)
			->limit(1)
		->update();
		
		if($result){ return true; }
		return false;
	}
	
	public function increase_post_count(){
		$result = get('db')->update('users')->set(array('posts'=>'posts+1'))->where('id=%d',$this->id)->limit(1)->update();
		if($result){ return true; }
		return false;
	}
	
	public function avatar(){
		if(file_exists(ABS_AVATARS.$this->id.'.png')){
			return URL_AVATARS.$this->id.'.png';
		}
		elseif(file_exists(ABS_AVATARS.$this->id.'.jpg')){
			return URL_AVATARS.$this->id.'.jpg';
		}
		elseif(file_exists(ABS_AVATARS.$this->id.'.gif')){
			return URL_AVATARS.$this->id.'.gif';
		}
		else{
			return URL_AVATARS.'noavatar.jpg';
		}
	}
	
	public function profile_link(){
		/* This will automatically format for classes/groups later */
		return '<a href="'.SITE_URL.'profile.php?id='.$this->id.'">'.$this->username.'</a>';
	}
	
	public static function get_profile_link($id,$username){
		return '<a href="'.SITE_URL.'profile.php?id='.$id.'">'.$username.'</a>';
	}
	
	public function is_logged_in(){
		return ($this->id != 0);
	}
	
	public function date_format($date,$inc_time=false){
		if(!is_numeric($date)){ $date = strtotime($date); }
		if($inc_time){ return date($this->time_format.' g:ia',$date); }
		else{ return date($this->time_format,$date); }
	}
}
?>