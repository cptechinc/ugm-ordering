<?php namespace Controllers;
// Dplus Ecomm
use Dplus\Ecomm\Cart as CartCRUD;
// Mvc Controllers
use Controllers\Base;


class Cart extends Base {
/* =============================================================
	Index Functions
============================================================= */
	public static function index($data) {
		$fields = ['action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::cart($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'page|text'];
		self::sanitizeParametersShort($data, $fields);
		$cart = CartCRUD::getInstance();
		$url  = self::cartUrl();

		if (empty($data->action) === false) {
			$success = $cart->processInput(self::pw('input'));

			switch ($data->action) {
				case 'checkout':
					if ($success) {
						$url = self::pw('pages')->get('template=checkout')->url;
					}
					break;
			}

			if (empty($data->page) === false) {
				$url = $data->page;
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function cart($data) {
		$config = self::pw('config');
		$page   = self::pw('page');
		$cart   = CartCRUD::getInstance();

		$checkout = self::pw('modules')->get('Checkout');
		$checkout->deleteOldBilling();

		$page->js .= $config->twig->render('cart/js.twig');
		$page->js .= $config->twig->render('cart/lookup.js.twig');
		$page->js .= $config->twig->render('cart/notes/js.twig');
		$config->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayCart($data);
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayCart($data) {
		$cart   = CartCRUD::getInstance();
		$qnotes = self::pw('modules')->get('QnotesCart');

		$html = '';
		$html .= self::displayResponseCart($data);
		$html .= self::displayResponseQnotes($data);
		$html .= self::pw('config')->twig->render('cart/cart.twig', ['cart' => $cart, 'qnotes' => $qnotes]);
		$html .= self::pw('config')->twig->render('cart/notes/modal.twig', ['cart' => $cart, 'qnotes' => $qnotes]);
		return $html;
	}

	private static function displayResponseCart($data) {
		$cart     = CartCRUD::getInstance();
		$response = $cart->getResponse();
		$html = '';

		if ($response) {
			$html .= $config->twig->render('util/dplus-response.twig', ['response' => $response]);
			$cart->deleteResponse();
		}
		return $html;
	}

	private static function displayResponseQnotes($data) {
		$session = self::pw('session');
		$html = '';

		if ($session->response_qnote) {
			$html .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}
		return $html;
	}

/* =============================================================
	URLs functions
============================================================= */
	public static function cartUrl() {
		return self::pw('pages')->get('template=cart')->url;
	}
}
