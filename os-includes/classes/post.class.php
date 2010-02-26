<?
class OsimoPost{
	function OsimoPost($info){
		if(is_array($info)){
			foreach($info as $key=>$val){
				$this->$key = $val;
			}
			
			$this->format_dates();
		}
	}
	
	private function format_dates(){
		$this->post_time = date(get('user')->time_format,strtotime($this->post_time));
		$this->last_edit_time = date(get('user')->time_format,strtotime($this->last_edit_time));
	}
	
	public function get($field){
		if(isset($this->$field)){
			return $this->$field;
		}
		
		return false;
	}
	
	public function parse_post(){
		return get('bbparser')->parse($this->body);
	}
}
?>