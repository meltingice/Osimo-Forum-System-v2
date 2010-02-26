<?
class OsimoThread{
	function OsimoThread($info){
		if(is_array($info)){
			foreach($info as $key=>$val){
				$this->$key = $val;
			}
			
			$this->format_dates();
		}
	}
	
	private function format_dates(){
		$this->original_post_time = date(get('user')->time_format,strtotime($this->original_post_time));
		$this->last_post_time = date(get('user')->time_format,strtotime($this->last_post_time));
	}
	
	public function get($field){
		if($field == 'original_poster_link'){
			return OsimoUser::get_profile_link($this->original_poster_id,$this->original_poster);
		}
		elseif(isset($this->$field)){
			return $this->$field;
		}
		
		return false;
	}
	
	
}
?>