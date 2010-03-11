<?
require("../config.php");
require("ajax.class.php");

class OsimoAjaxThread extends OsimoAjax{
	function OsimoAjaxThread(){
		parent::OsimoAjax();
		$this->register(
			array(
				"loadPage"=>"load_page"
			)
		);
	}
	
	protected function load_page(){
		$thread = get('osimo')->POST['thread'];
		$page = get('osimo')->POST['page'];
		
		$html = get('data')->do_standard_loop('thread',$thread,$page,false);
		$pagination = get('theme')->pagination_numbers('thread',$thread,$page);
		$this->json_return(array("html"=>$html,"pagination"=>$pagination));
	}
}

new OsimoAjaxThread();
?>