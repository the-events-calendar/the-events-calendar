<?php


class Tribe__Events__Utils__Id_Generator {

	protected static $count = array();

	public static function generate_id( $string, $group = 'default' ) {

		if ( ! ( is_string( $string ) || ( is_int( $string ) ) ) ) {
			throw new InvalidArgumentException( 'First argument must be a string or an int' );
		}

		if ( ! ( is_string( $group ) || is_int( $group ) ) ) {
			throw new InvalidArgumentException( 'Group argument must be a string' );
		}

		if ( ! isset( self::$count[ $group ] ) ) {
			self::$count[ $group ] = 0;
		}

		$out = $string . '-' . self::$count[ $group ];
		self::$count[ $group ] ++;

		return $out;
	}

	public static function reset( $group = null ) {
		if ( empty( $group ) ) {
			self::$count = array();
		} else {
			self::$count[ $group ] = 0;
		}
	}
}
