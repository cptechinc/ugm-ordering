<?php namespace Controllers\Orders\Order;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dpluso Models
use Ordrhed;
// Dplus
use Dplus\Responses\AlertData;
use Dplus\Responses\Response;
use Dplus\Mso\So\SalesOrder as SalesOrders;
// Ecomm
use Dplus\Ecomm\Order\Edit as OrderEditor;
// Mvc Controllers
use Controllers\Base;
use Controllers\Items\Item;

class Edit extends Base {
/* =============================================================
	Index Functions
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['ordn|text', 'action|text']);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ordn)) {
			return self::displayError("Order Number not provided", 'Enter an Order # below');
		}
		self::pw('input')->get->ordn = $data->ordn;
		return self::order($data);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['ordn|text', 'action|text']);
		$editor = OrderEditor::instance();
		$editor->processInput(self::pw('input'));
		$url = self::editUrl($data->ordn);

		switch ($data->action) {
			case 'exit-order':
				$url = self::viewUrl($data->ordn);
				break;
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function order($data) {
		self::sanitizeParametersShort($data, ['retry|bool']);
		$library = SalesOrders::instance();

		if ($library->exists($data->ordn) === false) {
			return self::displayError("Order Number not found", "$data->ordn not found");
		}

		if ($library->isEditable($data->ordn) === false) {
			return self::displayError("Order can not be edited", "$data->ordn is not editable");
		}

		if ($library->orderUser($data->ordn, self::pw('user')) === false) {
			return self::displayError("Not Permitted", "You are not permitted to edit order #$data->ordn");
		}
		$editor = OrderEditor::instance();

		if ($editor->header->exists($data->ordn) === false) {
			if (boolval($data->retry) === false) {
				$editor->header->requestOrder($data->ordn);
				self::pw('session')->redirect(self::editUrl($data->ordn), $http301 = false);
			}
			return self::displayError('Order cannot be edited', "Order #$data->ordn cannot be loaded");
		}
		$order = $editor->header->order($data->ordn);
		self::pw('page')->headline = "Editing Order # $data->ordn";
		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('orders/order/edit/js.twig', ['editor' => $editor]);
		self::pw('page')->js .= self::pw('config')->twig->render('orders/order/qnotes/js.twig');
		self::pw('page')->js .= self::pw('config')->twig->render('cart/lookup.js.twig');
		return self::displayOrder($data, $order);
	}
/* =============================================================
	Display Functions
============================================================= */
	private static function displayError($title, $msg, $data = null) {
		$error = AlertData::newDanger('No Order # Provided', 'Enter an Order # below');
		return self::pw('config')->twig->render('orders/order/error.twig', ['error' => $error]);
	}

	private static function displayResponse($data) {
		$session = self::pw('session');
		$html = '';

		if ($session->response_edit) {
			$html .= self::pw('config')->twig->render('util/dplus-response.twig', ['response' => $session->response_edit]);
		}
		$editor = OrderEditor::instance();
		$response = $editor->getResponse();

		if ($response) {
			$html .= self::pw('config')->twig->render('util/dplus-response.twig', ['response' => $response]);
		}
		$session->remove('response_edit');
		$editor->deleteResponse();
		return $html;
	}

	private static function displayResponseQnotes($data) {
		$session = self::pw('session');
		$html = '';

		if ($session->response_qnotes) {
			$html .= self::pw('config')->twig->render('util/dplus-response.twig', ['response' => $session->response_qnotes]);
		}
		$session->remove('response_qnotes');
		return $html;
	}

	private static function displayOrder($data, Ordrhed $order) {
		$html = '';

		if ($order->has_error()) {
			$html .= self::pw('config')->twig->render('util/dplus-response.twig', ['response' => Response::create_error($orderedit->error)]);
		}
		$library = SalesOrders::instance();
		$orderStatic = $library->order($data->ordn);
		$qnotes = self::pw('modules')->get('QnotesSalesOrder');

		$html .= self::displayResponse($data);
		$html .= self::displayResponseQnotes($data);
		$html .= self::pw('config')->twig->render('orders/order/edit/order.twig', ['orderedit' => $order, 'orderstatic' => $orderStatic, 'editor' => OrderEditor::instance(), 'qnotes' => $qnotes]);
		$html .= self::pw('config')->twig->render('orders/order/qnotes/modal.twig', ['ordn' => $data->ordn, 'qnotes' => $qnotes]);
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function viewUrl($ordn) {
		$url = new Purl(self::pw('pages')->get('template=order')->url);
		$url->query->set('ordn', $ordn);
		return $url->getUrl();
	}

	public static function editUrl($ordn) {
		$url = new Purl(self::pw('pages')->get('template=order-edit')->url);
		$url->path->add($ordn);
		return $url->getUrl();
	}

	public static function exitUrl($ordn) {
		$url = new Purl(self::editUrl($ordn));
		$url->query->set('action', 'exit-order');
		$url->query->set('ordn', $ordn);
		return $url->getUrl();
	}

	public static function deleteLotUrl($ordn, $linenbr, $lot) {
		$url = new Purl(self::editUrl($ordn));
		$url->query->set('action', 'delete-lot');
		$url->query->set('ordn', $ordn);
		$url->query->set('linenbr', $linenbr);
		$url->query->set('lot', $lot);
		return $url->getUrl();
	}

	public static function deleteItemUrl($ordn, $linenbr) {
		$url = new Purl(self::editUrl($ordn));
		$url->query->set('action', 'delete-item');
		$url->query->set('ordn', $ordn);
		$url->query->set('linenbr', $linenbr);
		return $url->getUrl();
	}

	public static function itemUrl($itemID, $ordn = '') {
		$url = new Purl(self::pw('pages')->get("template=item,itemid=$itemID")->url);
		$url->query->set('ordn', $ordn);
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('UgmOrderingPages');

		$m->addHook('Page(template=order-edit)::orderExitUrl', function($event) {
			$ordn    = $event->arguments(0);
			$event->return = self::exitUrl($ordn);
		});

		$m->addHook('Page(template=order-edit)::searchItemsUrl', function($event) {
			$event->return = self::pw('pages')->get('template=items-search')->url;
		});

		$m->addHook('Page(template=order-edit)::itemDeleteUrl', function($event) {
			$ordn    = $event->arguments(0);
			$linenbr = $event->arguments(1);
			$event->return = self::deleteItemUrl($ordn, $linenbr);
		});

		$m->addHook('Page(template=order-edit)::lotDeleteUrl', function($event) {
			$ordn    = $event->arguments(0);
			$linenbr = $event->arguments(1);
			$lotnbr  = $event->arguments(2);
			$event->return = self::deleteLotUrl($ordn, $linenbr, $lotnbr);
		});

		$m->addHook('Page(template=order-edit)::itemLotsUrl', function($event) {
			$itemID = $event->arguments(0);
			$ordn   = $event->arguments(1);
			$event->return = self::itemUrl($itemID, $ordn);
		});

		$m->addHook('Page(template=order-edit)::itemsJsonUrl', function($event) {
			$event->return = self::pw('pages')->get('template=items-json')->url;
		});

		$m->addHook('Page(template=order-edit)::lotUrl', function($event) {
			$itemID = $event->arguments(0);
			$lotnbr = $event->arguments(1);
			$ordn   = $event->arguments(2);
			$event->return = Item::itemLotUrl($itemID, $lotnbr, $ordn);
		});
	}
}
