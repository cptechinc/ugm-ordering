<?php namespace Dplus\Ecomm\Items\Available;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup as LotInventory;
// Dplus Ecomm
use Dplus\Ecomm\Cart;

/**
 * Lots
 * Handles checking lot availability based on Inventory + qty in cart
 * @property LotInventory $inventory  Lot Inventory Lookup
 */
class Lots extends WireData {
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
	 * Return Lot #s, Qtys for Item ID
	 * @param  array|string $itemID Item ID
	 * @return array
	 */
	public function getLotsByItemid($itemID) {
		$lots = $this->inventory->getLotsByItemid($itemID);
		$data = [];

		foreach ($lots as $lot) {
			$data[] = array_merge($lot, ['available' => $this->getLotAvailability($lot['lot'])]);
		}
		unset($lots);
		return $data;
	}

	/**
	 * Return Lot Availability based on Inventory and Cart Lots
	 * @param  string $lotnbr Lot Number
	 * @return int
	 */
	public function getLotAvailability($lotnbr) {
		$cart = Cart::getInstance();
		$qtyInventory = $this->inventory->getQtyByLotserial($lotnbr);
		$qtyInCart    = $cart->lots->getLotQtyAllSessionids($lotnbr);
		$available    = $qtyInventory - $qtyInCart;
		return $available >= 0 ? $available : 0;
	}
}
