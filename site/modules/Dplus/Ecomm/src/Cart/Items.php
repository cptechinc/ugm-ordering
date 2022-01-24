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
		return CartItemQuery::create();
	}

	/**
	 * Return CartItemQuery
	 * @return CartItemQuery
	 */
	public function querySessionid() {
		$q = $this->query();
		$q->filterBySessionid($this->sessionID);
		return $q;
	}

	/**
	 * Return Query for CartItems
	 * @return CartItemQuery
	 */
	public function queryItems() {
		$q = $this->querySessionid();
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

	/**
	 * Return Item ID qty in the cart
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function qtyItemid($itemID) {
		$q = $this->queryItems();
		$q->filterByItemid($itemID);
		$q->select('qty');
		return intval($q->findOne());
	}

	/**
	 * Return Item ID Qty for All Session IDs
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function qtyItemidAllSessionids($itemID) {
		$q = $this->query();
		$q->select('qty');
		$q->filterByItemid($itemID);
		$q->withColumn('SUM(qty)', 'qty');
		return intval($q->findOne());
	}

/* =============================================================
	Delete Functions
============================================================= */
	/**
	 * Clear Cart
	 * @return bool
	 */
	public function clear() {
		$q = $this->querySessionid();
		return boolval($q->delete());
	}
}
