<?php namespace Controllers;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Mvc Controllers
use Controllers\Base;

class Maintenance extends Base {
/* =============================================================
	Index Functions
============================================================= */
	public static function index($data) {
		// $fields = ['action|text'];
		// self::sanitizeParametersShort($data, $fields);

		return self::pw('config')->twig->render('maintenance/display.twig');
	}



/* =============================================================
	URLs functions
============================================================= */
	public static function url() {
		return self::pw('pages')->get('template=maintenance')->url;
	}
/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		// $m = self::pw('modules')->get('UgmOrderingPages');
	}
}
