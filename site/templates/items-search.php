<?php
	use Controllers\Items;

	$routes = [
		['GET',  '', Items\Search::class, 'index'],
		['GET',  'page{pagenbr:\d+}/', Items\Search::class, 'index'],
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
