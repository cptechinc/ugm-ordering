<?php
	$page->searchurl = $pages->get('template=items-search')->url;
	
	if ($user->has_item_restrictions()) {
		$groups = implode('|', $user->restricted_itemgroups());
		$itemgroups = $page->children("template=item-group,groupcode=$groups");
	} else {
		$itemgroups = $page->children('template=item-group');
	}
	$page->body .= $config->twig->render('items/search/form.twig', ['page' => $page]);
	$page->body .= $config->twig->render('items/menu.twig', ['page' => $page, 'itemgroups' => $itemgroups]);

	include('./basic-page.php');
