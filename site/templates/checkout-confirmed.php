<?php
	$checkoutm = $modules->get('Checkout');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$billing = $checkoutm->get_billing();

	if ($billing->has_error() === false && empty($billing->orders) === false) {
		$session->setFor('order', 'created', $billing->orders);
	}

	if ($billing->has_error() === false && empty($billing->orders)) {
		$q = SalesOrderQuery::create();
		$q->select(SalesOrder::aliasproperty('ordernumber'));
		$q->filterByCustid($billing->custid);
		$q->filterByShiptoid($billing->shiptoid);
		$q->filterByEmail($billing->email);
		$session->setFor('order', 'created', $q->findOne());
	}

	if ($session->response_checkout) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_checkout]);
		$session->remove('response_checkout');
	}

	if ($billing->has_error()) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => DplusResponse::create_error($billing->ermes)]);
	} else {
		$modules->get('Cart')->clear();
	}

	$page->body .= $config->twig->render('checkout/confirmation.twig', ['page' => $page, 'user' => $user, 'billing' => $billing]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include('./basic-page.php');
