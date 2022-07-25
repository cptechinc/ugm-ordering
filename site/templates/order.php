<?php
	use ProcessWire\AlertData;
	$loader = $modules->get('LoaderOrder');
	$validator = $modules->get('ValidateOrder');
	$editm     = $modules->get('EditOrder');
	$html = $modules->get('HtmlWriter');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->ordn) {
		$ordn = SalesOrder::get_paddedordernumber($values->text('ordn'));

		if ($loader->exists($ordn) && $loader->validate_user($ordn, $user)) {
			$qnotes = $modules->get('QnotesSales');

			if ($values->action) {
				$qnotes->process_input($input);
				$session->redirect($page->url."?ordn=".$ordn);
			}

			if ($session->response_qnote) {
				$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_qnote]);
				$session->remove('response_qnote');
			}

			$docm = $modules->get('DocumentManagementSo');
			$order = $loader->load($ordn);
			$page->headline = "Order # $ordn";

			if ($loader->validator->invoice($ordn)) {
				$page->show_breadcrumbs = false;
				$page->body .= $config->twig->render('orders/invoices/breadcrumbs.twig', ['page' => $page]);
			}

			$page->body .= $config->twig->render('orders/order/order.twig', ['page' => $page, 'order' => $order, 'docm' => $docm, 'qnotes' => $qnotes, 'editm' => $editm]);
			$page->body .= $config->twig->render('orders/order/qnotes/modal.twig', ['page' => $page, 'ordn' => $ordn, 'qnotes' => $qnotes]);
			$page->js   .= $config->twig->render('orders/order/qnotes/js.twig', ['page' => $page, 'ordn' => $ordn, 'qnotes' => $qnotes]);
		} else {
			$page->headline = "Order # $ordn";
			$error = AlertData::newDanger('Order # not found', 'Check the Order # and retry');
			$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
		}
	} else {
		$error = AlertData::newDanger('No Order # Provided', 'Enter an Order # below');
		$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
	}

	include __DIR__ . "/basic-page.php";
