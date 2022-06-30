<?php namespace Dplus\Ecomm\Order\Edit;
// Dpluso Models
use OrdrdetQuery, Ordrdet;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\WireInput;
// Dplus
use Dplus\Mso\So\SalesOrder as SalesOrders;
use Dplus\Min\Itm;

// Ecomm
use Ecomm\Response;
use Dplus\Ecomm\Items\Available\Lots as LotAvailability;

/**
 * Handles Order Items Editing
 */
class Items extends WireData {
	public function __construct($sessionID = '') {
		$this->sessionID = $sessionID ? $sessionID : session_id();
		$this->inventory = LotAvailability::getInstance();
	}

	public function setSessionID($sessionID) {
		$this->sessionID = $sessionID;
	}
/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Ordrdet Query
	 * @param  string       $ordn Sales Order Number
	 * @return OrdrdetQuery
	 */
	public function queryOrdn($ordn) {
		$q = OrdrdetQuery::create();
		$q->filterBySessionid($this->sessionID);
		$q->filterByOrderno($ordn);
		return $q;
	}

	/**
	 * Return Ordrdet query filtered by Sales Order #, Line #
	 * @param  string $ordn    Sales Order Number
	 * @param  string $linenbr Line Number
	 * @return Ordrdet
	 */
	public function queryOrdnLinenbr($ordn, $linenbr = 1) {
		$q = $this->queryOrdn($ordn);
		$q->filterByLinenbr($linenbr);
		return $q;
	}

/* =============================================================
	Get Functions
============================================================= */
	/**
	 * Return Ordrdet Record
	 * @param  string $ordn    Sales Order Number
	 * @param  string $linenbr Line Number
	 * @return Ordrdet
	 */
	public function item($ordn, $linenbr) {
		$q = $this->queryOrdnLinenbr($ordn, $linenbr);
		return $q->findOne();
	}

	/**
	 * Return Ordrdet Records
	 * @param  string $ordn    Sales Order Number
	 * @param  string $linenbr Line Number
	 * @return Ordrdet[]
	 */
	public function items($ordn) {
		$q = $this->queryOrdn($ordn);
		return $q->find();
	}

	/**
	 * Return if Edit Detail Record Exists
	 * @param  string $ordn    Sales Order Number
	 * @param  string $linenbr Line Number
	 * @return bool
	 */
	public function exists($ordn, $linenbr) {
		$q = $this->queryOrdnLinenbr($ordn, $linenbr);
		return boolval($q->count());
	}

	/**
	 * Return if Order Has Detail Lines
	 * @param  string $ordn    Sales Order Number
	 * @return bool
	 */
	public function hasDetails($ordn) {
		$q = $this->queryOrdn($ordn);
		return boolval($q->count());
	}

	public function qtyItemid($ordn, $itemID) {
		$colQty = Ordrdet::get_aliasproperty('qty');

		$q = $this->queryOrdn($ordn);
		$q->filterByItemid($itemID);
		$q->addAsColumn('qty', "SUM($colQty)");
		$q->select('qty');
		return $q->findOne();
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
			case 'add-item':
				$this->addItem($input);
				break;
			case 'update-item-qty':
				$this->updateItemQty($input);
				break;
			case 'delete-item':
				$this->deleteItem($input);
				break;

		}
	}

/* =============================================================
	CRUD Update
============================================================= */
	/**
	 * Adds Item
	 * @param WireInput $input Input Data
	 */
	public function addItem(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$ordn   = $values->text('ordn');
		$itemID = $values->text('itemID');
		$qty    = $values->int('qty');

		$library = SalesOrders::instance();

		if ($library->isEditable($ordn) === false) {
			$this->wire('session')->response_edit = Response::createError("You cannot edit order #$ordn");
			return false;
		}

		$this->requestAdd($ordn, $itemID, $qty);
	}
	/**
	 * Updates Ordrdet Record
	 * @param  WireInput  $input  Input Data
	 * @return void
	 */
	private function updateItemQty(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$ordn    = $values->text('ordn');
		$linenbr = $values->int('linenbr');

		$library = SalesOrders::instance();

		if ($library->isEditable($ordn) === false){
			$this->wire('session')->response_edit = Response::createError("You cannot edit order #$ordn");
			return false;
		}

		if ($this->exists($ordn, $linenbr) === false){
			$this->wire('session')->response_edit = Response::createError("Order # $ordn Line #$linenbr not found");
			return false;
		}

		$item = $this->item($ordn, $linenbr);
		$item->setQty($values->int('qty'));
		$saved = $item->save();

		if ($saved === false) {
			$this->wire('session')->response_edit = Response::createError("Could not save changes to Order #$ordn Line #$linenbr");
			return false;
		}
		$this->requestUpdate($item);
	}

	/**
	 * Deletes Line Item
	 * @param  WireInput  $input  Input Data
	 * @return void
	 */
	public function deleteItem($input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$ordn    = $values->text('ordn');
		$linenbr = $values->int('linenbr');

		$library = SalesOrders::instance();

		if ($library->isEditable($ordn) === false){
			$this->wire('session')->response_edit = Response::createError("You cannot edit order #$ordn");
			return false;
		}

		$item = $this->item($ordn, $linenbr);
		$item->setQty(0);
		$item->save();
		$this->requestDelete($item);
	}

/* =============================================================
	CRUD Delete
============================================================= */

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Send Add Order Item
	 * @return void
	 */
	private function requestAdd($ordn, $itemID, int $qty) {
		$data = ['SALEDET', "ORDERNO=$ordn", "ITEMID=$itemID", "QTY=$qty"];
		$this->sendRequest($data);
	}

	/**
	 * Send Update Order Item Request to Dplus
	 * @return void
	 */
	private function requestUpdate(Ordrdet $item) {
		$data = ['SALEDET', "ORDERNO=$item->orderno", "LINENO=$item->linenbr"];
		$this->sendRequest($data);
	}

	/**
	 * Send Delete Order Item
	 * @return void
	 */
	private function requestDelete(Ordrdet $item) {
		$data = ['SALEDET', "ORDERNO=$item->orderno", "LINENO=$item->linenbr", "QTY=0"];
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
	public function getItm() {
		return Itm::getInstance();
	}
}
