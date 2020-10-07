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
		return $this;
	}

	public function set_error(bool $error) {
		$this->error = $error;
		return $this;
	}

	public function set_message($message) {
		$this->message = $message;
		return $this;
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

	/**
	 * Return Success Response with Provided Message
	 * @param  string $message    Error Message
	 * @return DplusResponse
	 */
	public static function create_success($message) {
		$response = new DplusResponse();
		$response->set_error(false);
		$response->set_success(true);
		$response->set_message($message);
		return $response;
	}
}
