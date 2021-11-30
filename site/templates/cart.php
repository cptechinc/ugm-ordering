<?php
	use Controllers\Cart as Controller;

	$routes = [
		['GET',  '', Controller::class, 'index'],
		['POST',  '', Controller::class, 'handleCRUD'],
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
