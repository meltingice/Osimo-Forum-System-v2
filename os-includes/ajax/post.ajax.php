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
			get('user')->increase_post_count();
			get('db')->update('threads')->set(array('posts'=>'posts+1'))->where('id=%d',$data['thread'])->limit(1)->update();
			get('db')->
				update('forums')->
				set(array('posts'=>'posts+1'))->
				where('id=(SELECT forum FROM threads WHERE id=%d LIMIT 1)',$data['thread'])->
				limit(1)->
				update();
		
			if(get('theme')->is_ajax_capable()){
				get('data')->do_standard_loop();
			}
			else{
				$this->json_return(array("refresh"=>true,"location"=>$post->location()));
			}
		}
		else{
			$this->json_error("There was an error creating your post, please try again.");
		}
	}
}

new OsimoAjaxPost();
?>