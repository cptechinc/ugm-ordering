<?php
	$filter = $modules->get('FilterOrders');
	$filter->init_query($user);
	$filter->filter_query($input);
	$filter->apply_sortby($page);
	$q = $filter->get_query();
	$orders = $q->paginate($input->pageNum, 10);
	
	$page->body .= $config->twig->render('orders/list/list.twig', ['page' => $page, 'orders' => $orders]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);

	include __DIR__ . "/basic-page.php";
