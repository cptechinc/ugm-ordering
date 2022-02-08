<?php
	$m = $modules->get('DplusLogin');
	$m->forceLogouts();

	$cart = Dplus\Ecomm\Cart::getInstance();
	// $cart->clearAll();
