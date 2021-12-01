<?php namespace Dplus\Wm\Inventory\Lots;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use WhseLotserialQuery, WhseLotserial;
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
	 * @return WhseLotserialQuery
	 */
	public function query() {
		$q = WhseLotserialQuery::create();
		return $q;
	}

	/**
	 * Return Query Filtered By Warehouse ID if set
	 * @return WhseLotserialQuery
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
	 * @return WhseLotserialQuery
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
		$q->select(WhseLotserial::aliasproperty('bin'));
		$q->groupBy(WhseLotserial::aliasproperty('bin'));
		return $q->find()->toArray();
	}

	/**
	 * Return Lot #s, Qtys for Item ID
	 * @param  array|string $itemID Item ID
	 * @return array
	 */
	public function getLotsByItemid($itemID) {
		$colQty = WhseLotserial::aliasproperty('qty');
		$colLot = WhseLotserial::aliasproperty('lotserial');
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
		$q->select(WhseLotserial::aliasproperty('lotserial'));
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
		$q->select(WhseLotserial::aliasproperty('itemid'));
		return $q->find()->toArray();
	}

	/**
	 * Return Qty for Item ID
	 * @param  string $itemID Item ID
	 * @return int
	 */
	public function getQtyByItemid($itemID) {
		$colQty = WhseLotserial::aliasproperty('qty');
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
		$colQty = WhseLotserial::aliasproperty('qty');
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
	 * Return Lot
	 * @param  string $lotserial  Lot / Serial #
	 * @return WhseLotserial
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
}
