<?php
	use Controllers\Maintenance as Controller;

	$routes = [
		['GET', '', Controller::class, 'index'],
	];

	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	include __DIR__ . "/blank-page.php";
