<?php namespace Dplus\Min;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire
use ProcessWire\WireData;

class Itm extends WireData {
	private static $instance;

	public static function getInstance() {
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
	 * Return Query
	 * @return ItemMasterItemQuery
	 */
	public function query() {
		return ItemMasterItemQuery::create();
	}

	/**
	 * Return Query filtered by Item ID
	 * @param  string $itemID  Item ID
	 * @return ItemMasterItemQuery
	 */
	public function queryItemid($itemID) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		return $q;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return if Item Exists
	 * @param  string $itemID  Item ID
	 * @return bool
	 */
	public function exists($itemID) {
		$q = $this->queryItemid($itemID);
		return boolval($q->count());
	}

	/**
	 * Return Item
	 * @param  string $itemID  Item ID
	 * @return ItemMasterItem
	 */
	public function item($itemID) {
		$q = $this->queryItemid($itemID);
		return $q->findOne();
	}
}
