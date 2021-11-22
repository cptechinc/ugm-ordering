<?php namespace Dplus\Ecomm;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;


class Cart extends WireData {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function __construct() {
		$this->sessionID = session_id();
		$this->items = Cart\Items::getInstance();
		$this->items->setSessionid($this->sessionID);
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Process Input Data, process cart action
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'add-item':
				return $this->inputAddItem($input);
				break;
			case 'update-item-qty':
				return $this->inputUpdateItemQty($input);
				break;
			case 'delete-item':
				return $this->inputDeleteItem($input);
				break;
			case 'update-notes':
			case 'delete-notes':
				$qnotes = $this->wire('modules')->get('QnotesCart');
				return $qnotes->process_input($input);
				break;
			case 'checkout':
				return $this->checkout($input);
				break;
		}
	}

	/**
	 * Processes Input for Add To Cart Request
	 * @param  WireInput $input
	 * @return bool
	 */
	private function inputAddItem(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$validateItem = $this->wire('modules')->get('ValidateItem');
		$itemID = $values->text('itemID');
		$qty    = $values->int('qty') ? $values->int('qty') : 1;

		if ($validateItem->validate($itemID)) {
			$this->requestItemAdd($itemID, $qty);

			if ($this->items->exists($itemID)) {
				$response = Response::createSuccess("$itemID was added to the cart");
				$this->wire('session')->setFor('cart', 'add', $itemID);
				$this->setResponse($response);
				return true;
			}
			$this->setResponse(Response::createError("$itemID was not added to the cart"));
			return false;
		}
		$this->setResponse(Response::createError("Item $itemID not found"));
		return false;
	}

	/**
	 * Processes Input for Edit Item Qty Request
	 * @param  WireInput $input
	 * @return void
	 */
	public function inputUpdateItemQty(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->text('itemID');
		$qty = $values->int('qty') ? $values->int('qty') : 1;

		if ($this->items->exists($itemID)) {
			$this->requestItemUpdate($itemID, $qty);
			return true;
		}
		$this->setResponse(Response::createError("Item $itemID is not in cart"));
		return false;
	}

	/**
	 * Processes Input for Delete Item Qty Request
	 * @param  WireInput $input
	 * @return void
	 */
	public function inputDeleteItem(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->text('itemID');

		if ($this->items->exists($itemID)) {
			$this->requestItemDelete($itemID);

			if ($this->items->exists($itemID) === false) {
				$qnotes = $this->wire('modules')->get('QnotesCart');
				$qnotes->delete_notes($itemID);
			}
			return true;
		}
		$this->setResponse(Response::createError("Item $itemID is not in cart"));
		return false;
	}

	/**
	 * Processes Checkout Request
	 * @return void
	 */
	private function checkout() {
		if ($this->items->count()) {
			$checkoutM = $this->modules->get('Checkout');
			if ($checkoutM->has_billing() === false) {
				$this->requestCheckout();
			}

			if ($checkoutM->has_billing()) {
				$billing = $checkoutM->get_billing();

				if (empty($billing->orders) === false) {
					$this->requestCheckout();
				}
			}
			return true;
		}
		$this->setResponse(Response::createError("You don't have items in your cart"));
		return false;
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Send Add Item Request
	 * @param  string $itemID Item ID
	 * @param  int    $qty    Qty
	 * @return void
	 */
	protected function requestItemAdd($itemID, int $qty) {
		$data = ["ADDTOCART", "ITEMID=$itemID", "QTY=$qty"];
		$this->sendRequest($data);
	}

	/**
	 * Send Update Item Request
	 * @param  string $itemID Item ID
	 * @param  int    $qty    Qty
	 * @return void
	 */
	protected function requestItemUpdate($itemID, int $qty) {
		$this->requestItemAdd($itemID, $qty);
	}

	/**
	 * Send Delete Item Request
	 * @param  string $itemID Item ID
	 * @return void
	 */
	protected function requestItemDelete($itemID) {
		$data = ["ADDTOCART", "ITEMID=$itemID", "QTY=0"];
		$this->sendRequest($data);
	}

	/**
	 * Send Checkout Request
	 * @return void
	 */
	protected function requestCheckout() {
		$dplusdb = $this->wire('modules')->get('DplusDatabaseDpluso')->db_name;
		$this->sendRequest(['PREBILL']);
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
	Response Functions
============================================================= */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('cart', 'response', $response);
	}

	public function getResponse() {
		return $this->wire('session')->getFor('cart', 'response');
	}

	public function deleteResponse() {
		return $this->wire('session')->removeFor('cart', 'response');
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return Item Master Item
	 * @param  string $itemID Item ID
	 * @return ItemMasterItem
	 */
	public function getItem($itemID) {
		return $this->wire('modules')->get('LoaderItem')->load($itemID);
	}
}
