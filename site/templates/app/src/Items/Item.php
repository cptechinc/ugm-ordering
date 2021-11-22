<?php namespace Controllers\Items;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Dplus Document Managment
use Dplus\DocManagement\Finders\Lt\Img as Docm;
// Mvc Controllers
use Controllers\Base;


class Item extends Base {
	public static function index($data) {
		$whseLots = new WhseLots();
		$whseLots->setWhseID(1);
		$lots = $whseLots->getLotsByItemid(self::pw('page')->itemid);

		return self::pw('config')->twig->render('items/item/display.twig', ['lots' => $lots, 'lotm' => $lotm]);
	}
}
