<?php
/**
 * Main ajax class for Osimo that all other
 * ajax classes extend.
 *
 * @brief Main ajax class for Osimo.
 * @author Ryan LeFevre
 */
 
class OsimoAjax {
	protected $triggers;

	/**
	 * Class constructor
	 */
	function OsimoAjax() {
		$this->triggers = array();
		get('osimo')->ajax_mode = true;
	}

	/**
	 * Used to register all the ajax triggers for the
	 * current ajax class. A trigger is a variable
	 * set in Javascript and set over ajax so that PHP
	 * knows what function to execute to handle the incoming
	 * ajax data. All incoming POST variables are pulled into
	 * Osimo's class scope.
	 *
	 * @param Array $triggers
	 */
	protected function register($triggers) {
		if (is_array($triggers)) {
			$this->triggers = $triggers;

			if (isset($_POST['ajax_trigger']) && isset($triggers[$_POST['ajax_trigger']])) {
				$this->run_trigger($triggers[$_POST['ajax_trigger']]);
			}
		}
	}

	private function run_trigger($func) {
		foreach ($_POST as $var=>$POST) {
			get('osimo')->requirePOST($var, false, false);
		}

		$this->$func();
	}

	/**
	 * Simple function that echoes data in json format
	 * and halts script execution.
	 *
	 * @param mixed $data
	 */
	protected function json_return($data) {
		if (is_array($data)) {
			echo json_encode($data);
		}
		else {
			echo json_encode(array("data"=>$data));
		}

		exit;
	}

	/**
	 * Similar to json_return except the default
	 * index for an error is "error" instead of "data".
	 * This function also halts script execution.
	 *
	 * @param mixed $data
	 */
	protected function json_error($data) {
		if (is_array($data)) {
			echo json_encode($data);
		}
		else {
			echo json_encode(array("error"=>$data));
		}

		exit;
	}


}


?>