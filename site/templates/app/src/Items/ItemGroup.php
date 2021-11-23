<?php namespace Controllers\Items;
// ProcessWire
use ProcessWire\Exceptions;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Mvc Controllers
use Controllers\Base;

/**
 * Controller for the template item-group
 */
class ItemGroup extends Base {

	public static function index($data) {
		if (self::pw('user')->has_itemgroup(self::pw('page')->groupcode) === false) {
			throw new Wire404Exception();
		}
		return self::itemgroup($data);
	}

	private static function itemgroup($data) {
		$items = self::getInstockItems();

		$html  = '';
		$html .= self::pw('config')->twig->render('items/search/form.twig');
		$html .= self::pw('config')->twig->render('items/list.twig', ['items' => $items, 'inventory' => self::getWhseLots()]);
		return $html;
	}

/* =============================================================
	Item Filtering
============================================================= */
	private static function search() {
		$search = self::pw('modules')->get('ItemSearch');
		$search->send_request_all();
		return self::pw('page')->get_stocked_items();
	}

	private static function getInstockItemids() {
		$whseLots = self::getWhseLots();
		return $whseLots->getItemidsWithQty(self::pw('page')->children('template=item')->explode('itemid'));
	}

	private static function getInstockItems() {
		$whseLots = self::getWhseLots();
		$itemIDs  = self::getInstockItemids();
		return self::pw('page')->children("template=item,itemid=".implode('|', $itemIDs) . ',sort=itemid');
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('UgmOrderingPages');

	}

	public static function getWhseLots() {
		$whseLots = WhseLots::getInstance();
		$whseLots->setWhseID(1);
		return $whseLots;
	}
}
