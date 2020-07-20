<?php
	$m_login = $modules->get('DplusLogin');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		$m_login->process_input($input, $user);
		$session->redirect($page->url, $http301 = false);
	} elseif ($user->isLoggedInDplus()) {
		$session->redirect($pages->get('/')->url, $http301 = false);
	}

	$page->body .= $config->twig->render('user/login/form.twig', ['page' => $page, 'siteconfig' => $siteconfig, 'response' => $session->response_login]);
	$page->js   .= $config->twig->render('user/login/js.twig');
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	if ($session->response_login) {
		$session->remove('response_login');
	}

	$page->hidetitle = true;
	include __DIR__ . "/blank-page.php";
