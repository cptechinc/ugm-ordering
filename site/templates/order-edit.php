<?php
	use Controllers\Orders\Order\Edit as Controller;

	$routes = [
		['POST', '', Controller::class, 'handleCRUD'],
		['GET', '{ordn}', Controller::class, 'index'],
		['POST', '{ordn}', Controller::class, 'handleCRUD'],
	];

	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	if ($config->ajax) {
		echo $page->body;
	} else {
		include('./basic-page.php');
	}
