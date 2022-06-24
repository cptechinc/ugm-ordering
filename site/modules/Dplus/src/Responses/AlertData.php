<?php namespace Dplus\Responses;

use ProcessWire\WireData;

/**
 * AlertData
 * Container for Data to create Twig Alerts
 *
 * @property string type    Bootstrap alert-* class info|success|warning|danger
 * @property string title   Alert Title
 * @property string icon    Icon Class to use (font-awesome)
 * @property string message Message to display in body
 */
class AlertData extends WireData {
	public function __construct() {
		$this->type    = 'info';
		$this->title   = '';
		$this->icon    = '';
		$this->message = '';
	}

	public static function newDanger($title = '', $message = '') {
		$alert = new AlertData();
		$alert->type = 'danger';
		$alert->icon = 'fa fa-warning fa-2x';
		$alert->title = $title;
		$alert->message = $message;
		return $alert;
	}
}
