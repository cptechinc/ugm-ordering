<?php namespace Dplus\Ecomm\Items\Available;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup as LotInventory;
// Dplus Ecomm
use Dplus\Ecomm\Cart;

/**
 * Items
 * Handles checking Item availability based on Inventory + qty in cart
 * @property LotInventory $inventory  Lot Inventory Lookup
 */
class Items extends WireData {
	private static $instance;
	private $inventory;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function __construct() {
		$this->sessionID = session_id();
		$this->setInventory(LotInventory\ExcludePackBin::getInstance());
	}

	/**
	 * Set Lot Inventory Lookup
	 * @param LotInventory $inventory
	 */
	public function setInventory(LotInventory $inventory) {
		$this->inventory = $inventory;
		$this->inventory->setWhseID(1);
	}

	/**
	 * Return Lot Inventory Lookup
	 * @return LotInventory
	 */
	public function getInventory() {
		return $this->inventory;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Item ID Availability based on Inventory and Cart Lots
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function getAvailability($itemID) {
		$cart = Cart::getInstance();
		$qtyInventory = $this->inventory->getQtyByItemid($itemID);
		$qtyInCart    = $cart->items->qtyItemidAllSessionids($itemID);
		$available    = $qtyInventory - $qtyInCart;
		return $available >= 0 ? $available : 0;
	}
}
