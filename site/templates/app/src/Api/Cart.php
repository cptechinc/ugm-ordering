<?php namespace Controllers\Api;
// Dplus Ecomm
use Dplus\Ecomm\Cart as CartCRUD;

class Cart extends Base {
	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text']);
		$response = ['error' => false, 'message' => ''];

		if (empty($data->action)) {
			$response['error'] = true;
			$response['message'] = 'No Action Provided';
			return $response;
		}

		$cart = CartCRUD::getInstance();
		$success = $cart->processInput(self::pw('input'));
		$responseCart = $cart->getResponse();
		$response['error'] = boolval($responseCart->hasError());
		$response['message'] = $responseCart->message;
		return $response;
	}
}
