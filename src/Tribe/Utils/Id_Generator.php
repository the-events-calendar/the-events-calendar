<?php


class Tribe__Events__Utils__Id_Generator {

	protected static $count = array();

	public static function generate_id( $string, $group = 'default' ) {
		if ( ! isset( self::$count[ $group ] ) ) {
			self::$count[ $group ] = 0;
		}

		$out = $string . '-' . self::$count[ $group ];
		self::$count[ $group ] ++;

		return $out;
	}
}
