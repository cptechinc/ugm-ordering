<?php
	use ProcessWire\AlertData;
	$loader = $modules->get('LoaderOrder');
	$html = $modules->get('HtmlWriter');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->ordn) {
		$ordn = SalesOrder::get_paddedordernumber($values->text('ordn'));

		if ($loader->exists($ordn) && $loader->validate_user($ordn, $user)) {
			$docm = $modules->get('DocumentManagementSo');

			if ($input->get->document && $input->get->folder) {
				$folder = $input->get->text('folder');
				$filename = $input->get->text('document');
				$docm->move_document($folder, $filename);

				if ($docm->is_filewebaccessible($filename)) {
					$session->redirect($config->url_webdocs.$filename);
				}
			}
			$order = $loader->load($ordn);
			$page->headline = "Order # $ordn Documents";

			$page->body .= $config->twig->render('orders/order/documents/list.twig', ['page' => $page, 'order' => $order, 'docm' => $docm->get_documents($order->ordernumber)]);
		}  else {
			$page->headline = "Order # $ordn";
			$error = AlertData::newDanger('Order # not found', 'Check the Order # and retry');
			$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
		}
	} else {
		$error = AlertData::newDanger('No Order # Provided', 'Enter an Order # below');
		$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
	}

	include __DIR__ . "/basic-page.php";
