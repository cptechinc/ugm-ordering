<?php
	$page->searchurl = $pages->get('template=items-search')->url;
	$page->body .= $config->twig->render('items/search/form.twig', ['page' => $page]);
	$page->body .= $config->twig->render('items/menu.twig', ['page' => $page, 'itemgroups' => $page->children('template=item-group')]);

	include('./basic-page.php');
