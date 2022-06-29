<?php namespace Dplus\Ecomm\Order;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

class Edit extends WireData {
	private static $instance;

	public static function instance($sessionID = '') {
		if (empty(self::$instance)) {
			$instance = new self($sessionID);
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function __construct($sessionID = '') {
		$this->sessionID = $sessionID ? $sessionID : session_id();
		$this->header = new Edit\Header($this->sessionID);
		$this->items  = new Edit\Items($this->sessionID);
		$this->allocatedLots  = new Edit\AllocatedLots($this->sessionID);
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Take Input and process action
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if (empty($values->action)) {
			return false;
		}

		if (empty($values->ordn)) {
			return false;
		}

		switch ($values->text('action')) {
			case 'update-order':
			case 'exit-order':
				$this->header->processInput($input);
				break;
			case 'update-item-qty':
			case 'delete-item':
			case 'add-item':
				$this->items->processInput($input);
				break;
			case 'update-lot-qty':
			case 'delete-lot':
			case 'add-lot':
				$this->allocatedLots->processInput($input);
				break;
			case 'update-notes':
			case 'delete-notes':
				$qnotes = $this->wire('modules')->get('QnotesSalesOrder');
				$qnotes->process_input($input);
				break;
		}
	}

/* =============================================================
	Response Functions
============================================================= */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('order', 'response', $response);
	}

	public function getResponse() {
		return $this->wire('session')->getFor('order', 'response');
	}

	public function deleteResponse() {
		return $this->wire('session')->removeFor('order', 'response');
	}
}
