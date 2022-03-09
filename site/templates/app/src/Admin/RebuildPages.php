<?php namespace Controllers\Admin;
// Dplus UgmOrdering
use Dplus\UgmOrdering\Pages;
// Mvc Controllers
use Controllers\Base;

class RebuildPages extends Base {
/* =============================================================
	Index Functions
============================================================= */
	public static function index($data) {
		$results = [
			'groups' => Pages\Itemgroup::instance()->updateAll(),
			'items' => [
				'deleted' => Pages\Item::instance()->deletePagesForOldItemids(),
				'updated' => Pages\Item::instance()->updateAll(),
			]
		];
		return $results;
	}

/* =============================================================
	Display Functions
============================================================= */


/* =============================================================
	URLs functions
============================================================= */


/* =============================================================
	Hooks
============================================================= */

}
