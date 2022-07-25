<?php namespace Dplus\Ecomm\Items;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;


class ItemGroup extends WireData {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	public function getItemids($groupID) {
		$p = $this->getGroupPage($groupID);
		self::pw('page')->children('template=item')->explode('itemid')
	}
	
/* =============================================================
	Supplemental Functions
============================================================= */
	public function getGroupPage($groupID) {
		return $this->wire('pages')->get("template=item-group, groupcode=$groupID");
	}

	/**
	 * Return Whse Lots Lookup
	 * @return WhseLots
	 */
	public function getWhseLotsM() {
		$m = WhseLots::getInstance();
		$m->setWhseID(1);
		return $m;
	}
}
