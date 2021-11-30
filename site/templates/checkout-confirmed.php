<?php
	$checkoutm = $modules->get('Checkout');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$billing = $checkoutm->get_billing();
	if (empty($billing)) {
		$session->redirect($pages->get('template=cart')->url, $http301 = false);
	}
	if ($billing->has_error() === false && empty($billing->orders) === false) {
		$session->setFor('order', 'created', $billing->orders);
	}

	if ($session->response_checkout) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_checkout]);
		$session->remove('response_checkout');
	}

	if ($billing->has_error()) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => DplusResponse::create_error($billing->ermes)]);
	}

	$page->body .= $config->twig->render('checkout/confirmation.twig', ['page' => $page, 'user' => $user, 'billing' => $billing]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include('./basic-page.php');
