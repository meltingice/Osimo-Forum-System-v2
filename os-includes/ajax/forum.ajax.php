<?
require("../config.php");
require("ajax.class.php");

class OsimoAjaxForum extends OsimoAjax{
	function OsimoAjaxForum(){
		parent::OsimoAjax();
		$this->register(
			array(
				"loadPage"=>"load_page"
			)
		);
	}
	
	protected function load_page(){
		$forum = get('osimo')->POST['forum'];
		$page = get('osimo')->POST['page'];
		
		$html = get('data')->do_standard_loop('forum',$forum,$page,false);
		$pagination = get('theme')->pagination_numbers('forum',$forum,$page);
		$this->json_return(array("html"=>$html,"pagination"=>$pagination));
	}
}

new OsimoAjaxForum();
?>