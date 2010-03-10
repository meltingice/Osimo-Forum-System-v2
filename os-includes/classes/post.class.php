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
		if(isset($this->post_time)){
			$this->post_time = get('user')->date_format($this->post_time,true);
		}
		if(isset($this->last_edit_time)){
			$this->last_edit_time = get('user')->date_format($this->last_edit_time,true);
		}
	}
	
	public function get($field){
		if(isset($this->$field)){
			return $this->$field;
		}
		
		return false;
	}
	
	public function parse_post(){
		return nl2br(get('bbparser')->parse($this->body));
	}
	
	public function location(){
		$ids = get('db')->select('id')->from('posts')->where('thread=%d',$this->thread)->order_by('id','ASC')->rows();

		$i = 0;		
		foreach($ids as $id){
			$i++;
			if($id['id'] == $this->id){
				break;
			}
		}
		
		isset(get('osimo')->config['post_num_per_page']) ? $num = get('osimo')->config['post_num_per_page'] : $num = 10;
		return array(
			"page"=>ceil($i / $num),
			"post"=>$this->id,
			"thread"=>$this->thread
		);
	}
	
	public function create(&$post){
		$this->post_time = OsimoDB::formatDateForDB();
		$this->body = get('db')->escape($this->body);
		if(!is_numeric($this->thread) || !is_numeric($this->poster_id)){
			return false;
		}
		
		$query = "
			INSERT INTO posts (
				thread,
				body,
				poster_id,
				post_time
			) VALUES (
				'".$this->thread."',
				'".$this->body."',
				'".$this->poster_id."',
				'".$this->post_time."'
			)";
		$result = get('db')->query($query)->insert($postID);
		if($result){
			$this->id = $postID;
			$post = $this;
			
			get('user')->increase_post_count();
			get('db')->
				update('threads')->
				set(
					array(
						'posts'=>'posts+1',
						'last_poster'=>"'".get('user')->username."'",
						'last_poster_id'=>"'".get('user')->id."'",
						'last_post_time'=>"'".get('db')->formatDateForDB()."'"
					)
				)->
				where('id=%d',$this->thread)->
				limit(1)->
				update();
				
			get('db')->
				update('forums')->
				set(
					array(
						'posts'=>'posts+1',
						'last_thread_id'=>$this->thread,
						'last_poster'=>"'".get('user')->username."'",
						'last_poster_id'=>"'".get('user')->id."'",
						'last_post_time'=>"'".get('db')->formatDateForDB()."'"
					)
				)->
				where('id=(SELECT forum FROM threads WHERE id=%d LIMIT 1)',$this->thread)->
				limit(1)->
				update();
				
			return true;
		}
		else{
			return false;
		}
	}
}
?>