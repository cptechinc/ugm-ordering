<?php namespace Dplus\Wm\Inventory\Lots;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use InvWhseLotQuery, InvWhseLot;
use WarehouseBinQuery, WarehouseBin;
// ProcessWire
use ProcessWire\WireData;
// Dplus Inventory
use Dplus\Wm\Inventory\Lotm;

/**
 * WhseInventory
 * Class for filtering querying Whse Lots
 */
class Lookup extends WireData {
	private static $instance;
	private $whseID;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Setter Functions
============================================================= */
	public function setWhseID($whseID) {
		$this->whseID = $whseID;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered By Sessionid
	 * @return InvWhseLotQuery
	 */
	public function query() {
		$q = InvWhseLotQuery::create();
		return $q;
	}

	/**
	 * Return Query Filtered By Warehouse ID if set
	 * @return InvWhseLotQuery
	 */
	public function queryWhse() {
		$q = $this->query();

		if (empty($this->whseID) === false) {
			$q->filterByWhse($this->whseID);
		}
		return $q;
	}

	/**
	 * Return Query
	 * @return InvWhseLotQuery
	 */
	public function queryWhseBins() {
		return $this->queryWhse();
	}

/* =============================================================
	Lookup Functions
============================================================= */
	/**
	 * Return Bins for Item ID
	 * @param  array|string $itemID Item ID
	 * @return array
	 */
	public function getDistinctBinsByItemid($itemID) {
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->select(InvWhseLot::aliasproperty('bin'));
		$q->groupBy(InvWhseLot::aliasproperty('bin'));
		return $q->find()->toArray();
	}

	/**
	 * Return Lot #s, Qtys for Item ID
	 * @param  array|string $itemID Item ID
	 * @return array
	 */
	public function getLotsByItemid($itemID) {
		$colQty = InvWhseLot::aliasproperty('qty');
		$colLot = InvWhseLot::aliasproperty('lotserial');
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->addAsColumn('qty', "SUM($colQty)");
		$q->withColumn($colLot, 'lot');
		$q->select(['lot', 'qty']);
		$q->groupBy('lot');
		return $q->find()->toArray();
	}

	/**
	 * Return Lotnbrs
	 * @param  string $itemID Item ID
	 * @return array
	 */
	public function getLotnbrsByItemid($itemID) {
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->select(InvWhseLot::aliasproperty('lotserial'));
		return $q->find()->toArray();
	}

	/**
	 * Return if Lotserial matches itemid and has qty
	 * @param  string $lotserial  Lotserial
	 * @param  string $itemID     Item ID
	 * @return bool
	 */
	public function existsByItemid($lotserial, $itemID) {
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->filterByLotserial($lotserial);
		$q->filterByQty(1, Criteria::GREATER_EQUAL);
		return boolval($q->count());
	}

	/**
	 * Return Item IDs that have Stock
	 * @param  array  $itemID
	 * @return array
	 */
	public function getItemidsWithQty($itemID = []) {
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->filterByQty(1, Criteria::GREATER_EQUAL);
		$q->select(InvWhseLot::aliasproperty('itemid'));
		$q->distinct();
		return $q->find()->toArray();
	}

	/**
	 * Return Qty for Item ID
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function getQtyByItemid($itemID) {
		$colQty = InvWhseLot::aliasproperty('qty');
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->addAsColumn('qty', "SUM($colQty)");
		$q->select('qty');
		return intval($q->findOne());
	}

	/**
	 * Return Qty for Lot
	 * @param  string $lotserial Lot / Serial Number
	 * @return int
	 */
	public function getQtyByLotserial($lotserial) {
		$colQty = InvWhseLot::aliasproperty('qty');
		$q = $this->queryWhseBins();
		$q->filterByLotserial($lotserial);
		$q->addAsColumn('qty', "SUM($colQty)");
		$q->select('qty');
		return intval($q->findOne());
	}

	/**
	 * Count Item IDs that are in stock
	 * @param  string|array $itemID  Item ID
	 * @return int
	 */
	public function countInstockByItemid($itemID) {
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->filterByQty(1, Criteria::GREATER_EQUAL);
		return $q->count();
	}

	/**
	 * Count Item IDs that are in stock
	 * @param  string|array $itemID  Item ID
	 * @return int
	 */
	public function countInstockByItemidDistinct($itemID) {
		$col = InvWhseLot::aliasproperty('itemid');
		$q = $this->queryWhseBins();
		$q->filterByItemid($itemID);
		$q->filterByQty(1, Criteria::GREATER_EQUAL);
		$q->withColumn("COUNT(DISTINCT($col))", 'count');
		$q->select('count');
		return intval($q->findOne());
	}

	/**
	 * Return Lot
	 * @param  string $lotserial  Lot / Serial #
	 * @return InvWhseLot
	 */
	public function getLot($lotserial) {
		$q = $this->queryWhseBins();
		$q->filterByLotserial($lotserial);
		return $q->findOne();
	}

	/**
	 * Return if Any of the Lots for Item ID have images
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemidLotsHaveImages($itemID) {
		$lotnbrs = $this->getLotnbrsByItemid($itemID);
		$lotm    = Lotm::getInstance();
		return $lotm->lotsHaveImages($lotnbrs);
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
}
