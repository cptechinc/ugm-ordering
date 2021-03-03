<?php
	/**
	 * Items JSON
	 * This template is made for JSON interface for Items
	 * NOTE: the response values can be formatted to be used by Jquery Validate's remote validation method
	 */
	$validator = $modules->get('ValidateItem');
	$dpluspricing = $modules->get('ItemSearchDplus');
	$dpluspricing->sessionID = session_id();
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$response   = '';
	// $returntype = $input->get->return ? $input->get->text('return') : 'jqueryvalidate';

	$itemID = strtoupper($values->text('itemID'));

	if ($input->get->action) {
		switch ($input->get->text('action')) {
			case 'validate-itemid':
				$exists = $validator->validate($itemID);
				$exists = $validator->validate_restriction($itemID, $user);

				$response = array(
					'exists' => $exists,
					'itemid' => $itemID,
				);
				break;
			case 'get-item-description':
				if ($validator->validate($itemID)) {
					$item = ItemMasterItemQuery::create()->findOneByItemid($itemID);
					$dpluspricing->request_one($itemID);
					$pricing = $dpluspricing->get_pricing_item($itemID);
					$response = array(
						'exists' => true,
						'itemid' => $itemID,
						'description1' => $item->description,
						'description2' => $item->description2,
						'uom_sale'     => $item->uom_sale,
						'available'    =>  $pricing->qty
					);
				} else {
					$response = array(
						'exists' => false,
						'itemid' => $itemID
					);
				}
				break;
		}
	}

	echo json_encode($response);
