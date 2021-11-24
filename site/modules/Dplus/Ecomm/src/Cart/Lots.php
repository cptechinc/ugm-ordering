<?php namespace Dplus\Ecomm\Cart;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dpluso Model
use CartLotQuery, CartLot;
// ProcessWire
use ProcessWire\WireData;


class Lots extends WireData {
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
	 * Return CartLotQuery
	 * @return CartLotQuery
	 */
	public function query() {
		$q = CartLotQuery::create();
		$q->filterBySessionid($this->sessionID);
		return $q;
	}

	/**
	 * Return Query for CartLots filtered By Item ID
	 * @param  string $itemID  Item ID
	 * @return CartLotQuery
	 */
	public function queryItemid($itemID) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		return $q;
	}

/* =============================================================
	Get Functions
============================================================= */
	/**
	 * Return Lots For Line Number
	 * @property int $linenbr
	 * @return CartLot[]|ObjectCollection
	 */
	public function lotsByLinenbr($linenbr = 1) {
		$q = $this->query();
		$q->filterByLinenbr($linenbr);
		return $q->find();
	}

	public function lineHasLots($linenbr = 1) {
		$q = $this->query();
		$q->filterByLinenbr($linenbr);
		return boolval($q->count());
	}

	/**
	 * Return if Lot Exists for Item ID
	 * @param  string $lot     Lot Serial #
	 * @param  string $itemID  Item ID
	 * @return boolval
	 */
	public function existsByItemid($lot, $itemID) {
		$q = $this->queryItemid($itemID);
		$q->filterByLotserial($lot);
		return boolval($q->count());
	}

	/**
	 * Return if Lot Exists for Line
	 * @param  string $lot     Lot Serial #
	 * @param  int    $linenbr Line Number
	 * @return boolval
	 */
	public function existsByLinenbr($lot, $linenbr) {
		$q = $this->query();
		$q->filterByLinenbr($linenbr);
		$q->filterByLotserial($lot);
		return boolval($q->count());
	}

	/**
	 * Return if Lot Exists
	 * @param  string $lot     Lot Serial #
	 * @return boolval
	 */
	public function exists($lot) {
		$q = $this->query();
		$q->filterByLotserial($lot);
		return boolval($q->count());
	}
}
