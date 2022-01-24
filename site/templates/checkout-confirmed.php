<?php
	use Controllers\Checkout\Confirmed as Controller;

	$routes = [
		['GET',  '', Controller::class, 'index'],
		['POST',  '', Controller::class, 'handleCRUD'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	include __DIR__ . "/basic-page.php";
