<?php
	$navpages = array(
		'products' => array(
			'icon' => 'fa-database',
			'page' => $pages->get('template=items-menu'),
			'target' => '_self',
		),
		'cart' => array(
			'icon' => 'fa-shopping-cart',
			'page' => $pages->get('template=cart'),
			'target' => '_self',
		),
		'orders' => array(
			'icon' => 'fa fa-list-alt',
			'page' => $pages->get('template=orders'),
			'target' => '_self',
		),
		'invoices' => array(
			'icon' => 'fa fa-list-alt',
			'page' => $pages->get('template=invoices'),
			'target' => '_self',
		),
	);

	if ($user->hasRole('items-admin')) {
		$navpages['build'] = array(
			'icon' => 'fa fa-sitemap',
			'page' => $pages->get('template=build'),
			'target' => '_blank',
		);
	}

	$page->body = $config->twig->render('user/dashboard/dashboard.twig', ['page' => $page, 'navpages' => $navpages]);

	include('./basic-page.php');
