<?php
	use Controllers\Api;

	$routes  = [
		'cart' => [
			['POST', '', Api\Cart::class, 'handleCRUD'],
		],
		'items' => [
			'validate' => [
				['GET', 'itemid', Api\Items::class, 'validateItemid'],
			],
			'item' => [
				['GET', '', Api\Items::class, 'getItem'],
			]
		]
	];

	$router = new Mvc\Routers\Json();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$response = $router->route();

	echo json_encode($response);
