<?php
	use Controllers\Items;

	$routes = [
		['GET',  '', Items\Item::class, 'index'],
		'lots' => [
			['GET',  '{lot}/', Items\Item\Lots::class, 'index'],
		]
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	if ($config->ajax) {
		echo $page->body;
	} else {
		include('./basic-page.php');
	}
