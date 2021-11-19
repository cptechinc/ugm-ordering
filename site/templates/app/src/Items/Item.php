<?php namespace Controllers\Items;
// Mvc Controllers
use Controllers\Base;

class Item extends Base {
	public static function index($data) {
		return self::pw('config')->twig->render('items/item/display.twig');
	}
}
