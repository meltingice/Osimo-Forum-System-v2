<?
class OsimoTheme extends OsimoModule{
	public $page_type;
	protected $theme,$title;
	private $theme_path,$theme_file;
	private $css,$js;
	public $classes;
	
	function OsimoTheme($options=false){
		parent::OsimoModule();
		$this->defaults = array(
			"theme"=>"default",
			"title"=>"Osimo Forum System"
		);
		
		$this->parseOptions($options);
		$this->init();
	}
	
	private function init(){
		$this->css = array();
		$this->js = array();
		
		if(isset(get('osimo')->config['current_theme'])){
			$this->theme = get('osimo')->config['current_theme'];
		}
		
		$this->theme_path = ABS_THEMES.$this->theme.'/';
		define('ABS_THEME',$this->theme_path);
		define('URL_THEME',URL_THEMES.$this->theme.'/');
		
		$this->add_javascript(URL_JS.'jquery/jquery.js',false);
		$this->add_javascript(URL_JS.'jquery/jquery-ui.js',false);
		$this->add_javascript(URL_JS.'osimo_editor/osimo_editor.js',false);
		if(!get('debug')->disabled()){
			$this->add_javascript(URL_JS.'OsimoDebug.js',false);
		}
		$this->add_javascript(URL_JS.'OsimoJS.js',false);
		$this->add_javascript(URL_JS.'OsimoAjax.js',false);
		$this->add_javascript(URL_JS.'OsimoUI.js',false);
		$this->add_javascript(URL_JS.'OsimoModal.js',false);
		
		$this->add_stylesheet(URL_DEFAULT_CONTENT.'css/styles.css',false);
		$this->add_stylesheet(URL_JS.'jquery/css/jquery-ui.css',false);
	}
	
	/* Theme loading functions */
	public function load($file){
		if(!is_file($this->theme_path."$file.php")){
			get('debug')->error("OsimoTheme: unable to locate file '$file'",__LINE__,__FUNCTION__,__FILE__,true);
			return false;
		}
		
		$this->auto_set_page_type($file);
		get('user')->update_user_stats();
		
		$this->theme_file = $this->theme_path."/$file.php";
		include($this->theme_file);
	}
	
	public function include_theme_file($file,$echo=true){
		$osimo = $this->osimo;
		if(!is_file($this->theme_path.$file)){
			$this->osimo->debug->error("OsimoTheme: unable to locate file '$file'",__LINE__,__FUNCTION__,__FILE__,true);
			return false;
		}
		
		if($echo){
			include($this->theme_path.$file);
			return true;
		}
		else{
			return $this->include_contents($this->theme_path.$file);
		}
	}
	
	public function include_header($echo=true){
		$this->include_theme_file('header.php',$echo);
	}
	
	public function include_footer($echo=true){
		$this->include_theme_file('footer.php',$echo);
		get('debug')->scriptEnd();
		get('debug')->output_log();
	}

	/* Theme altering functions */

	public function set_title($title){
		$this->title = $title;
		
		return $this;
	}
	
	public function get_title(){
		if(!isset($this->title)){
			$this->auto_title();
		}
		
		return $this->title;
	}
	
	public function auto_title(){
		if($this->page_type == 'index'){
			$this->set_title(OS_SITE_TITLE);
		}
		elseif($this->page_type == 'forum'){
			$this->set_title(OS_SITE_TITLE);
		}
		
		return $this;
	}
	
	public function site_title($echo=true){
		if($echo){ echo OS_SITE_TITLE; } else{ return OS_SITE_TITLE; }
	}
	
	public function site_description($echo=true){
		if($echo){ echo OS_SITE_DESC; } else { return OS_SITE_DESC; }
	}
	
	public function add_stylesheet($url,$is_theme=true){
		if($is_theme){
			$url = URL_THEME.$url;
		}
		
		if(!in_array($url,$this->css)){
			$this->css[] = $url;
		}
		
		return $this;
	}
	
	public function add_javascript($url,$is_theme=true){
		if($is_theme){
			$url = URL_THEME.$url;
		}
		
		if(!in_array($url,$this->js)){
			$this->js[] = $url;
		}
		
		return $this;
	}
	
	public function get_header($echo = true){
		if(!$this->title){ $this->auto_title(); }
		
		$html = "<title>".$this->title." - Powered by Osimo</title>\n";
		$html .= "<meta http-equiv=\"x-ua-compatible\" content=\"IE=8\">\n";
		foreach($this->js as $js){
			$html .= "<script src=\"".$js."\"></script>\n";
		}
		foreach($this->css as $css){
			$html .= "<link rel=\"stylesheet\" href=\"".$css."\" type=\"text/css\" media=\"screen\" />\n";
		}
		
		$html .= "
		<script type='text/javascript'>
			var osimo = new OsimoJS({
				'debug' : true
			});
		</script>";

		if($echo){ echo $html; }
		return $html;
	}
	
	public function set_page_type($type){
		$this->page_type = strtolower(str_replace(" ","_",$type));
	}
	
	private function auto_set_page_type($page){
		$page = strtolower(str_replace(" ","_",$page));
		$types = array(
			'index','forum',
			'thread','profile',
			'login'
		);
		
		if(in_array($page,$types)){
			$this->page_type = $page;
		}
		else{
			$this->page_type = 'other';
		}
	}
	
	public function include_postbox(){
		if(file_exists(ABS_THEME.'postbox.php')){
			include(ABS_THEME.'postbox.php');
		}
		else{
			include(ABS_DEFAULT_CONTENT.'postbox.php');
		}
	}
	
	/* Utility Functions */
	public function is_index(){
		if($this->page_type == 'index'){ return true; }
		return false;
	}
	
	public function is_forum(){
		if($this->page_type == 'forum'){ return true; }
		return false;
	}
	
	public function is_thread(){
		if($this->page_type == 'thread'){ return true; }
		return false;
	}
	
	public function is_ajax_capable($page_type=false){
		if($page_type == 'thread' || $this->page_type == 'thread'){
			return file_exists(ABS_THEME.'single_post.php');
		}
		elseif($page_type == 'forum' || $this->page_type == 'forum'){
			return file_exists(ABS_THEME.'single_thread.php');
		}
		
		return false;
	}
	
	public function login_username_css_id($echo=true){
		$var = "osimo_username";
		if($echo){ echo $var; } else { return $var; }
	}
	
	public function login_username_name($echo=true){
		$var = "osimo_username";
		if($echo){ echo $var; } else { return $var; }
	}
	
	public function login_password_css_id($echo=true){
		$var = "osimo_password";
		if($echo){ echo $var; } else { return $var; }
	}

	public function login_password_name($echo=true){
		$var = "osimo_password";
		if($echo){ echo $var; } else { return $var; }
	}
	
	public function login_action_url($echo=true){
		$var = 'os-includes/login.php';
		if($echo){ echo $var; } else { return $var; }
	}
	
	public function osimo_editor($options=false,$pretext='',$css_id='OsimoPostbox'){
		echo '<textarea id="#'.$css_id.'">'.$pretext.'</textarea>';
		echo "
			<script type=\"text/javascript\">
				$(window).ready(function(){
					$('#OsimoPostbox').osimoeditor(
						".(is_array($options) ? json_encode($options) : '')."
					);
				});
			</script>
		";
	}
	
	public function post_submit(){
		echo "osimo.submitPost()";
	}
	
	public function post_preview(){
		echo "osimo.previewPost()";
	}
	
	public function num_pages(){
		echo '<span class="OsimoNumPages">'.get('data')->num_pages().'</span>';
	}
	
	public function preset_pagination($before=' ', $after=' ', $page_type=false, $page=false){
		if(!$page){
			isset(get('osimo')->GET['page']) ? $page = get('osimo')->GET['page'] : $page = 1;
		}
		
		$pages = $this->pagination_numbers($page);
		
		if(!$page_type && isset($this->page_type)){
			$page_type = $this->page_type;
		}
		
		$ajax = $this->is_ajax_capable($page_type);
		
		echo "<span class='OsimoPaginationWrap'>";
		if($pages['first']){
			echo '<span class="OsimoPagination" onclick="';
			if($ajax){
				echo 'osimo.loadPage(1)';
			}
			else{
				echo "window.location.href='".$page_type.".php?id=".get('osimo')->GET['id']."&page=1'";
			}
			echo '">First</span>'.$after.$before;
		}
		for($i = $pages['start']; $i <= $pages['end']; $i++){
			if($i != $pages['start']){ echo $before; }
			echo '<span class="OsimoPagination OsimoPaginationPage_'.$i;
			if($i == $page){ echo ' OsimoPaginationActivePage'; }
			echo '" onclick="';
			if($ajax){
				echo 'osimo.loadPage('.$i.')';
			}
			else{
				echo "window.location.href='".$page_type.".php?id=".get('osimo')->GET['id']."&page=$i'";
			}
			echo '">'.$i.'</span>';
			if($i != $pages['end']){ echo $after; }
		}
		if($pages['last']){ echo $after.$before.'<span class="OsimoPagination" onclick="osimo.loadPage('.$pages['num'].')">Last</span>'; }
		echo '</span>';
	}
	
	public function pagination_numbers($type=false,$id=false,$page=false){
		if(!$page){
			isset(get('osimo')->GET['page']) ? $page = get('osimo')->GET['page'] : $page = 1;
		}
		
		$pages['num'] = get('data')->num_pages($type,$id);
		
		if($pages['num'] <= 5){
			$pages['start'] = 1;
			$pages['end'] = $pages['num'];
			$pages['first'] = false; $pages['last'] = false;
		}
		else{
			if($page <= 3){
				$pages['start'] = 1;
				$pages['end'] = 5;
				$pages['first'] = false;
				$pages['last'] = true;
			}
			elseif($pages['num'] - $page <= 2){
				$pages['start'] = $pages['num'] - 5 + 1;
				$pages['end'] = $pages['num'];
				$pages['first'] = true;
				$pages['last'] = false;
			}
			else{
				$pages['start'] = $page - 2;
				$pages['end'] = $page + 2;
				$pages['first'] = true;
				$pages['last'] = true;
			}
		}
		
		return $pages;
	}
	
	public function include_contents($filename){
		$osimo = get('osimo'); //pull the osimo object into this scope just in case

		if (is_file($filename)) {
			ob_start();
			include $filename;
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
    	}
    	
    	return false;
	}
}
?>