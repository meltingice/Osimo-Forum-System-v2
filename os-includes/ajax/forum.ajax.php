<?php
require "../config.php";
require "ajax.class.php";

/**
 * All ajax functions relating to forums.
 *
 * @author Ryan LeFevre
 * @see OsimoAjax
 */
class OsimoAjaxForum extends OsimoAjax{

	/**
	 * Class constructor.
	 * Ajax triggers are set here.
	 */
	function OsimoAjaxForum() {
		parent::OsimoAjax();
		$this->register(
			array(
				"loadPage"=>"load_page"
			)
		);
	}

	/**
	 * Loads a forum page for ajax-enabled themes.
	 */
	protected function load_page() {
		$forum = get('osimo')->POST['forum'];
		$page = get('osimo')->POST['page'];

		$html = get('data')->do_standard_loop('forum', $forum, $page, false);
		$pagination = get('theme')->pagination_numbers('forum', $forum, $page);
		$this->json_return(array("html"=>$html, "pagination"=>$pagination));
	}


}

/**
 * Class is automatically instantiated so that
 * the ajax triggers set up in the constructor can 
 * be set and the proper one chosen for the incoming
 * data over ajax.
 */
new OsimoAjaxForum();
?>