<?php namespace ProcessWire;

/**
 * DplusResponse
 * Basic class for Storing Dplus Request Response values
 *
 * @author Paul Gomez
 *
 * @property bool    $success            Did the function succeed?
 * @property bool    $error              Was there an error?
 * @property bool    $message            Error Message / Success Message
 *
 */
class DplusResponse extends WireData {

	public function __construct() {
		$this->success = false;
		$this->error = false;
		$this->message = '';
	}

	public function has_success() {
		return boolval($this->success);
	}

	public function has_error() {
		return boolval($this->error);
	}

	public function set_success(bool $success) {
		$this->success = $success;
	}

	public function set_error(bool $error) {
		$this->error = $error;
	}

	public function set_message($message) {
		$this->message = $message;
	}

	/**
	 * Return Error Response with Provided Message
	 * @param  string $message    Error Message
	 * @return DplusResponse
	 */
	public static function create_error($message) {
		$response = new DplusResponse();
		$response->set_error(true);
		$response->set_message($message);
		return $response;
	}
}
