<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$permissions = $modules->get('ItemPermissions');

	if ($values->action) {
		$permissions->process_input($input);
		$session->redirect($page->url, $http301 = false);
	}

	if ($session->response_permissions) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_permissions]);
		$session->remove('response_permissions');
	}

	$page->body .= $config->twig->render('item-permissions/customer/page.twig', ['customer' => $page, 'permissionsm' => $permissions]);
	$page->js   .= $config->twig->render('item-permissions/customer/js.twig', ['customer' => $page, 'permissionsm' => $permissions]);
	include('./basic-page.php');
