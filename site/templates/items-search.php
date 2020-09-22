<?php
	$search = $modules->get('ItemSearch');
	$search->send_request_all();
	$dpluspricing = $modules->get('ItemSearchDplus');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	$q = $values->text('q');
	$page->headline = $values->q ? "Searching for '$q'" : $page->title;

	$search->set_search($q);
	$items = $search->find($input->pageNum, 12);
	$page->carturl = $pages->get('template=cart')->url;

	if ($config->ajax) {
		$page->searchurl = $page->url;
		$page->body .= $config->twig->render('items/search/form.twig', ['page' => $page, 'q' => $q]);
		$page->body .= $config->twig->render('items/search/results-ajax.twig', ['page' => $page, 'q' => $q, 'items' => $items, 'dpluspricing' => $dpluspricing]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount' => $search->count()]);
		echo $page->body;
	} else {
		$page->searchurl = $page->url;
		$page->body .= $config->twig->render('items/search/form.twig', ['page' => $page, 'q' => $q]);
		$page->body .= $config->twig->render('items/search/results.twig', ['page' => $page, 'q' => $q, 'items' => $items, 'dpluspricing' => $dpluspricing]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount' => $search->count()]);
		include('./basic-page.php');
	}
