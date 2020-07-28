<?php
	$cart = $modules->get('Cart');
	$qnotes = $modules->get('QnotesCart');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		$cart->process_input($input);
		$url = $values->page ? $values->text('page') : $page->url;
		$session->redirect($url);
	}

	if ($session->response_cart) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_cart]);
		$session->remove('response_cart');
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	$page->body .= $config->twig->render('cart/cart.twig', ['page' => $page, 'user' => $user, 'cart' => $cart, 'qnotes' => $qnotes]);
	$page->body .= $config->twig->render('cart/notes/modal.twig', ['page' => $page, 'qnotes' => $qnotes]);
	$page->js   .= $config->twig->render('cart/js.twig', ['page' => $page, 'cart' => $cart]);
	$page->js   .= $config->twig->render('cart/lookup.js.twig', ['page' => $page, 'cart' => $cart]);
	$page->js   .= $config->twig->render('cart/notes/js.twig', ['page' => $page, 'cart' => $cart]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include('./basic-page.php');
