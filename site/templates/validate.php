<?php
/**
 * This template is made for Validating Data Inputs
 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
 */
	$response   = '';
	$returntype = $input->get->return ? $input->get->text('return') : 'jqueryvalidate';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	switch ($values->text('action')) {
		case 'validate-custid':
			$validator = $modules->get('ValidateCustomer');
			$custID = $values->text('custID');

			if ($validator->validate($custID)) {
				$response = true;
			} else {
				$response = "Customer $custID not found";
			}
			break;
		case 'get-customer-name':
			$validator = $modules->get('ValidateCustomer');
			$custID = $values->text('custID');

			if ($validator->validate($custID)) {
				$customer = CustomerQuery::create()->findOneByCustid($custID);
				$response = $customer->name;
			} else {
				$response = "$custID not found";
			}
			break;
	}

	$page->body = json_encode($response);

	echo $page->body;
