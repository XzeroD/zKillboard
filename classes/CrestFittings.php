<?php

class CrestFittings {
	public static function saveFitting($killID) {
		global $mdb, $crestServer;
	
		$killmail = $mdb->findDoc('rawmails', ['killID' => (int) $killID]);
		$victim = $killmail['victim'];
		header('Content-Type: application/json');
		$export = [];
		$charName = Info::getInfoField('characterID', (int) @$victim['character']['id'], 'name') . "'s ";
		$shipName = Info::getInfoField('shipTypeID', $victim['shipType']['id'], 'name');
		$export['name'] = "$charName's $shipName";
		$export['description'] = "Imported from https://zkillboard.com/kill/$killID/";
		$export['ship'] = ['id' => $victim['shipType']['id']];
		$export['ship']['name'] = Info::getInfoField('typeID', $victim['shipType']['id'], 'name');
		$export['ship']['href'] = "$crestServer/inventory/types/" . $victim['shipType']['id'] . "/";

		$items = $victim['items'];
		$export['items'] = [];
		foreach ($items as $item) {
			$flag = $item['flag'];
			if (!self::isFit($flag)) continue;
			$nextItem = [];	
			$nextItem['flag'] = $flag;
			$nextItem['quantity'] = @$item['quantityDropped'] + @$item['quantityDestroyed'];
			$nextItem['type']['id'] = $item['itemType']['id'];
			$nextItem['type']['name'] = Info::getInfoField('typeID', $item['itemType']['id'], 'name');
			$nextItem['type']['href'] = "$crestServer/inventory/types/" . $item['itemType']['id'] . "/";
			$export['items'][] = $nextItem;
		}
		if (sizeof($export['items']) == 0) return ['message' => 'Cannot save this fit, no hardware.'];

		$decode = CrestSSO::crestGet("https://crest-tq.eveonline.com/decode/");
		if (isset($decode['message'])) return $decode;
		$character = CrestSSO::crestGet($decode['character']['href']);
		$result = CrestSSO::crestPost($character['fittings']['href'], $export);
		if ($result['httpCode'] == 201) return ['message' => "Fit successfully saved to your character's fittings."];
		return $result;
	}

	private static $infernoFlags = array(
			//5 => array(5,5), // Cargo
			12 => array(27, 34), // Highs
			13 => array(19, 26), // Mids
			11 => array(11, 18), // Lows
			87 => array(87, 87), // Drones
			//133 => array(133, 133), // Fuel Bay
			2663 => array(92, 98), // Rigs
			3772 => array(125, 132), // Subs
			);

	public static function isFit($flag) {
		foreach (self::$infernoFlags as $key=>$range) {
			if ($flag >= $range[0] && $flag <= $range[1]) return true;
		}
		return false;
	}
}
