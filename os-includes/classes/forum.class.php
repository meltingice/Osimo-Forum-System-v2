<?
class OsimoForum{
	function OsimoForum($info){
		if(is_array($info)){
			foreach($info as $key=>$val){
				$this->$key = $val;
			}
			
			$this->format_dates();
		}
	}
	
	private function format_dates(){
		$this->last_post_time = get('user')->date_format($this->last_post_time,true);
	}
	
	public function get($field){
		if($field == 'last_thread_link'){
			return $this->forum_last_thread_link();
		}
		elseif(isset($this->$field)){
			return $this->$field;
		}
		
		return false;
	}
	
	public function forum_last_thread_link(){
		return SITE_URL.'thread.php?id='.$this->the_forum['last_thread_id'];
	}
}
?>