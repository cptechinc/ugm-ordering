<?php
	use Controllers\Api;

	$routes  = [
		'cart' => [
			['GET', '', Api\Cart::class, 'handleCRUD'],
		],
	];

	$router = new Mvc\JsonRouter();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$response = $router->route();

	echo json_encode($response);
