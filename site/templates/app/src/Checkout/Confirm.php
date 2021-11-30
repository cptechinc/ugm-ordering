<?php namespace Controllers\Checkout;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Dplus Ecomm
use Dplus\Ecomm\Cart as CartCRUD;
use Dplus\Ecomm\Response;
// Mvc Controllers
use Controllers\Base;
use Controllers\Checkout;
use Controllers\Items\Item;

class Confirm extends Checkout {
/* =============================================================
	Index Functions
============================================================= */
	public static function index($data) {
		$fields = ['action|text'];
		self::sanitizeParametersShort($data, $fields);

		$checkoutm = self::pw('modules')->get('Checkout');
		$billing   = $checkoutm->get_billing();

		if (empty($billing)) {
			$session->redirect(Cart::checkoutUrl(), $http301 = false);
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::confirm($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text'];
		self::sanitizeParametersShort($data, $fields);
		$checkoutm = self::pw('modules')->get('Checkout');

		if (empty($data->action) === false) {
			$checkoutm->process_input(self::pw('input'));
		}
		self::pw('session')->redirect(self::checkoutConfirmUrl(), $http301 = false);
	}

	private static function confirm($data) {
		$config = self::pw('config');
		$page   = self::pw('page');
		$checkoutm = self::pw('modules')->get('Checkout');

		self::initHooks();
		$page->js .= $config->twig->render('cart/notes/js.twig');
		$config->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayConfirm($data);
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayConfirm($data) {
		$config = self::pw('config');
		$checkoutm = self::pw('modules')->get('Checkout');
		$cart      = CartCRUD::getInstance();
		$qnotes    = self::pw('modules')->get('QnotesCart');

		$html = '';
		$html .= self::displayResponses($data);
		$html .= $config->twig->render('checkout/confirm-checkout.twig', ['checkoutm' => $checkoutm, 'user' => self::pw('user'), 'qnotes' => $qnotes, 'cart' => $cart, 'inventory' => self::getWhseLots()]);
		$html .= $config->twig->render('cart/notes/modal.twig', ['qnotes' => $qnotes]);
		return $html;
	}

/* =============================================================
	URLs functions
============================================================= */


/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		parent::initHooks();

		$m = self::pw('modules')->get('UgmOrderingPages');

		$m->addHook('Page(template=checkout-confirm)::lotUrl', function($event) {
			$itemID = $event->arguments(0);
			$lot     = $event->arguments(1);
			$event->return = Item::itemLotUrl($itemID, $lot);
		});
	}
}
