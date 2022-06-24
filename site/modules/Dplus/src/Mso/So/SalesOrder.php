<?php namespace Dplus\Mso\So;
// Dplus Models
use SalesOrderQuery, SalesOrder as SoModel;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\User;

/**
 * So\SalesOrders
 *
 * Wrapper for Querying Sales Order Database
 */
class SalesOrder extends WireData {
	private static $instance;

	/** @return self */
	public static function instance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return SalesOrderQuery
	 */
	public function query() {
		return SalesOrderQuery::create();
	}

	/**
	 * Return Query filtered by Order #
	 * @return SalesOrderQuery
	 */
	public function queryOrdernumber($ordn) {
		$q = $this->query();
		$q->filterByOrdernumber($ordn);
		return $q;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return if Order Exists
	 * @param  string $ordn
	 * @return bool
	 */
	public function exists($ordn) {
		$q = $this->queryOrdernumber($ordn);
		return boolval($q->count());
	}

	/**
	 * Return Order
	 * @param  string $ordn
	 * @return SoModel
	 */
	public function order($ordn) {
		return $this->queryOrdernumber($ordn)->findOne();
	}

	/**
	 * Return Order Customer ID
	 * @param  string $ordn
	 * @return string
	 */
	public function custid($ordn) {
		$q = $this->queryOrdernumber($ordn);
		$q->select(SoModel::aliasproperty('custid'));
		return $q->findOne();
	}

	/**
	 * Return if Sales Order is Editable
	 * @param  string $ordn Order #
	 * @return bool
	 */
	public function isEditable($ordn) {
		$q = $this->queryOrdernumber($ordn);
		$q->select([SoModel::aliasproperty('status'), SoModel::aliasproperty('heldby')]);
		$order = $q->findOne();

		if (empty($order)) {
			return false;
		}
		if ($order->status != SoModel::STATUS_NEW) {
			return false;
		}
		if ($order->heldby != '') {
			return false;
		}
		return true;
	}

	/**
	 * Returns if User can view order according to their custid
	 * @param  string $ordn Order #
	 * @param  User   $user User
	 * @return bool
	 */
	public function orderUser($ordn, User $user) {
		$q = $this->queryOrdernumber($ordn);
		$q->filterByCustid($user->custid);
		return boolval($q->count());
	}
}
