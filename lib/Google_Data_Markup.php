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
		$id  = $post->ID;

		$events_data = array();

		// Index by ID: this will allow filter code to identify the actual event being referred to
		// without injecting an additional property
		$events_data[$id]               = new stdClass();
		$events_data[$id]->{'@context'} = 'http://schema.org';
		$events_data[$id]->{'@type'} = 'Event';
		$events_data[$id]->name         = get_the_title();
		if ( has_post_thumbnail() ) {
			$events_data[$id]->image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
		}
		$events_data[$id]->url       = get_permalink( $post->ID );
		$events_data[$id]->startDate = get_gmt_from_date( tribe_get_start_date( $post, true, Tribe__Events__Date_Utils::DBDATETIMEFORMAT ), 'c' );
		$events_data[$id]->endDate   = get_gmt_from_date( tribe_get_end_date( $post, true, Tribe__Events__Date_Utils::DBDATETIMEFORMAT ), 'c' );
		if ( tribe_has_venue( $id ) ) {
			$events_data[$id]->location          = new stdClass();
			$events_data[$id]->location->{'@type'} = 'Place';
			$events_data[$id]->location->name    = tribe_get_venue( $post->ID );
			$events_data[$id]->location->address = strip_tags( str_replace( "\n", '', tribe_get_full_address( $post->ID ) ) );
		}

		/**
		 * Allows the event data to be modifed by themes and other plugins.
		 *
		 * @param array $events_data objects representing the Google Markup for each event.
		 */
		$events_data = apply_filters( 'tribe_google_event_data', $events_data );

		// Strip the post ID indexing before returning
		$events_data = array_values( $events_data );
		return $events_data;
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
			$html .= str_replace( '\/', '/', json_encode( $events_data ) );
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