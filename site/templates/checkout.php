<?php
	$checkoutm = $modules->get('Checkout');
	$cart   = $modules->get('Cart');
	$qnotes = $modules->get('QnotesCart');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$billing = $checkoutm->get_billing();

	if ($values->action) {
		$checkoutm->process_input($input);
		$url = $values->text('action') == 'update-billing' ? $pages->get('template=checkout-confirm')->url : $page->url;
		$session->redirect($url);
	}

	if ($session->response_checkout) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_checkout]);
		$session->remove('response_checkout');
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($billing->has_error()) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => DplusResponse::create_error($billing->ermes)]);
	}

	$page->body .= $config->twig->render('checkout/checkout.twig', ['page' => $page, 'checkoutm' => $checkoutm, 'user' => $user, 'qnotes' => $qnotes, 'cart' => $cart]);
	$page->js   .= $config->twig->render('checkout/js.twig', ['page' => $page, 'checkoutm' => $checkoutm]);
	$page->body .= $config->twig->render('cart/notes/modal.twig', ['page' => $page, 'qnotes' => $qnotes]);
	$page->js   .= $config->twig->render('cart/notes/js.twig', ['page' => $page]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include('./basic-page.php');
