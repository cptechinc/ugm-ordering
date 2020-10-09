<?php
	if ($user->has_itemgroup()) {
		$dpluspricing = $modules->get('ItemSearchDplus');
		$page->searchurl = $pages->get('template=items-search')->url;
		$page->carturl = $pages->get('template=cart')->url;
		$items = $page->get_stocked_items();

		$page->body .= $config->twig->render('items/search/form.twig', ['page' => $page]);
		$page->body .= $config->twig->render('items/list.twig', ['page' => $page, 'items' => $items, 'dpluspricing' => $dpluspricing]);

		include('./basic-page.php');
	} else {
		throw new Wire404Exception();
	}
