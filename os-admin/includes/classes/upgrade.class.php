<?
class OsimoUpgrade {
	private $repo_loc;
	
	function OsimoUpgrade(){
		$this->repo_loc = "http://repo.getosimo.com/releases/";
	}
	
	public function can_upgrade(&$latest){
		$req = get('config')->branch . '/LATEST';
		$latest = $this->do_remote_request($req);
		
		if($latest == get('config')->version){
			return false;
		}
		
		return true;
	}
	
	private function do_remote_request($req){
		return file_get_contents($this->repo_loc . $req);
	}
}
?>