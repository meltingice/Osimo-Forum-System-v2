<?php
require "../config.php";
require "ajax.class.php";

/**
 * All ajax functions relating to threads.
 *
 * @author Ryan LeFevre
 * @see OsimoAjax
 */
class OsimoAjaxThread extends OsimoAjax{

	/**
	 * Class constructor.
	 * Ajax triggers are set here.
	 */
	function OsimoAjaxThread() {
		parent::OsimoAjax();
		$this->register(
			array(
				"loadPage"=>"load_page",
				"createThread"=>"create_thread"
			)
		);
	}

	/**
	 * Loads a thread page for ajax-capable themes.
	 */
	protected function load_page() {
		$thread = get('osimo')->POST['thread'];
		$page = get('osimo')->POST['page'];

		$html = get('data')->do_standard_loop('thread', $thread, $page, false);
		$pagination = get('theme')->pagination_numbers('thread', $thread, $page);
		$this->json_return(array("html"=>$html, "pagination"=>$pagination));
	}

	/**
	 * Creates a new thread
	 */
	protected function create_thread() {
		$forum = get('osimo')->POST['forum'];
		$title = get('osimo')->POST['title'];
		$desc = get('osimo')->POST['desc'];
		$content = get('osimo')->POST['content'];
		
		if (!get('user')->is_logged_in()) {
			$this->json_error("You must be logged in to create a thread!");
		} elseif ($content == '') {
			$this->json_error("You cannot submit a blank post.");
		} elseif($title == '') {
			$this->json_error("You cannot create a thread with a blank title");
		}
		
		$threadData = array(
			"forum"=>$forum,
			"title"=>$title,
			"description"=>$desc,
			"original_poster"=>get('user')->username,
			"original_poster_id"=>get('user')->id
		);
		
		$postData = array(
			"body"=>$content
		);
		$post = get('osimo')->post($postData);
		
		$result = get('osimo')->thread($threadData)->create($post, $thread);
		if($result) {
			$this->json_return(array("thread_id"=>$thread->id));
		} else {
			$this->json_error("There was an error creating the thread, please try again!");
		}
	}
}

/**
 * Class is automatically instantiated so that
 * the ajax triggers set up in the constructor can 
 * be set and the proper one chosen for the incoming
 * data over ajax.
 */
new OsimoAjaxThread();
?>