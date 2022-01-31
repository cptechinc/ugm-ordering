<?php namespace Dplus\Ecomm\Items\Available;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
use SalesOrderDetailQuery, SalesOrderDetail;
use SalesOrder;
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

	/**
	 * Return Qty that is on Orders
	 * @param  string $itemID Item ID
	 * @return float
	 */
	public function getQtyOnOrder($itemID) {
		$q = SalesOrderDetailQuery::create();
		$q->useSalesOrderQuery()
			->filterByStatus([SalesOrder::STATUS_CODES['new'], SalesOrder::STATUS_CODES['picked']])
			->endUse();
		$q->filterByItemid($itemID);
		$q->withColumn('SUM('.SalesOrderDetail::aliasproperty('qty_ordered').')', 'qty');
		$q->select('qty');
		return floatval($q->findOne());
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Item ID Availability based on Inventory, Qty on Order, Qty in Carts
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function getAvailability($itemID) {
		$cart = Cart::getInstance();
		$qtyInventory = $this->inventory->getQtyByItemid($itemID);
		$qtyInCart    = $cart->items->qtyItemidAllSessionids($itemID);
		$qtyOnOrder   = $this->getQtyOnOrder($itemID);
		$available    = $qtyInventory - $qtyOnOrder - $qtyInCart;
		return $available >= 0 ? $available : 0;
	}
}
