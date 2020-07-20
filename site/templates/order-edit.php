<?php
	$editm = $modules->get('EditOrder');
	$qnotes = $modules->get('QnotesSalesOrder');
	$validator = $modules->get('ValidateOrder');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($session->response_edit) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_edit]);
		$session->remove('response_edit');
	}

	if ($values->ordn) {
		$ordn = $values->text('ordn');

		if ($values->action) {
			$editm->process_input($input);
			$url = $values->text('action') == 'exit-order' ? $page->url_vieworder($ordn) : $page->url_editorder($ordn);
			$session->redirect($url);
		}

		if ($validator->order($ordn) && $validator->order_user($ordn, $user) && $editm->can_edit_order($ordn)) {
			if ($editm->has_order_header($ordn)) {
				$page->headline = "Editing Order # $ordn";
				$orderedit = $editm->get_order_header($ordn);

				if ($orderedit->has_error()) {
					$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => DplusResponse::create_error($orderedit->error)]);
				}
				$qnotes->get_notes_form_array($orderedit->ordernumber, 0, $qnotes->get_default_forms_string());

				$page->body .= $config->twig->render('orders/order/edit/order.twig', ['page' => $page, 'orderedit' => $orderedit, 'editm' => $editm, 'qnotes' => $qnotes]);
				$page->js   .= $config->twig->render('orders/order/edit/js.twig', ['editm' => $editm]);
				$page->body .= $config->twig->render('orders/order/qnotes/modal.twig', ['page' => $page, 'ordn' => $ordn, 'qnotes' => $qnotes]);
				$page->js   .= $config->twig->render('orders/order/qnotes/js.twig', ['page' => $page, 'ordn' => $ordn, 'qnotes' => $qnotes]);
				$page->js   .= $config->twig->render('cart/lookup.js.twig', ['page' => $page]);
			} else {
				if ($values->retry) {
					$error = AlertData::newDanger('Order Cannot be edited', 'The order cannot be loaded to be edited');
					$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
				} else {
					$editm->request_order_edit($ordn);
					$page->fullURL->query->set('retry', 'true');
					$session->redirect($page->fullURL->getUrl());
				}
			}
		} else {
			$page->headline = "Editing Order # $ordn";

			if (!$validator->order($ordn) && !$validator->order_user($ordn, $user)) {
				$error = AlertData::newDanger('Order # not found', 'Check the Order # and retry');
			} else {
				$error = AlertData::newDanger('Order Cannot be edited', 'The order is in uneditable state');
			}
			$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
		}
	} else {
		$error = AlertData::newDanger('No Order # Provided', 'Enter an Order # below');
		$page->body .= $config->twig->render('orders/order/error.twig', ['page' => $page, 'error' => $error]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include('./basic-page.php');
