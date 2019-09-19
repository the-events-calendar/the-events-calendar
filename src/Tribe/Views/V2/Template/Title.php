<?php
/**
 * Handles the manipulation of the template.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Template
 */


namespace Tribe\Events\Views\V2\Template;

/**
 * Class Title
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Template
 */
class Title {

	public function filter_wp_title( $title, $sep = null ) {
		$new_title = tribe_get_events_title( false );
		$the_title = apply_filters( 'tribe_events_title_tag', $new_title, $title, $sep );

		return $the_title;
	}

	public function filter_document_title_parts( array $title = [] ) {
		$sep       = apply_filters( 'document_title_separator', '-' );
		$the_title = $title['title'];

		$new_title = tribe_get_events_title( false );
		$the_title = apply_filters( 'tribe_events_title_tag', $new_title, $the_title, $sep );

		$title['title'] = $the_title;

		return $title;
	}
}
