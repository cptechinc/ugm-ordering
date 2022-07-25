<?php namespace Dplus\Ecomm\Order\Edit;
// Dplus Models
use SoAllocatedLotserial;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\WireInput;
// Dplus
use Dplus\Mso\So\AllocatedLotserial;
use Dplus\Min\Itm;
// Ecomm
use Dplus\Responses\Response;
use Dplus\Ecomm\Items\Available\Lots as LotAvailability;

/**
 * Handles Order Items Editing
 */
class AllocatedLots extends WireData {
	public function __construct($sessionID = '') {
		$this->sessionID = $sessionID ? $sessionID : session_id();
		$this->allocatedLots = AllocatedLotserial::instance();
	}

	public function setSessionID($sessionID) {
		$this->sessionID = $sessionID;
	}

/* =============================================================
	Get Functions
============================================================= */
	/**
	 * Return Allocated Lots
	 * @param  string $ordn    Sales Order Number
	 * @param  string $linenbr Line Number
	 * @return Ordrdet
	 */
	public function allocatedLots($ordn, $linenbr) {
		return $this->allocatedLots->allocatedLotserials($ordn, $linenbr);
	}

	/**
	 * Return if Line has Allocated Lots
	 * @param  string $ordn    Sales Order Number
	 * @param  string $linenbr Line Number
	 * @return bool
	 */
	public function hasAllocated($ordn, $linenbr) {
		return $this->allocatedLots->hasAllocated($ordn, $linenbr);
	}

	/**
	 * Return Total Allocated Lot Qty for Item ID
	 * @param  string $ordn    Sales Order Number
	 * @param  string $$itemID Item ID
	 * @return float
	 */
	public function qtyItemid($ordn, $itemID) {
		return $this->allocatedLots->qtyItemid($ordn, $itemID);
	}

	/**
	 * Return Total Allocated Lot Qty
	 * @param  string $ordn    Sales Order Number
	 * @param  string $lotnbr  Lot Number
	 * @return float
	 */
	public function qty($ordn, $lotnbr) {
		return $this->allocatedLots->qty($ordn, $lotnbr);
	}

	/**
	 * Return if Lot Exists
	 *@param   string $ordn     Sales Order Number
	 * @param  string $linenbr Line Number
	 * @param  string $lotnbr  Lot Number
	 * @return bool
	 */
	public function existsByLinenbr($ordn, $linenbr, $lotnbr) {
		return $this->allocatedLots->existsByLinenbr($ordn, $linenbr, $lotnbr);
	}

	public function existsOnOrder($ordn, $lotnbr) {
		return $this->allocatedLots->existsOnOrder($ordn, $lotnbr);
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
			case 'add-lot':
				return $this->addLot($input);
				break;
			case 'update-lot-qty':
				return $this->updateLotQty($input);
				break;
			case 'delete-lot':
				return $this->deleteLot($input);
				break;
		}
	}

/* =============================================================
	CRUD Update
============================================================= */
	/**
	 * Processes Input for Update Lot Qty Request
	 * @param  WireInput $input
	 * @return void
	 */
	private function updateLotQty(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$ordn    = $values->text('ordn');
		$linenbr = $values->int('linenbr');
		$lotnbr  = $values->text('lot');
		$qty     = $values->int('qty');

		if ($this->existsByLinenbr($ordn, $linenbr, $lotnbr) === false) {
			$this->setResponse(Response::createError("Line # $linenbr Lot $lotnbr not found"));
			return false;
		}
		$lot = $this->allocatedLots->lot($ordn, $linenbr, $lotnbr);
		$lot->setQtyship($qty);
		$item = $this->item($ordn, $linenbr);
		$item->setQty($item->qty + $values->int('qty'));
		$item->save();
		$this->requestLotUpdateQty($lot);
	}

	/**
	 * Processes Input for Add To Cart Request
	 * @param  WireInput $input
	 * @return bool
	 */
	private function addLot(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$lot = new WireData();
		$lot->ordernumber = $values->text('ordn');
		$lot->itemid      = $values->text('itemID');
		$lot->lotserial   = $values->text('lot');
		$lot->qty         = $values->int('qty') ? $values->int('qty') : 1;
		$lot->linenbr     = $values->int('linenbr');

		if (Itm::getInstance()->exists($lot->itemid) === false) {
			$this->setResponse(Response::createError("Item $lot->itemid not found"));
			return false;
		}

		$lotAvailability = LotAvailability::getInstance();

		if ($lotAvailability->getInventory()->existsByItemid($lot->lotserial, $lot->itemid) === false) {
			$this->setResponse(Response::createError("Item $lot->itemid Lot $lot->lotserial not found"));
			return false;
		}

		$qtyAvailable = $lotAvailability->getLotAvailability($lot->lotserial);

		if ($qtyAvailable < $lot->qty) {
			$msg = $qtyAvailable == 0 ? "Lot $lot->lotserial is Out of Stock" : "Lot $lot->lotserial only has $qtyAvailable left";
			$this->setResponse(Response::createError($msg));
			return false;
		}

		$this->requestLotAdd($lot);

		if ($this->existsOnOrder($lot->ordernumber, $lot->lotserial) === false) {
			$this->setResponse(Response::createError("$lot->itemid Lot $lot->lotserial was not added to the order"));
			return false;
		}
		$this->setResponse(Response::createSuccess("$lot->itemid Lot $lot->lotserial was added to the order"));
		return true;
	}

/* =============================================================
	CRUD Delete
============================================================= */
	/**
	 * Processes Input for Update Lot Qty Request
	 * @param  WireInput $input
	 * @return void
	 */
	private function deleteLot(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$ordn    = $values->text('ordn');
		$linenbr = $values->int('linenbr');
		$lotnbr  = $values->text('lot');

		if ($this->existsByLinenbr($ordn, $linenbr, $lotnbr) === false) {
			return true;
		}
		$lot = $this->allocatedLots->lot($ordn, $linenbr, $lotnbr);
		$this->requestLotDelete($lot);
	}

/* =============================================================
	Response Functions
============================================================= */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('order', 'response', $response);
	}

	public function getResponse() {
		return $this->wire('session')->getFor('order', 'response');
	}

	public function deleteResponse() {
		return $this->wire('session')->removeFor('order', 'response');
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Send Update Qty LOT Request
	 * @param  SoAllocatedLotserial $lot     Allocated Lot
	 * @return void
	 */
	private function requestLotUpdateQty(SoAllocatedLotserial $lot) {
		$data = ["EDITLOTONSALESORDER", "ORDERNO=$lot->ordernumber", "LINENBR=$lot->linenumber", "LOTSER=$lot->lotserial", "QTY=$lot->qtyship"];
		$this->sendRequest($data);
	}

	/**
	 * Send Delete LOT Request
	 * @param  SoAllocatedLotserial $lot     Allocated Lot
	 * @return void
	 */
	private function requestLotDelete(SoAllocatedLotserial $lot) {
		$data = ["REMOVELOTFROMSALESORDER", "ORDERNO=$lot->ordernumber", "LINENBR=$lot->linenumber", "LOTSER=$lot->lotserial"];
		$this->sendRequest($data);
	}

	/**
	 * Send Add LOT Request
	 * @param  WireData $lot     Allocated Lot
	 * @return void
	 */
	private function requestLotAdd(WireData $lot) {
		$data = ["ADDLOTTOSALESORDER", "ORDERNO=$lot->ordernumber", "ITEMID=$lot->itemid", "LOTSER=$lot->lotserial", "QTY=$lot->qty", "LINENBR=$lot->linenbr"];
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
}
