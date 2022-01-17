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
	/**
	 * Return Query
	 * @return InvWhseLotQuery
	 */
	public function queryWhseBins() {
		$q = $this->queryWhse();
		$q->filterByBin('PACK', Criteria::ALT_NOT_EQUAL);
		return $q;
	}
}
