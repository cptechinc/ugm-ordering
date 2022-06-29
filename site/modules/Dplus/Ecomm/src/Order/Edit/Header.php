<?php namespace Dplus\Ecomm\Order\Edit;
// Dpluso Models
use OrdrhedQuery, Ordrhed;
use ShipviaQuery, Shipvia;
use StatesQuery, States;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\WireInput;
// Dplus Mso
use Dplus\Mso\So\SalesOrder as SalesOrders;
// Ecomm
use Ecomm\Response;

/**
 * Handles Order Header Editing
 */
class Header extends WireData {
	public function __construct($sessionID = '') {
		$this->sessionID = $sessionID ? $sessionID : session_id();
	}

	public function setSessionID($sessionID) {
		$this->sessionID = $sessionID;
	}
/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Ordrhed Query
	 * @param  string       $ordn Sales Order Number
	 * @return OrdrhedQuery
	 */
	public function queryOrdn($ordn) {
		$q = OrdrhedQuery::create();
		$q->filterBySessionid($this->sessionID);
		$q->filterByOrderno($ordn);
		return $q;
	}

/* =============================================================
	Get Functions
============================================================= */
	/**
	 * Return Ordrhed Record
	 * @param  string   $ordn  Sales Order Number
	 * @return Ordrhed
	 */
	public function order($ordn) {
		$q = $this->queryOrdn($ordn);
		return $q->findOne();
	}

	/**
	 * Return if Edit Order Record Exists
	 * @param  string   $ordn  Sales Order Number
	 * @return bool
	 */
	public function exists($ordn) {
		$q = $this->queryOrdn($ordn);
		return boolval($q->count());
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Take Input and process action
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if (empty($values->action)) {
			return false;
		}

		if (empty($values->ordn)) {
			return false;
		}

		switch ($values->text('action')) {
			case 'update-order':
				$this->updateOrder($input);
				break;
			case 'exit-order':
				 $this->requestExit($values->text('ordn'));
				break;
		}
	}

/* =============================================================
	CRUD Update
============================================================= */
	/**
	 * Updates Ordrhed Record
	 * @param  WireInput  $input  Input Data
	 * @return void
	 */
	private function updateOrder(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$ordn = $values->text('ordn');

		$library = SalesOrders::instance();

		if ($library->isEditable($ordn) === false){
			$this->wire('session')->response_edit = Response::createError("You cannot edit order #$ordn");
			return false;
		}

		$order = $this->order($ordn);
		$order->setShiptoid($values->text('shiptoid'));
		$order->setShipname($values->text('shipname'));
		$order->setContact($values->text('contact'));
		$order->setShipaddress($values->text('shipaddress'));
		$order->setShipaddress2($values->text('shipaddress2'));
		$order->setShipcity($values->text('shipcity'));
		$order->setShipstate($values->text('shipstate'));
		$order->setShipzip($values->text('shipzip'));
		$order->setEmail($values->text('email'));
		$order->setPhone($values->text('phone'));
		$order->setCustpo($values->text('custpo'));
		$order->setShipviacd($values->text('shipvia'));
		$order->setRqstdate($values->text('rqstdate'));
		$saved = $order->save();

		if ($saved === false) {
			$this->wire('session')->response_edit = Response::createError("Could not save changes to order #$ordn");
			return false;
		}
		$this->requestUpdate($order);
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Send Create Order Edit Request to Dplus
	 * NOTE: Keep public for controller
	 * @param  string $ordn  Sales Order #
	 * @return void
	 */
	public function requestOrder($ordn) {
		$data = ["ORDRDET=$ordn"];
		$this->sendRequest($data);
	}

	/**
	 * Send Update Order Request to Dplus
	 * @param  Ordrhed $order
	 * @return void
	 */
	private function requestUpdate(Ordrhed $order) {
		$data = ['SALESHEAD', "ORDERNO=$order->ordernumber", "CUSTID=$order->custid"];
		$this->sendRequest($data);
	}

	/**
	 * Send Unlock Order Request to Dplus
	 * @param  string $ordn  Sales Order #
	 * @return void
	 */
	private function requestExit($ordn) {
		$data = ['SALESHEAD', "ORDERNO=$ordn", 'UNLOCK'];
		$this->sendRequest($data);
	}

	/**
	 * Write Session File and Make CGI Request
	 * @param  array  $data
	 * @return void
	 */
	protected function sendRequest(array $data) {
		$dplusdb = $this->wire('modules')->get('DplusDatabaseDpluso')->db_name;
		$data = array_merge(["DBNAME=$dplusdb"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($this->wire('config')->cgis['default'], $this->sessionID);
	}

/* =============================================================
	Suppmental Functions
============================================================= */
	/**
	 * Return States
	 * @return States[]|ObjectCollection
	 */
	public function getStates() {
		return StatesQuery::create()->find();
	}

	/**
	 * Return Shipvias
	 * @return Shipvia[]|ObjectCollection
	 */
	public function getShipvias() {
		return ShipviaQuery::create()->find();
	}

}
