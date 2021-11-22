<?php namespace Dplus\Ecomm;
// ProcessWire
use ProcessWire\WireData;


class Cart extends WireData {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function __construct() {
		$this->sessionID = session_id();
		$this->items = Cart\Items::getInstance();
		$this->items->setSessionid($this->sessionID);
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
}
