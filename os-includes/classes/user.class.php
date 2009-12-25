<?
class OsimoUser{
	public $id,$username,$email;
	private $is_guest,$ip_address;
	
	function OsimoUser($id=false,$is_viewer=true){
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
		foreach($_SESSION['user'] as $key=>$val){
			$this->$key = $val;
		}
	}
	
	private function loadFromDB($id){
		$user = get("db")->select('id,username,email,ip_address,time_format')->from('users')->where('id=%d',$id)->row(true,300);
		foreach($user as $key=>$val){
			$this->$key = $val;
		}
	}
	
	private function loadAsGuest(){
		$this->id = 0;
		$this->username = 'Guest';
		$this->email = false;
		$this->is_guest = 1;
		$this->ip_address = $_SERVER['REMOTE_ADDR'];
	}
	
	private function loadBasicInfo($id){
		$user = get('db')->select('id,username')->from('users')->where('id=%d',$id)->row(true,86400);
		foreach($user as $key=>$val){
			$this->$key = $val;
		}
	}
	
	private function init(){
		if(!$this->ip_check()){
			return false;
		}
	}
	
	private function ip_check(){
		if($this->ip_address != $_SERVER['REMOTE_ADDR']){
			//get('db')->update('users')->set()
		}
		
		return true;
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
}
?>