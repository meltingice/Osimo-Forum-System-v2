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
		$this->post_time = get('user')->date_format($this->post_time,true);
		$this->last_edit_time = get('user')->date_format($this->last_edit_time,true);
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