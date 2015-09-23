<?php


class Tribe__Events__Utils__Id_Generator {

	protected static $count;

	public static function generate_id( $string ) {
		$out = $string . '-' . self::$count;
		self::$count ++;

		return $out;
	}
}
