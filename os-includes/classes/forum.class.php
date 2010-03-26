<?php

/**
 * Contains all interaction related to forums.
 *
 * @author Ryan LeFevre
 */
class OsimoForum {
	
	/**
	 * Class constructor.
	 *
	 * @param Array $info
	 *		All of the data pertaining to a particular forum.
	 */
	function OsimoForum($info) {
		if (is_array($info)) {
			foreach ($info as $key=>$val) {
				$this->$key = $val;
			}

			$this->format_dates();
		}
	}

	private function format_dates() {
		$this->last_post_time = get('user')->date_format($this->last_post_time, true);
	}

	/**
	 * Retrieves a single piece of information about the
	 * currently loaded forum.
	 *
	 * @param String $field
	 * @return The data specified by $field.
	 */
	public function get($field) {
		if ($field == 'last_thread_link') {
			return $this->forum_last_thread_link();
		}
		elseif (isset($this->$field)) {
			return $this->$field;
		}

		return false;
	}

	/**
	 * Formats a link to the last updated thread in this forum.
	 *
	 * @return A link to a thread
	 */
	public function forum_last_thread_link() {
		return SITE_URL.'thread.php?id='.$this->the_forum['last_thread_id'];
	}
}
?>