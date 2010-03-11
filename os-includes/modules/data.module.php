<?
/*
 * Very important class
 * Data retrieval for entire forum used in themes
 * Very 'stateful' file that provides a large data abstraction
 * layer for theme developers
 */
class OsimoData extends OsimoModule{
	private $category_tree,$forum_tree,$thread_tree,$post_tree;
	private $the_category,$the_forum,$the_thread,$the_post;
	private $post_users,$post_user;
	
	function OsimoData(){
		parent::OsimoModule();
		$this->defaults = array(
			
		);
		
		$this->init();
	}
	
	private function init(){
		get('debug')->register('OsimoData',array('events'));
	}
	
	public function load_forum_list($args=false){
		if(!$args){ //determine what to load automatically
			if(get('theme')->is_index()){
				$args = 'parent_forum=0';
			}
			elseif(get('theme')->is_forum()){
				$args = 'parent_forum='.get('osimo')->GET['id'];
			}
			else{
				get('debug')->error("OsimoData: unable to automatically determine parameter",__LINE__,__FUNCTION__,__FILE__,true);
			}
		}
		
		$allowed = array(
			'parent_forum'=>'numeric'
		);
		
		$args = Osimo::validateOQLArgs($args,$allowed,true);
		$result = get('db')->select('*')->from('forums')->where(implode(' AND ',$args))->order_by('title','ASC')->rows();
		if($result){
			foreach($result as $data){
				$this->forum_tree[$data['category']][$data['id']] = $data;
			}
		}
	}
	
	public function load_thread_list($args=false){
		if(!$args){
			if(get('theme')->is_forum()){
				$args = 'forum='.get('osimo')->GET['id'];
			}
			else{
				get('debug')->error("OsimoData: unable to automatically determine parameter",__LINE__,__FUNCTION__,__FILE__,true);
			}
		}
		
		$allowed = array(
			'forum'=>'numeric'
		);
		
		$args = Osimo::validateOQLArgs($args,$allowed,true);
		isset(get('osimo')->config['thread_num_per_page']) ? $num = get('osimo')->config['thread_num_per_page'] : $num = 20;
		isset(get('osimo')->GET['page']) ? $page = get('osimo')->GET['page'] : $page = 1;
		
		$limit = get('osimo')->getPageLimits($page,$num);
		$result = get('db')->select('*')->from('threads')->where(implode(' AND ',$args))->order_by('last_post_time','DESC')->limit($limit['start'],$limit['num'])->rows();
		if($result){
			foreach($result as $data){
				$this->thread_tree[$data['id']] = $data;
			}
		}
	}
	
	public function load_post_list($args=false,$page=false){
		if(!$args){
			if(get('theme')->is_thread()){
				$args = 'thread='.get('osimo')->GET['id'];
			}
			else{
				get('debug')->error("OsimoData: unable to automatically determine parameter",__LINE__,__FUNCTION__,__FILE__,true);
			}
		}

		$allowed = array(
			'thread'=>'numeric'
		);
		
		$args = Osimo::validateOQLArgs($args,$allowed,true);

		isset(get('osimo')->config['post_num_per_page']) ? $num = get('osimo')->config['post_num_per_page'] : $num = 10;
		if(!$page){
			isset(get('osimo')->GET['page']) ? $page = get('osimo')->GET['page'] : $page = 1;
		}
		
		$limit = get('osimo')->getPageLimits($page,$num);
		$result = get('db')->select('*')->from('posts')->where(implode(' AND ',$args))->order_by('id','ASC')->limit($limit['start'],$limit['num'])->rows();
		if($result){
			foreach($result as $data){
				$this->post_tree[$data['id']] = $data;
			}
			
			$result = get('db')->select('*')->from('threads')->where('id=%d',get('osimo')->GET['id'])->limit(1)->row();
			if($result){
				$this->the_thread = get('osimo')->thread($result);
			}
		}
		elseif(get('theme')->page_type != 'index'){
			header('Location: index.php'); exit;
		}
	}
	
	private function load_category_info($catID){
		return get('db')->select('*')->from('categories')->where("id=%d",$catID)->row(true);
	}
	
	public function do_standard_loop($type=false,$id=false,$page=false,$echo=true){
		if($type == false){
			if(isset(get('theme')->page_type)){
				$type = get('theme')->page_type;
			}
			else{
				return false;
			}
		}

		if($id == false){
			if(is_numeric(get('osimo')->GET['id'])){
				$id = get('osimo')->GET['id'];
			}
			else{
				return false;
			}
		}
		
		$html = '';
		
		if($type == 'thread'){
			$this->load_post_list("thread=$id",$page);
			if($echo){ echo '<div id="OsimoPosts">'; } else { $html .= '<div id="OsimoPosts">'; }
			if($this->are_posts()){ while($this->has_posts()){
				if($echo){
					include(ABS_THEME.'single_post.php');
				}
				else{
					$html .= get('theme')->include_contents(ABS_THEME.'single_post.php');
				}
			} }
			if($echo){ echo '</div>'; } else { $html .= '</div>'; }
		}
		elseif($type == 'forum'){
			
		}
		else{
			return false;
		}
		
		if($echo){
			return true;
		}
		else{
			return $html;
		}
	}
	
	public function are_categories(){
		return (count($this->forum_tree) > 0);
	}
	
	public function are_forums(){
		return (count(reset($this->category_tree)) > 0);
	}
	
	public function are_threads(){
		return (count($this->thread_tree) > 0);
	}
	
	public function are_posts(){
		return (count($this->post_tree) > 0);
	}
	
	/* Sets the current category and returns false when categories are depleted */
	public function has_categories(){
		$this->category_tree = array_shift($this->forum_tree);
		if(!is_array($this->category_tree)){ return false; }
		$temp = reset($this->category_tree);
		$catID = $temp['category'];
		$this->the_category = $this->load_category_info($catID);

		return is_array($this->the_category);
	}
	
	/* Sets the current forum and returns false when forums are depleted */
	public function has_forums(){
		$forum_info = array_shift($this->category_tree);
		if(is_array($forum_info)){
			$this->the_forum = get('osimo')->forum($forum_info);
			if(is_object($this->the_forum)){
				return true;
			}
		}
		
		return NULL;
	}
	
	public function has_threads(){
		$thread_info = array_shift($this->thread_tree);
		if(is_array($thread_info)){
			$this->the_thread = get('osimo')->thread($thread_info);
			if(is_object($this->the_thread)){
				return true;
			}
		}

		return NULL;
	}
	
	public function has_posts(){
		$post_info = array_shift($this->post_tree);		
		if(is_array($post_info)){
			$this->the_post = get('osimo')->post($post_info);
			if(is_object($this->the_post)){
				$this->set_post_user();
				return true;
			}
		}
		
		return NULL;
	}
	
	private function set_post_user(){
		if(!isset($this->post_users[$this->the_post->get('poster_id')])){
			$this->post_users[$this->the_post->get('poster_id')] = new OsimoUser($this->the_post->get('poster_id'),false);
		}
		
		$this->post_user = $this->post_users[$this->the_post->get('poster_id')];
	}
	
	/* Data output functions */
	public function the_category($field=false,$echo=true){
		if(!$field){ return $this->the_category; }
		
		if(isset($this->the_category[$field])){
			if($echo){ echo $this->the_category[$field]; } else { return $this->the_category[$field]; }
		}
		
		return NULL;
	}
	
	public function the_forum($field=false,$echo=true){
		if(!is_object($this->the_forum)){ return false; }
		if(!$field){ return $this->the_forum; }
		if($echo){ echo $this->the_forum->get($field); } else { return $this->the_forum->get($field); }
		
		return NULL;
	}
	
	public function the_thread($field=false,$echo=true){
		if(!is_object($this->the_thread)){ return false; }
		if(!$field){ return $this->the_thread; }
		if($echo){ echo $this->the_thread->get($field); } else { return $this->the_thread->get($field); }
		
		return NULL;
	}
	
	public function the_post($field=false,$echo=true){
		if(!is_object($this->the_post)){ return false; }
		if($field == 'poster_link'){
			if($echo){ echo $this->post_user->profile_link(); return true; } else { return $this->post_user->profile_link(); }
		}
		elseif(!$field){ return $this->the_post; }
		
		if($echo){ echo $this->the_post->get($field); } else { return $this->the_post->get($field); }
		
		return NULL;
	}
	
	public function post_user($field=false,$echo=true){
		if(!$field){ return $this->post_user; }
		
		if(isset($this->post_user->$field)){
			if($echo){ echo $this->post_user->$field; } else { return $this->post_user->$field; }
		}
		
		return NULL;
	}
	
	public function thread_title($echo=true){
		return $this->the_thread('title',$echo);
	}
	
	public function thread_description($echo=true){
		return $this->the_thread('description',$echo);
	}
	
	public function the_forum_link(){
		$name = $this->the_forum('title',false);
		$id = $this->the_forum('id',false);
		echo '<a href="'.SITE_URL.'forum.php?id='.$id.'">'.$name.'</a>';
	}
	
	public function the_thread_link(){
		$name = $this->the_thread('title',false);
		$id = $this->the_thread('id',false);
		echo '<a href="'.SITE_URL.'thread.php?id='.$id.'">'.$name.'</a>';
	}
	
	public function the_avatar_url($echo=true){
		if($echo){ echo $this->post_user->avatar(); } else { return $this->post_user->avatar(); }
	}
	
	public function the_post_css_id($echo=true){
		if($echo){ echo "post_".$this->the_post->get('id'); } else { return "post_".$this->the_post->get('id'); }
	}
	
	public function the_formatted_post($echo=true){
		if($echo){ echo $this->the_post->parse_post(); } else { return $this->the_post->parse_post(); }
	}
	
	public function num_pages($type=false,$id=false){
		if(!isset($this->num_pages)){
			if($type == 'thread' || get('theme')->page_type == 'thread'){
				if($id == false){ $id = get('osimo')->GET['id']; }
				$posts = get('db')->select('COUNT(*)')->from('posts')->where('thread=%d',$id)->limit(1)->cell();
				isset(get('osimo')->config['post_num_per_page']) ? $num = get('osimo')->config['post_num_per_page'] : $num = 10;
				$this->num_pages = ceil($posts / $num);
			}
		}
		
		return $this->num_pages;
	}
	
	public function breadcrumb_trail($sep=' &raquo; ',$echo=true){
		$trail = $this->build_breadcrumb_trail();
		if(!$trail){ return false; }
		
		$output = implode($sep,$trail);
		if($echo){ echo $output; }
		else{ return $output; }
	}
	
	private function build_breadcrumb_trail(){
		get('debug')->logMsg('OsimoData','events','Beginning breadcrumb trail builder...');
		
		if(get('theme')->is_index()){
			return get('theme')->site_title(false);
		}
		elseif(!get('theme')->is_forum() && !get('theme')->is_thread()){
			get('debug')->logError('OsimoData','events','Attempting to build breadcrumb trail on non-applicable page, returning false');
			return false;
		}
		
		$trail = array();
		if(get('theme')->is_thread()){
			$trail[] = $this->thread_title(false);
			$start_id = $this->the_thread->forum;
		}
		else{
			$start_id = get('osimo')->GET['id'];
		}		

		$result = get('db')->select('title,parent_forum')->from('forums')->where('id=%d',$start_id)->row();
		$trail[] = '<a href="forum.php?id='.$start_id.'">'.$result['title'].'</a>';
		while($result != false & $result['parent_forum'] != 0){
			$cur_id = $result['parent_forum'];
			$result = get('db')->select('title,parent_forum')->from('forums')->where('id=%d',$cur_id)->row();
			$trail[] = '<a href="forum.php?id='.$cur_id.'">'.$result['title'].'</a>';
		}
		
		$trail[] = '<a href="index.php">'.get('theme')->site_title(false).'</a>';
		$trail = array_reverse($trail);
		get('debug')->logMsg('OsimoData','events',"Breadcrumb trail built, results: \n".print_r($trail,true));
		return $trail;
	}
}
?>