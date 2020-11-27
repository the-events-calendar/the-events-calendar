<?php

class Tribe__Events__Utils__Radius {
	/**
	 * get_radii
	 *
	 * Get the possible radius value by miles
	 * or kilometers based on settings
	 *
	 * @static
	 *
	 * @return array|mixed|void
	 */
	public static function get_radii() {
		$unit = tribe_get_option( 'geoloc_default_unit', 'miles' );

		if ( $unit == 'miles' ) {
			$mi_string = _x( '%1$s (mi)', 'X (miles abbreviation)', 'the-events-calendar' );
			$radii = [
				sprintf( $mi_string, 1 )   => 1.6,
				sprintf( $mi_string, 5 )   => 8,
				sprintf( $mi_string, 10 )  => 16.1,
				sprintf( $mi_string, 25 )  => 40.2,
				sprintf( $mi_string, 50 )  => 80.5,
				sprintf( $mi_string, 100 ) => 160.9,
			];
		} else {
			$km_string = _x( '%1$s (km)', 'X (kilometers abbreviation)', 'the-events-calendar' );
			$radii = [
				sprintf( $km_string, 1 )   => 1,
				sprintf( $km_string, 5 )   => 5,
				sprintf( $km_string, 10 )  => 10,
				sprintf( $km_string, 25 )  => 25,
				sprintf( $km_string, 50 )  => 50,
				sprintf( $km_string, 100 ) => 100,
			];
		}

		/**
		 * Filter the default radii options
		 *
		 * @param array $radii Radii options
		 */
		$radii = apply_filters( 'tribe-events-radii', $radii );

		return $radii;
	}

	public static function get_abbreviation() {
		$unit = tribe_get_option( 'geoloc_default_unit', 'miles' );

		if ( $unit == 'miles' ) {
			return _x( 'mi', 'Abbreviation for the miles unit of measure', 'the-events-calendar' );
		} else {
			return _x( 'km', 'Abbreviation for the kilometers unit of measure', 'the-events-calendar' );
		}
	}
}
