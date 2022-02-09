<?php namespace Controllers;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;
// Dplus Ecomm
use Dplus\Ecomm\Cart as CartCRUD;
use Dplus\Ecomm\Response;
// Mvc Controllers
use Controllers\Base;
use Controllers\Items\Item;

class Checkout extends Base {
/* =============================================================
	Index Functions
============================================================= */
	public static function index($data) {
		$fields = ['action|text'];
		self::sanitizeParametersShort($data, $fields);

		$checkoutm = self::pw('modules')->get('Checkout');
		$billing   = $checkoutm->get_billing();

		if (empty($billing)) {
			self::pw('session')->redirect(Cart::checkoutUrl(), $http301 = false);
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::checkout($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'page|text'];
		self::sanitizeParametersShort($data, $fields);
		$checkoutm = self::pw('modules')->get('Checkout');
		$url  = self::checkoutUrl();

		if (empty($data->action) === false) {
			$checkoutm->process_input(self::pw('input'));
			$url = $data->action == 'update-billing' ? self::checkoutConfirmUrl() : self::checkoutUrl();
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function checkout($data) {
		$config = self::pw('config');
		$page   = self::pw('page');
		$checkoutm = self::pw('modules')->get('Checkout');

		self::initHooks();
		$page->js .= $config->twig->render('checkout/js.twig', ['checkoutm' => $checkoutm]);
		$page->js .= $config->twig->render('cart/notes/js.twig');
		$config->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayCheckout($data);
	}

/* =============================================================
	Display Functions
============================================================= */
	protected static function displayCheckout($data) {
		$config = self::pw('config');
		$checkoutm = self::pw('modules')->get('Checkout');
		$cart      = CartCRUD::getInstance();
		$qnotes    = self::pw('modules')->get('QnotesCart');

		$html = '';
		$html .= self::displayResponses($data);
		$html .= $config->twig->render('checkout/checkout.twig', ['checkoutm' => $checkoutm, 'user' => self::pw('user'), 'qnotes' => $qnotes, 'cart' => $cart, 'inventory' => self::getWhseLots()]);
		$html .= $config->twig->render('cart/notes/modal.twig', ['qnotes' => $qnotes]);
		return $html;
	}

	protected static function displayResponses($data) {
		$checkoutm = self::pw('modules')->get('Checkout');
		$billing   = $checkoutm->get_billing();

		$html = '';
		$html .= self::displayResponseQnotes($data);
		$html .= self::displayResponseCheckout($data);

		if ($billing->has_error()) {
			$html .= self::pw('config')->twig->render('util/ecomm-response.twig', ['response' => Response::createError($billing->ermes)]);
		}

		return $html;
	}

	protected static function displayResponseQnotes($data) {
		$session = self::pw('session');
		$html = '';

		if ($session->response_qnote) {
			$html .= self::pw('config')->render('util/dplus-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}
		return $html;
	}

	protected static function displayResponseCheckout($data) {
		$session = self::pw('session');
		$html = '';

		if ($session->response_checkout) {
			$html .= self::pw('config')->twig->render('util/dplus-response.twig', ['response' => $session->response_checkout]);
			$session->remove('response_checkout');
		}
		return $html;
	}

/* =============================================================
	URLs functions
============================================================= */
	public static function checkoutUrl() {
		return self::pw('pages')->get('template=checkout')->url;
	}

	public static function checkoutConfirmUrl() {
		return self::pw('pages')->get('template=checkout-confirm')->url;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('UgmOrderingPages');

		$m->addHook('Page(template=checkout)::lotUrl', function($event) {
			$itemID = $event->arguments(0);
			$lot     = $event->arguments(1);
			$event->return = Item::itemLotUrl($itemID, $lot);
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getWhseLots() {
		$whseLots = new WhseLots();
		$whseLots->setWhseID(1);
		return $whseLots;
	}
}
