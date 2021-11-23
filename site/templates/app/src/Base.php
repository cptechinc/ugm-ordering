<?php namespace Controllers;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Base extends AbstractController {
	public static function getWhseLots() {
		$whseLots = WhseLots::getInstance();
		$whseLots->setWhseID(1);
		return $whseLots;
	}
}
