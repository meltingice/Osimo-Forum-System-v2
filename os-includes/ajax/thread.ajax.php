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
				"loadPage"=>"load_page"
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


}

/**
 * Class is automatically instantiated so that
 * the ajax triggers set up in the constructor can 
 * be set and the proper one chosen for the incoming
 * data over ajax.
 */
new OsimoAjaxThread();
?>