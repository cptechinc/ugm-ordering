<?php namespace Controllers\Items\Item;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Dplus Document Managment
use Dplus\DocManagement\Finders\Lt\Img as Docm;
use Dplus\DocManagement\Folders;
use Dplus\DocManagement\Copier;
// Dplus Ecomm
use Dplus\Ecomm\Cart as CartCRUD;
// Mvc Controllers
use Controllers\Base;

class Lots extends Base {
	private static $docm;

	public static function index($data) {
		$fields = ['lot|text'];
		self::sanitizeParametersShort($data, $fields);

		if ($data->lot) {
			return self::lot($data);
		}


	}

	private static function lot($data) {
		$whseLots = new WhseLots();
		$whseLots->setWhseID(1);
		$lot = $whseLots->getLot($data->lot);
		self::copyImage($data, $lot);

		$docm = self::getDocm();
		$cart = CartCRUD::getInstance();
		return self::pw('config')->twig->render('items/item/lot/display.twig', ['lot' => $lot, 'docm' => $docm, 'cart' => $cart]);
	}

	private static function copyImage($data, $lot) {
		$docm = self::getDocm();

		if ($docm->hasImage($lot->lotserial)) {
			$file = $docm->getImage($lot->lotserial);
			$folder = Folders::getInstance()->folder($file->folder);
			$copier = Copier::getInstance();
			$copier->useDocVwrDirectory();

			if ($copier->isInDirectory($file->filename) === false) {
				$copier->copyFile($folder->directory, $file->filename) ? 'true' : 'false';
			}
		}
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
