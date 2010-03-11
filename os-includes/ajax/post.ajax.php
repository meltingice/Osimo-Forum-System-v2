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
		if(!get('user')->is_logged_in()){
			$this->json_error("You must be logged in to post!");
		}
		elseif(get('osimo')->POST['content'] == ''){
			$this->json_error("You cannot submit a blank post.");
		}
		
		$data = array(
			"thread"=>get('osimo')->POST['threadID'],
			"body"=>get('osimo')->POST['content'],
			"poster_id"=>get('user')->id
		);
		
		$result = get('osimo')->post($data)->create($post);
		if($result){
			$loc = $post->location();
			if(get('theme')->is_ajax_capable('thread')){
				$html = get('data')->do_standard_loop('thread',$loc['thread'],$loc['page'],false);
				$pagination = get('theme')->pagination_numbers('thread',$loc['thread'],$loc['page']);
				$this->json_return(array("html"=>$html,"location"=>$loc,"pagination"=>$pagination));
			}
			else{
				$this->json_return(array("refresh"=>true,"location"=>$loc));
			}
		}
		else{
			$this->json_error("There was an error creating your post, please try again.");
		}
	}
}

new OsimoAjaxPost();
?>