<?php namespace Dplus\Ecomm\Cart;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dpluso Model
use CartQuery as CartItemQuery, Cart as CartItem;
// ProcessWire
use ProcessWire\WireData;


class Items extends WireData {
	private static $instance;

	public static function getInstance($sessionID = '') {
		if (empty(self::$instance)) {
			$instance = new self();
			if ($sessionID != '') {
				$instance->setSessionID($sessionID);
			}
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function __construct() {
		$this->sessionID = session_id();
	}

	public function setSessionID($sessionID) {
		$this->sessionID = $sessionID;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return CartItemQuery
	 * @return CartItemQuery
	 */
	public function query() {
		$q = CartItemQuery::create();
		$q->filterBySessionid(session_id());
		return $q;
	}

	/**
	 * Return Query for CartItems
	 * @return CartItemQuery
	 */
	public function queryItems() {
		$q = $this->query();
		$q->filterByItemid('', Criteria::ALT_NOT_EQUAL);
		return $q;
	}

/* =============================================================
	Get Functions
============================================================= */
	/**
	 * Return Items
	 * @return CartItem[]|ObjectCollection
	 */
	public function items() {
		$q = $this->queryItems();
		return $q->find();
	}

	/**
	 * Return the number of Items in cart
	 * @return int
	 */
	public function count() {
		$q = $this->queryItems();
		return $q->count();
	}

	/**
	 * Return that Item Exists in cart
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function exists($itemID) {
		$q = $this->queryItems();
		$q->filterByItemid($itemID);
		return boolval($q->count());
	}
}
