<?php
	$navpages = array(
		'products' => array(
			'icon' => 'fa-database',
			'page' => $pages->get('template=items-menu')
		),
		'cart' => array(
			'icon' => 'fa-shopping-cart',
			'page' => $pages->get('template=cart')
		),
		'orders' => array(
			'icon' => 'fa fa-list-alt',
			'page' => $pages->get('template=orders')
		),
		'invoices' => array(
			'icon' => 'fa fa-list-alt',
			'page' => $pages->get('template=invoices')
		),
	);
	$page->body = $config->twig->render('user/dashboard/dashboard.twig', ['page' => $page, 'navpages' => $navpages]);

	include('./basic-page.php');
