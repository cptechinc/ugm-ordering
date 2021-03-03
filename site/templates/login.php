<?php
	$m_login = $modules->get('DplusLogin');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		$m_login->process_input($input, $user);
		if ($values->text('action') != 'logout') {
			$session->redirect($page->url, $http301 = false);
		}
	} elseif ($user->isLoggedInDplus()) {
		$session->redirect($pages->get('/')->url, $http301 = false);
	}


	if ($input->get->create) {
		$page->validateURL = $pages->get('template=validate')->httpUrl;
		$page->body .= $config->twig->render('user/account/forms/create-account.twig', ['page' => $page, 'siteconfig' => $siteconfig, 'response' => $session->response_login]);
		$page->js   .= $config->twig->render('user/account/forms/create-account.js.twig', ['page' => $page]);
	} else {
		$page->body .= $config->twig->render('user/login/form.twig', ['page' => $page, 'siteconfig' => $siteconfig, 'response' => $session->response_login]);
		$page->js   .= $config->twig->render('user/login/js.twig');
	}

	if ($session->response_login) {
		$session->remove('response_login');
	}

	$page->hidetitle = true;
	include __DIR__ . "/blank-page.php";
