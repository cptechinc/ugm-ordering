<?php namespace Controllers\Items;
// ProcessWire
use ProcessWire\Exceptions;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Mvc Controllers
use Controllers\Base;


class ItemGroup extends Base {

	public static function index($data) {
		if (self::pw('user')->has_itemgroup(self::pw('page')->groupcode) === false) {
			throw new Wire404Exception();
		}
		return self::itemgroup($data);
	}

	private static function itemgroup($data) {
		$search = self::pw('modules')->get('ItemSearch');
		$search->send_request_all();
		$dpluspricing = self::pw('modules')->get('ItemSearchDplus');
		$page = self::pw('page');
		$page->searchurl = self::pw('pages')->get('template=items-search')->url;
		$page->carturl   = self::pw('pages')->get('template=cart')->url;
		$items = $page->get_stocked_items();

		$html  = '';
		$html .= self::pw('config')->twig->render('items/search/form.twig');
		$html .= self::pw('config')->twig->render('items/list.twig', ['items' => $items, 'dpluspricing' => $dpluspricing]);
		return $html;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('UgmOrderingPages');

	}
}
