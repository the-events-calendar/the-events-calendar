<?php

/**
 * Handles output of Google structured data markup
 */
class Tribe__Events__Google_Data_Markup {

	/**
	 * @var $instance
	 */
	private static $instance = null;

	/**
	 * Compile the schema.org event data into an array
	 */
	private function build_data() {

		global $post;

		$events_data = array();

		$events_data[0]               = new stdClass();
		$events_data[0]->{'@context'} = 'http://schema.org';
		$events_data[0]->name         = get_the_title();
		if ( has_post_thumbnail() ) {
			$events_data[0]->image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
		}
		$events_data[0]->url       = get_the_permalink( $post->ID );
		$events_data[0]->startDate = get_gmt_from_date( tribe_get_start_date( $post, true, TribeDateUtils::DBDATETIMEFORMAT ), 'c' );
		$events_data[0]->endDate   = get_gmt_from_date( tribe_get_end_date( $post, true, TribeDateUtils::DBDATETIMEFORMAT ), 'c' );
		if ( tribe_has_venue( $post->ID ) ) {
			$events_data[0]->location          = new stdClass();
			$events_data[0]->location->name    = tribe_get_venue( $post->ID );
			$events_data[0]->location->address = strip_tags( str_replace( "\n", '', tribe_get_full_address( $post->ID ) ) );
		}

		return apply_filters( 'tribe_google_event_data', $events_data );
	}

	/**
	 * puts together the actual html/json javascript block for output
	 * @return string
	 */
	public function script_block() {
		$events_data = $this->build_data();
		$html        = '';
		if ( ! empty( $events_data ) ) {
			$html .= '<script type="application/ld+json">';
			$html .= stripslashes( json_encode( $events_data ) );
			$html .= '</script>';
		}

		return $html;
	}


	/**
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}