<?php namespace Dplus\Wm\Inventory\Lots\Lookup;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Wm
use Dplus\Wm\Inventory\Lots\Lookup as Base;

/**
 * ExcludePackBin
 * Strategy for Inventory Lookup that Excludes looking in Pack Bin
 */
class ExcludePackBin extends Base {
	private static $instance;
	private $whseID;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}
	
	/**
	 * Return Query
	 * @return InvWhseLotQuery
	 */
	public function queryWhseBins() {
		$excludeBinids = $this->getQcBinids();
		$excludeBinids[] = 'PACK';
		$q = $this->queryWhse();
		$q->filterByBin($excludeBinids, Criteria::NOT_IN);
		return $q;
	}
}
