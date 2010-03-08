<?
require("../config.php");
require("ajax.class.php");

class OsimoAjaxPost extends OsimoAjax{
	function OsimoAjaxPost(){
		parent::OsimoAjax();
		$this->register(
			array(
				"submitPost"=>"submit_post"
			)
		);
	}
	
	protected function submit_post(){
		$content = get('osimo')->POST['content'];
		$this->json_return($content);
	}
}

new OsimoAjaxPost();
?>