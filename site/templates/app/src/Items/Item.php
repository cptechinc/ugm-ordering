<?php namespace Controllers\Items;
// Purl Manipulation Library
use Purl\Url as Purl;
// Dplus Document Managment
use Dplus\DocManagement\Finders\Lt\Img as Docm;
use Dplus\DocManagement\Folders;
use Dplus\DocManagement\Copier;
// Dplus Ecomm
use Dplus\Ecomm\Cart as CartCRUD;
use Dplus\Ecomm\Items\Available\Lots as LotInventory;
// Mvc Controllers
use Controllers\Base;


class Item extends Base {
	private static $docm;

	public static function index($data) {
		$lotInventory = LotInventory::getInstance();
		$lots = $lotInventory->getLotsByItemid(self::pw('page')->itemid);
		$docm = self::getDocm();
		self::copyImage($data, $lots);
		$cart = CartCRUD::getInstance();

		return self::pw('config')->twig->render('items/item/display.twig', ['lots' => $lots, 'docm' => $docm, 'cart' => $cart]);
	}

	private static function copyImage($data, $lots) {
		$docm = self::getDocm();

		foreach ($lots as $lot) {
			if ($docm->hasImage($lot['lot'])) {
				$file = $docm->getImage($lot['lot']);
				$folder = Folders::getInstance()->folder($file->folder);
				$copier = Copier::getInstance();
				$copier->useDocVwrDirectory();

				if ($copier->isInDirectory($file->filename) === false) {
					$copier->copyFile($folder->directory, $file->filename) ? 'true' : 'false';
				}
			}
		}
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function itemLotUrl($itemID, $lotserial) {
		$url = new Purl(self::pw('pages')->get("template=item,itemid=$itemID")->url);
		$url->path->add('lots');
		$url->path->add($lotserial);
		return $url->getUrl();
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('UgmOrderingPages');

	}

	private static function getDocm() {
		if (empty(self::$docm)) {
			self::$docm = new Docm();
		}
		return self::$docm;
	}
}
