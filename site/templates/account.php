<?php
	$m_login = $modules->get('DplusLogin');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		$m_login->process_input($input, $user);
		$session->redirect($page->url, $http301 = false);
	}

	if ($session->response_login) {
		$page->body .= $config->twig->render('util/dplus-response.twig', ['response' => $session->response_login]);
		$session->remove('response_login');
	}

	if ($user->needs_setup_recovery()) {
		$page->body .= $config->twig->render('user/account/forms/setup-recovery.twig', ['page' => $page, 'user' => $user]);
		$page->js   .= $config->twig->render('user/account/forms/setup-recovery.js.twig');
	} else {
		$page->body .= $config->twig->render('user/account/forms/update-password.twig', ['page' => $page, 'user' => $user]);
		$page->js   .= $config->twig->render('user/account/forms/update-password.js.twig');
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	if ($session->response_login) {
		$session->remove('response_login');
	}

	include __DIR__ . "/basic-page.php";
