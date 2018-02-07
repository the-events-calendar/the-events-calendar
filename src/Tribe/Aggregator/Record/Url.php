<?php


class Tribe__Events__Aggregator__Record__Url extends Tribe__Events__Aggregator__Record__Abstract {

	/**
	 * @var string
	 */
	public $origin = 'url';

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Other URL', 'the-events-calendar' );
	}

	/**
	 * Matches which other origin this source url might be
	 *
	 * @since  TBD
	 *
	 * @param  string $source Which source we are testing against
	 *
	 * @return string|bool
	 */
	public static function match_source_origin( $source ) {
		$origins = array(
			'eventbrite' => Tribe__Events__Aggregator__Record__Eventbrite::get_source_regexp(),
			'facebook' => Tribe__Events__Aggregator__Record__Facebook::get_source_regexp(),
			'meetup' => Tribe__Events__Aggregator__Record__Meetup::get_source_regexp(),
		);

		if ( ! is_string( $source  ) ) {
			return false;
		}

		foreach ( $origins as $origin => $regexp ) {
			// Skip if we don't match the source to any of the URLs
			if ( ! preg_match( '/' . $regexp . '/', $source ) ) {
				continue;
			}

			return $origin;
		}

		return false;
	}

}