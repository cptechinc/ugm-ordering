<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$permissions = $modules->get('ItemPermissions');
	$permissions->delete_unrestricted_customers();
	$html = $modules->get('HtmlWriter');

	if ($values->action) {
		$permissions->process_input($input);
		if ($values->text('action') == 'add-customer') {
			$custID = $values->text('custID');

			if ($permissions->customer_permission_exists($custID)) {
				$url = $page->restrictions_customerURL($values->text('custID'));
			} else {
			$url = $page->url;
			}
		} else {
			$url = $page->url;
		}
		$session->redirect($url, $http301 = false);
	}

	if ($session->response_permissions) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_permissions]);
		$session->remove('response_permissions');
	}

	$q = $input->get->text('q');
	$filter = $modules->get('FilterCustomers');

	$filter->init_query();
	$filter->active();
	$filter->search($q);

	if ($input->get->add) {
		$page->headline = "Choose Customer";
	} else {
		$filter->custid($permissions->custids_restricted());
	}

	$filter->apply_sortby($page);
	$query = $filter->get_query();
	$customers = $query->paginate($input->pageNum, 10);
	$count = $input->get->add ? $customers->getNbResults() : $customers->count();
	$page->body .= $config->twig->render('item-permissions/customers/page.twig', ['page' => $page, 'permissions' => $permissions, 'count' => $count, 'q' => $q, 'customers' => $customers]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount' => $customers->getNbResults()]);

	include('./basic-page.php');
