<?php namespace Dplus\Ecomm;

use ProcessWire\WireData;

/**
 * Response
 * Basic class for Storing Dplus Request Response values
 *
 * @author Paul Gomez
 *
 * @property bool    $success            Did the function succeed?
 * @property bool    $error              Was there an error?
 * @property bool    $message            Error Message / Success Message
 *
 */
class Response extends WireData {

	public function __construct() {
		$this->success = false;
		$this->error = false;
		$this->message = '';
	}

	public function hasSuccess() {
		return boolval($this->success);
	}

	public function hasError() {
		return boolval($this->error);
	}

	public function setSuccess(bool $success) {
		$this->success = $success;
		return $this;
	}

	public function setError(bool $error) {
		$this->error = $error;
		return $this;
	}

	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}

	/**
	 * Return Error Response with Provided Message
	 * @param  string $message    Error Message
	 * @return Response
	 */
	public static function createError($message) {
		$response = new Response();
		$response->setError(true);
		$response->setMessage($message);
		return $response;
	}

	/**
	 * Return Success Response with Provided Message
	 * @param  string $message    Error Message
	 * @return Response
	 */
	public static function createSuccess($message) {
		$response = new Response();
		$response->setError(false);
		$response->setSuccess(true);
		$response->setMessage($message);
		return $response;
	}
}
