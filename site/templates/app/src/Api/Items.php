<?php namespace Controllers\Api;
// Dplus Min
use Dplus\Min\Itm;

class Items extends Base {
	public static function getItem($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$itm = Itm::getInstance();

		if (empty($data->itemID) || $itm->exists($data->itemID) === false) {
			return false;
		}
		$item = $itm->item($data->itemID);
		$json = [
			'itemid'       => $data->itemID,
			'description1' => $item->description,
			'description2' => $item->description2,
			'uom' => [
				'sale' => $item->uom_sale,
			]
		];
		return $json;
	}
}
