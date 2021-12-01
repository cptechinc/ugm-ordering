<?php namespace Controllers\Items;
// ProcessWire
use ProcessWire\Exceptions;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Dplus Ecomm
use Dplus\Ecomm\Items\Search as SearchItems;
use Dplus\Ecomm\Cart as CartCRUD;
use Dplus\Ecomm\Items\Available\Items as ItemInventory;
// Mvc Controllers
use Controllers\Base;

/**
 * Controller for the template items-search
 */
class Search extends Base {

	public static function index($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		return self::search($data);
	}

	private static function search($data) {
		if (empty($data->q) === false) {
			self::pw('page')->headline = "Searching for '$data->q'";
		}
		$search = new SearchItems();
		$search->setQuery($data->q);
		return self::displaySearch($data, $search);
	}

	private static function searchOld($data) {
		if (empty($data->q) === false) {
			self::pw('page')->headline = "Searching for '$data->q'";
		}
		$search = self::pw('modules')->get('ItemSearch');
		$search->set_search($data->q);
		return self::displaySearch($data, $search);
	}


/* =============================================================
	Display Functions
============================================================= */
	private static function displaySearch($data, SearchItems $search) {
		$items  = $search->find(self::pw('input')->pageNum, 12);
		$config = self::pw('config');
		$html = '';

		$itemAvailability = ItemInventory::getInstance();
		$cart     = CartCRUD::getInstance();

		$html  = '';
		$html .= $config->twig->render('items/search/form.twig', ['q' => $data->q]);

		if ($config->ajax) {
			$html .= $config->twig->render('items/search/results-ajax.twig', ['q' => $data->q, 'items' => $items, 'cart' => $cart, 'inventory' => $itemAvailability]);
		}
		if ($config->ajax === false) {
			$html .= $config->twig->render('items/search/results.twig', ['q' => $data->q, 'items' => $items, 'cart' => $cart, 'inventory' => $itemAvailability]);
		}
		$html .= $config->twig->render('util/paginator.twig', ['resultscount' => $search->count()]);
		return $html;
	}
/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('UgmOrderingPages');

	}
}
