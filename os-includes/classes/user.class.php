<?
class OsimoUser{
	public $id,$username,$email;
	private $is_guest,$ip_address;
	
	function OsimoUser($id=false){
		if($id && !is_numeric($id)){
			get("debug")->error("OsimoUser: invalid user ID specified",true);
			return false;
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
		$user = get("db")->select('id,username,email,ip_address')->from('users')->where('id=%d',$id)->row();
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
}
?>