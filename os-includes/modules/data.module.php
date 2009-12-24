<?
/*
 * Very important class
 * Data retrieval for entire forum used in themes
 * Very 'stateful' file that provides a large data abstraction
 * layer for theme developers
 */
class OsimoData extends OsimoModule{
	private $category_tree,$forum_tree,$thread_tree;
	private $the_category,$the_forum,$the_thread;
	
	function OsimoData(){
		parent::OsimoModule();
		$this->defaults = array(
			
		);
		
		//$this->parseOptions($options);
		$this->init();
	}
	
	/* Does nothing right now... */
	private function init(){
		
	}
	
	public function load_forum_list($args=false){
		if(!$args){ //determine what to load automatically
			if(get('theme')->is_index()){
				$args = 'parent_forum=-1';
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
		$result = get('db')->select('*')->from('forums')->where(implode(' AND ',$args))->rows();
		if($result){
			foreach($result as $data){
				$this->forum_tree[$data['category']][$data['id']] = $data;
			}
		}
	}
	
	public function load_thread_list($args=false,$page=1){
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
		$limit = get('osimo')->getPageLimits($page,20);
		$result = get('db')->select('*')->from('threads')->where(implode(' AND ',$args))->limit($limit['start'],$limit['num'])->rows();
		if($result){
			foreach($result as $data){
				$this->thread_tree[$data['id']] = $data;
			}
		}
	}
	
	private function load_category_info($catID){
		return reset(get('db')->select('*')->from('categories')->where("id=%d",$catID)->row(true));
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
		$this->the_forum = array_shift($this->category_tree);
		return is_array($this->the_forum);
	}
	
	public function has_threads(){
		$this->the_thread = array_shift($this->thread_tree);
		return is_array($this->the_thread);
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
		if(!$field){ return $this->the_forum; }
		
		if(isset($this->the_forum[$field])){
			if($echo){ echo $this->the_forum[$field]; } else { return $this->the_forum[$field]; }
		}
		
		return NULL;
	}
	
	public function the_thread($field=false,$echo=true){
		if(!$field){ return $this->the_thread; }
		
		if(isset($this->the_thread[$field])){
			if($echo){ echo $this->the_thread[$field]; } else { return $this->the_thread[$field]; }
		}
		
		return NULL;
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
}
?>