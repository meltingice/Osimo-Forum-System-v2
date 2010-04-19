<?php

/**
 * Contains all interaction related to threads.
 *
 * @author Ryan LeFevre
 */
class OsimoThread {

	/**
	 * Class constructor.
	 *
	 * @param Array $info
	 *		All of the information pertaining to a particular thread.
	 */
	function OsimoThread($info) {
		if (is_array($info)) {
			foreach ($info as $key=>$val) {
				$this->$key = $val;
			}

			$this->format_dates();
		}
	}

	private function format_dates() {
		if(isset($this->original_post_time)) {
			$this->original_post_time = get('user')->date_format($this->original_post_time, true);
		}
		
		if(isset($this->last_post_time)) {
			$this->last_post_time = get('user')->date_format($this->last_post_time, true);
		}
	}

	/**
	 * Retrieves a single piece of information about the
	 * currently loaded thread.
	 *
	 * @param String $field
	 * @return The data specified by $field.
	 */
	public function get($field) {
		if ($field == 'original_poster_link') {
			return OsimoUser::get_profile_link($this->original_poster_id, $this->original_poster);
		}
		elseif (isset($this->$field)) {
			return $this->$field;
		}

		return false;
	}
	
	public function create($post, &$thread) {
		$this->original_post_time = OsimoDB::formatDateForDB();
		$this->title = get('db')->escape($this->title);
		$this->desc = get('db')->escape($this->description);
		$this->original_poster = get('db')->escape($this->original_poster);
		if(!is_numeric($this->forum) || !is_numeric($this->original_poster_id)) {
			return false;
		}

		$query = "
			INSERT INTO threads (
				forum,
				title,
				description,
				original_poster,
				original_poster_id,
				original_post_time
			) VALUES (
				'".$this->forum."',
				'".$this->title."',
				'".$this->description."',
				'".$this->original_poster."',
				'".$this->original_poster_id."',
				'".$this->original_post_time."'
			)";
			
		$result = get('db')->query($query)->insert($threadID);
		if($result) {
			$this->id = $threadID;
			$post->thread = $threadID;
			$post->poster_id = $this->original_poster_id;
			$result2 = $post->create($thePost);
			if($result2) {
				get('db')->
				update('forums')->
				set(
					array(
						'threads'=>'threads+1',
						'last_thread_id'=>$this->id,
						'last_thread_title'=>$this->title,
						'last_poster'=>"'".$this->original_poster."'",
						'last_poster_id'=>"'".$this->original_poster_id."'",
						'last_post_time'=>"'".OsimoDB::formatDateForDB()."'"
					)
				)->
				where('id=%d', $this->forum)->
				limit(1)->
				update();
				
				$thread = $this;
				return true;
			}
			else{
				// rollback the creation of this thread to prevent empty threads from lying around.
				$this->delete();
			}
		}
		
		return false;
	}
	
	/**
	 * Delete the currently loaded thread and update all references.
	 */
	public function delete() {
		get('db')->delete()->from('posts')->where('thread=%d',$this->id)->delete($numPosts);
		get('db')->delete()->from('threads')->where('id=%d',$this->id)->limit(1)->delete($numThreads);
		
		$data = get('db')->
			select('id')->
			from('threads')->
			where('forum = %d AND id != %d', $this->forum, $this->id)->
			order_by('last_post_time','DESC')->
			limit(1)->
			row();

		get('db')->
		update('forums')->
		set(
			array(
				'threads'=>'threads-1',
				'posts'=>'posts-'.$numPosts,
				'last_thread_id'=>$data['id'],
				'last_thread_title'=>$data['title'],
				'last_poster'=>$data['last_poster'],
				'last_poster_id'=>$data['last_poster_id'],
				'last_post_time'=>$data['last_post_time']
			)
		)->
		where('id=%d', $this->forum)->
		limit(1)->
		update();
	}
}
?>