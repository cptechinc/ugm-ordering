<?php namespace Dplus\Ecomm\Items\Available;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
use SalesOrderDetailQuery, SalesOrderDetail;
use SalesOrder;
use WarehouseBinQuery, WarehouseBin;
use InvWhseLotQuery, InvWhseLot;
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

	/**
	 * Return the Qty found in QC Bins
	 * @param  string $itemID Item ID
	 * @return float
	 */
	public function getQtyInQcBins($itemID) {
		$colQty = InvWhseLot::aliasproperty('qty');

		$q = $this->inventory->queryWhseBins();
		$q->filterByBinid($this->getQcBinids());
		$q->filterByItemid($itemID);
		$q->addAsColumn('qty', "SUM($colQty)");
		$q->select('qty');
		return intval($q->findOne());
	}

	/**
	 * Return Binids with the Q (quality control) bin type
	 * @return array
	 */
	protected function getQcBinids() {
		$q = WarehouseBinQuery::create();
		$q->select(WarehouseBin::aliasproperty('from'));
		$q->filterByType('Q');
		return $q->find()->toArray();
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Item ID Availability based on Inventory, Qty in QC bins, Qty on Order, Qty in Carts
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function getAvailability($itemID) {
		$cart = Cart::getInstance();
		$qtyInventory = $this->inventory->getQtyByItemid($itemID);
		$qtyInCart    = $cart->items->qtyItemidAllSessionids($itemID);
		$qtyOnOrder   = $this->getQtyOnOrder($itemID);
		$qtyInQc      = $this->getQtyInQcBins($itemID);
		$available    = $qtyInventory - $qtyInQc - $qtyOnOrder - $qtyInCart;
		return $available >= 0 ? $available : 0;
	}
}
