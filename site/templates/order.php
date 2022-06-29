<?php
	use Controllers\Orders\Order as Controller;

	$routes = [
		['GET', '', Controller::class, 'index'],
		['GET', '{ordn}', Controller::class, 'index'],
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
