<?php namespace Controllers\Checkout;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Model
use SalesOrderQuery, SalesOrder;
// Dplus Ecomm
use Dplus\Ecomm\Cart as CartCRUD;
use Dplus\Ecomm\Response;
// Mvc Controllers
use Controllers\Base;
use Controllers\Checkout;
use Controllers\Items\Item;
use Controllers\Cart;

class Confirmed extends Checkout {
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
		return self::confirmed($data);
	}

	private static function confirmed($data) {
		$config  = self::pw('config');
		$page    = self::pw('page');
		$session = self::pw('session');
		$checkoutm = self::pw('modules')->get('Checkout');
		$billing   = $checkoutm->get_billing();

		if ($billing->has_error() === false && empty($billing->orders) === false) {
			$session->setFor('order', 'created', $billing->orders);
		}

		if ($billing->has_error() === false && empty($billing->orders)) {
			$q = SalesOrderQuery::create();
			$q->select(SalesOrder::aliasproperty('ordernumber'));
			$q->filterByCustid($billing->custid);
			$q->filterByShiptoid($billing->shiptoid);
			$session->setFor('order', 'created', $q->findOne());
		}
		self::pw('config')->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		$html = self::displayConfirmed($data);
		CartCRUD::getInstance()->items->clear();
		$checkoutm->delete_billing();
		self::pw('modules')->get('QnotesCart')->delete_all();
		return $html;
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayConfirmed($data) {
		$config = self::pw('config');
		$checkoutm = self::pw('modules')->get('Checkout');
		$billing   = $checkoutm->get_billing();

		$html = '';
		$html .= self::displayResponses($data);
		$html .= $config->twig->render('checkout/confirmation.twig', ['billing' => $billing]);
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

	}
}
