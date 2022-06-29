<?php namespace Controllers\Orders;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use SalesOrder;
use SalesHistory;
// Dplus
use Dplus\Responses\AlertData;
use Dplus\Responses\Response;
use Dplus\Mso\So\SalesOrder as SalesOrders;
use Dplus\Mso\So\SalesHistory as Invoices;
// Ecomm
use Dplus\Ecomm\Order\Edit as OrderEditor;
// Mvc Controllers
use Controllers\Base;
use Controllers\Items\Item;

class Order extends Base {
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
		$url = self::orderUrl($data->ordn);
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function order($data) {
		self::sanitizeParametersShort($data, ['retry|bool']);
		$salesOrders = SalesOrders::instance();
		$invoices = Invoices::instance();

		if ($salesOrders->exists($data->ordn) === false && $invoices->exists($data->ordn) === false) {
			return self::displayError("Order Number not found", "Order #$data->ordn not found");
		}

		if ($salesOrders->orderUser($data->ordn, self::pw('user')) === false && $invoices->orderUser($data->ordn, self::pw('user')) === false) {
			return self::displayError("Not Permitted", "You are not permitted to view order #$data->ordn");
		}
		self::pw('page')->headline = "Order # $data->ordn";
		$order = $salesOrders->order($data->ordn);

		if ($invoices->exists($data->ordn)) {
			$order = $invoices->order($data->ordn);
		}
		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('orders/order/qnotes/js.twig');
		self::pw('page')->js .= self::pw('config')->twig->render('orders/order/order/order.js.twig');
		return self::displayOrder($data, $order);
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayError($title, $msg, $data = null) {
		$error = AlertData::newDanger($title, $msg);
		return self::pw('config')->twig->render('orders/order/error.twig', ['error' => $error]);
	}

	private static function displayOrder($data, $order) {
		$html = '';

		$isInvoice = Invoices::instance()->exists($data->ordn);

		if ($isInvoice) {
			self::pw('page')->show_breadcrumbs = false;
			$html .= self::pw('config')->twig->render('orders/invoices/breadcrumbs.twig');
		}

		$docm   = self::pw('modules')->get('DocumentManagementSo');
		$qnotes = self::pw('modules')->get('QnotesSales');
		$order->isEditable = SalesOrders::instance()->isEditable($data->ordn);
		$order->isInvoice  = $isInvoice;

		$html .= self::pw('config')->twig->render('orders/order/order.twig', ['order' => $order, 'docm' => $docm, 'qnotes' => $qnotes]);
		$html .= self::pw('config')->twig->render('orders/order/qnotes/modal.twig', ['ordn' => $data->ordn, 'qnotes' => $qnotes]);
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function orderUrl($ordn) {
		$url = new Purl(self::pw('pages')->get('template=order')->url);
		$url->path->add($ordn);
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('UgmOrderingPages');

		$m->addHook('Page(template=order)::orderEditUrl', function($event) {
			$ordn    = $event->arguments(0);
			$event->return = Order\Edit::editUrl($ordn);
		});

		$m->addHook('Page(template=order)::lotUrl', function($event) {
			$itemID = $event->arguments(0);
			$lotnbr = $event->arguments(1);
			$ordn   = $event->arguments(2);
			$event->return = Item::itemLotUrl($itemID, $lotnbr, $ordn);
		});
	}
}
