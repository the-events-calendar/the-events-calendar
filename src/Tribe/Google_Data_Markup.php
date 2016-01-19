<?php

/**
 * Handles output of Google structured data markup
 */
abstract class Tribe__Events__Google_Data_Markup {

	protected $filter = 'tribe_google_data';

	/**
	 * Compile the schema.org event data into an array
	 */
	protected function build_data() {
		global $post;
		$id             = $post->ID;
		$data           = array();

		// Index by ID: this will allow filter code to identify the actual event being referred to
		// without injecting an additional property
		$data[ $id ]               = new stdClass();
		$data[ $id ]->{'@context'} = 'http://schema.org';
		$data[ $id ]->{'@type'}    = 'Thing';
		$data[ $id ]->name         = esc_js( get_the_title() );
		$data[ $id ]->description  = esc_js( tribe_events_get_the_excerpt( $post ) );
		if ( has_post_thumbnail() ) {
			$data[ $id ]->image = wp_get_attachment_url( get_post_thumbnail_id( $id ) );
		}
		$data[ $id ]->url = esc_url_raw( get_permalink( $id ) );

		return $data;
	}

	protected function filter_data( $data ) {
		/**
		 * Allows the event data to be modifed by themes and other plugins.
		 *
		 * @param array $data objects representing the Google Markup for each event.
		 */
		$data = apply_filters( $this->filter, $data );

		// Strip the post ID indexing before returning
		$data = array_values( $data );

		return $data;

	}

	/**
	 * puts together the actual html/json javascript block for output
	 * @return string
	 */
	public function script_block() {
		$data = $this->build_data();
		$data = $this->filter_data( $data );

		$html = '';
		if ( ! empty( $data ) ) {
			$html .= '<script type="application/ld+json">';
			$html .= str_replace( '\/', '/', json_encode( $data ) );
			$html .= '</script>';
		}

		return $html;
	}
}
