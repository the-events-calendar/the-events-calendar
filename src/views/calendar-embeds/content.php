<?php
/**
 * Content for a calendar embed.
 *
 * @since TBD
 *
 * @version TBD
 *
 * @var int   $calendar_embed_id The ID of the calendar embed.
 * @var array $event_categories  The event categories.
 * @var array $event_tags        The event tags.
 */

use Tribe\Events\Views\V2\View;
use Tribe__Events__Main as TEC;

defined ( 'ABSPATH' ) || exit;

add_filter( 'tribe_events_views_v2_view_display_events_bar', '__return_false' );

add_filter( 'tribe_events_views_v2_view_repository_args' , function( $args ) use ( $event_categories, $event_tags ) {
	if ( ! empty( $event_categories ) ) {
		$args[ TEC::TAXONOMY ] = wp_list_pluck( $event_categories, 'term_id' );
	}

	if ( ! empty( $event_tags ) ) {
		$args['tag__and'] = wp_list_pluck( $event_tags, 'term_id' );
	}

	return $args;
} );

add_filter( 'tribe_repository_events_query_args', function( $args ) {
	if ( ! empty( $args['tax_query'][ TEC::TAXONOMY . '_term_id_in' ] ) ) {
		$args['tax_query'][ TEC::TAXONOMY . '_term_id_in' ]['operator'] = 'AND';
	}

	return $args;
} );

echo View::make( 'month' )->get_html();
