<?php

/**
 * Contains all interaction related to threads.
 *
 * @author Ryan LeFevre
 */
class OsimoThread {

	/**
	 * Class constructor.
	 *
	 * @param Array $info
	 *		All of the information pertaining to a particular thread.
	 */
	function OsimoThread($info) {
		if (is_array($info)) {
			foreach ($info as $key=>$val) {
				$this->$key = $val;
			}

			$this->format_dates();
		}
	}

	private function format_dates() {
		$this->original_post_time = get('user')->date_format($this->original_post_time, true);
		$this->last_post_time = get('user')->date_format($this->last_post_time, true);
	}

	/**
	 * Retrieves a single piece of information about the
	 * currently loaded thread.
	 *
	 * @param String $field
	 * @return The data specified by $field.
	 */
	public function get($field) {
		if ($field == 'original_poster_link') {
			return OsimoUser::get_profile_link($this->original_poster_id, $this->original_poster);
		}
		elseif (isset($this->$field)) {
			return $this->$field;
		}

		return false;
	}
}
?>