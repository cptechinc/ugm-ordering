<?php namespace Controllers;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Base extends Controller {
	public static function getWhseLots() {
		$whseLots = WhseLots::getInstance();
		$whseLots->setWhseID(1);
		return $whseLots;
	}
}
