<?php namespace Controllers\Items;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Dplus Document Managment
use Dplus\DocManagement\Finders\Lt\Img as Docm;
use Dplus\DocManagement\Folders;
use Dplus\DocManagement\Copier;
// Mvc Controllers
use Controllers\Base;


class Item extends Base {
	private static $docm;

	public static function index($data) {
		$whseLots = new WhseLots();
		$whseLots->setWhseID(1);
		$lots = $whseLots->getLotsByItemid(self::pw('page')->itemid);
		$docm = self::getDocm();
		self::copyImage($data, $lots);

		return self::pw('config')->twig->render('items/item/display.twig', ['lots' => $lots, 'docm' => $docm]);
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
					echo $copier->copyFile($folder->directory, $file->filename) ? 'true' : 'false';
				}
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
