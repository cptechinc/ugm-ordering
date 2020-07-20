<?php
	use ProcessWire\AlertData;
	$validator = $modules->get('ValidateOrder');
	$html = $modules->get('HtmlWriter');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->ordn) {
		$ordn = SalesOrder::get_paddedordernumber($values->text('ordn'));

		if ($validator->order($ordn) && $validator->order_user($ordn, $user)) {
			$session->redirect($pages->get('template=order')->url."?ordn=$ordn", $http301 = false);
		} else if ($validator->invoice($ordn) && $validator->invoice_user($ordn, $user)) {
			$docm = $modules->get('DocumentManagementSo');
			$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
			$page->headline = "Order # $ordn";
			$page->body .= $config->twig->render('orders/order/order.twig', ['page' => $page, 'order' => $order, 'docm' => $docm]);
		} else {
			$error = AlertData::newDanger('Order # not found', 'Check the Order # and retry');
			$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
		}
	} else {
		$error = AlertData::newDanger('No Order # Provided', 'Enter an Order # below');
		$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
	}

	include __DIR__ . "/basic-page.php";
