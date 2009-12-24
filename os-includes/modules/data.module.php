<?
/*
 * Very important class
 * Data retrieval for entire forum used in themes
 * Very 'stateful' file that provides a large data abstraction
 * layer for theme developers
 */
class OsimoData extends OsimoModule{
	private $category_tree,$forum_tree;
	private $the_category,$the_forum;
	
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
	
	public function load_forum_list($args){
		$allowed = array(
			'parent_forum'=>'numeric'
		);
		
		$args = Osimo::validateOQLArgs($args,$allowed,true);
		$result = get('db')->select('*')->from('forums')->where(implode(' AND ',$args))->rows(); //this needs proper sorting
		if($result){
			foreach($result as $data){
				$this->forum_tree[$data['category']][$data['id']] = $data;
			}
		}
	}
	
	private function load_category_info($catID){
		return get('db')->select('*')->from('categories')->where("id=$catID")->row(true); // wtf are ints in sprintf?
	}
	
	public function are_categories(){
		return (count($this->forum_tree) > 0);
	}
	
	public function are_forums(){
		return (count(reset($this->category_tree)) > 0);
	}
	
	/* Sets the current category and returns false when categories are depleted */
	public function has_categories(){
		$this->category_tree = array_shift($this->forum_tree);
		if(!is_array($this->category_tree)){ return false; }
		
		$this->the_category = $this->load_category_info(reset(array_keys($this->category_tree)));
		return is_array($this->the_category);
	}
	
	/* Sets the current forum and returns false when forums are depleted */
	public function has_forums(){
		$this->the_forum = array_shift($this->category_tree);
		return is_array($this->the_forum);
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
}
?>