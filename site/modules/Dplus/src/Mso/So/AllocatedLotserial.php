<?php namespace Dplus\Mso\So;
// Dplus Model
use SoAllocatedLotserialQuery, SoAllocatedLotserial;
// ProcessWire
use ProcessWire\WireData;
// Dplus Document Management
use Dplus\DocManagement\Finders\Lt\Img as Docm;

class AllocatedLotserial extends WireData {
	private static $instance;

	public static function instance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered By Sessionid
	 * @return SoAllocatedLotserialQuery
	 */
	public function query() {
		return SoAllocatedLotserialQuery::create();
	}

	/**
	 * Return Query Filtered by Sales Order Number
	 * @param  string $ordn  Sales Order Number
	 * @return SoAllocatedLotserialQuery
	 */
	public function querySo($ordn) {
		$q = $this->query();
		$q->filterByOrdn($ordn);
		return $q;
	}

	/**
	 * Return Query Filtered by Sales Order Number, Linenumber
	 * @param  string $ordn     Sales Order Number
	 * @param  string $linenbr  Line Number
	 * @return SoAllocatedLotserialQuery
	 */
	public function querySoLinenbr($ordn, $linenbr) {
		$q = $this->querySo($ordn);
		$q->filterByLine($linenbr);
		return $q;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return if Order Line has Allocated Lotserials
	 * @param  string $ordn     Sales Order Number
	 * @param  string $linenbr  Line Number
	 * @return bool
	 */
	public function hasAllocated($ordn, $linenbr) {
		$q = $this->querySoLinenbr($ordn, $linenbr);
		return boolval($q->count());
	}

	/**
	 * Return Allocated Lotserials for Order
	 * @param  string $ordn     Sales Order Number
	 * @param  string $linenbr  Line Number
	 * @return SoAllocatedLotserial[]
	 */
	public function allocatedLotserials($ordn, $linenbr) {
		if ($this->hasAllocated($ordn, $linenbr) === false) {
			return [];
		}
		$q = $this->querySoLinenbr($ordn, $linenbr);
		return $q->find();
	}

	/**
	 * Return Total Lot Qty for Item ID
	 * @param  string $ordn     Sales Order Number
	 * @param  string $itemID   Item ID
	 * @return float
	 */
	public function qtyItemid($ordn, $itemID) {
		$colQty = SoAllocatedLotserial::aliasproperty('qtyship');

		$q = $this->querySo($ordn);
		$q->filterByItemid($itemID);
		$q->addAsColumn('qty', "SUM($colQty)");
		$q->select('qty');
		return $q->findOne();
	}

	/**
	 * Return if Lot Exists for Line #
	 * @param  string $ordn       Sales Order Number
	 * @param  string $linenbr    Line Number
	 * @param  string $lotserial  Lot / Serial #
	 * @return bool
	 */
	public function existsByLinenbr($ordn, $linenbr, $lotserial) {
		$q = $this->querySoLinenbr($ordn, $linenbr);
		$q->filterByLotserial($lotserial);
		return boolval($q->count());
	}

	/**
	 * Return Allocated Lot
	 * @param  string $ordn       Sales Order Number
	 * @param  string $linenbr    Line Number
	 * @param  string $lotserial  Lot / Serial #
	 * @return SoAllocatedLotserial
	 */
	public function lot($ordn, $linenbr, $lotserial) {
		$q = $this->querySoLinenbr($ordn, $linenbr);
		$q->filterByLotserial($lotserial);
		return $q->findOne();
	}

	/**
	 * Return Qty allocated for whole order
	 * @param  string $ordn       Sales Order Number
	 * @param  string $lotserial  Lot / Serial #
	 * @return float
	 */
	public function qty($ordn, $lotserial) {
		$colQty = SoAllocatedLotserial::aliasproperty('qtyship');

		$q = $this->querySo($ordn);
		$q->filterByLotserial($lotserial);
		$q->addAsColumn('qty', "SUM($colQty)");
		$q->select('qty');
		return $q->findOne();
	}

	/**
	 * Return if Lot exists on order
	 * @param  string $ordn       Sales Order Number
	 * @param  string $lotserial  Lot / Serial #
	 * @return bool
	 */
	public function existsOnOrder($ordn, $lotserial) {
		$q = $this->querySo($ordn);
		$q->filterByLotserial($lotserial);
		return boolval($q->count());
	}
}
