<?php namespace Dplus\Wm\Inventory;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use InvLotQuery, InvLot;
// ProcessWire
use ProcessWire\WireData;

/**
 * Lotm
 * Wrapper for querying Lot Master
 */
class Lotm extends WireData {
	private static $instance;

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


/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered By Sessionid
	 * @return InvLotQuery
	 */
	public function query() {
		$q = InvLotQuery::create();
		return $q;
	}

	/**
	 * Return Query Filtered By Warehouse ID if set
	 * @param array|string $lotnbr
	 * @return InvLotQuery
	 */
	public function queryLotnbr($lotnbr = null) {
		$q = $this->query();

		if (empty($lotnbr) === false) {
			$q->filterByLotnbr($lotnbr);
		}
		return $q;
	}

/* =============================================================
	Lookup Functions
============================================================= */
	public function lotsHaveImages($lotnbr = null) {
		$q = $this->queryLotnbr($lotnbr);
		$q->filterByHasimage(InvLot::YN_TRUE);
		return boolval($q->count());
	}
}
