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
use TEC\Events\Calendar_Embeds\Calendar_Embeds;

defined ( 'ABSPATH' ) || exit;

$is_embed = is_embed();

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

// Make use of the views cache by correctly setting up the context which is being used for cache key generation.
add_filter( 'tribe_context_pre_post_tag', static fn() => $event_tags ? wp_list_pluck( $event_tags, 'term_id' ) : null );
add_filter( 'tribe_context_pre_' . TEC::TAXONOMY, static fn() => $event_categories ? wp_list_pluck( $event_categories, 'term_id' ) : null );

add_filter( 'tribe_events_views_v2_url_query_args', static function( $args ) use ( $calendar_embed_id, $is_embed ) {
	unset(
		$args[ TEC::TAXONOMY],
		$args['tag']
	);
	$args['name'] = get_post_field( 'post_name', $calendar_embed_id );
	$args['post_type'] = Calendar_Embeds::POSTTYPE;
	$args['eventDisplay'] = 'month';
	$args['embed'] = $is_embed;
	return $args;
} );

add_filter( 'tribe_events_views_v2_view_prev_url', static function ( $url, $canonical ) use ( $calendar_embed_id, $is_embed ): string {
	if ( ! $canonical ) {
		return $url;
	}

	if ( ! get_option( 'permalink_structure' ) ) {
		return $url;
	}

	$url_parts = wp_parse_url( $url );
	$args = wp_parse_args( $url_parts['query'] ?? '' );
	$date = $args['eventDate'] ?? null;
	if ( ! $date ) {
		return $url;
	}

	if ( ! $is_embed ) {
		return trailingslashit( get_the_permalink( $calendar_embed_id ) ) . $date . '/';
	}

	return trailingslashit( get_post_embed_url( $calendar_embed_id ) ) . $date . '/';
}, 10, 2 );

add_filter( 'tribe_events_views_v2_view_next_url', static function ( $url, $canonical ) use ( $calendar_embed_id, $is_embed ): string {
	if ( ! $canonical ) {
		return $url;
	}

	if ( ! get_option( 'permalink_structure' ) ) {
		return $url;
	}

	$url_parts = wp_parse_url( $url );
	$args = wp_parse_args( $url_parts['query'] ?? '' );
	$date = $args['eventDate'] ?? null;
	if ( ! $date ) {
		return $url;
	}

	if ( ! $is_embed ) {
		return trailingslashit( get_the_permalink( $calendar_embed_id ) ) . $date . '/';
	}

	return trailingslashit( get_post_embed_url( $calendar_embed_id ) ) . $date . '/';
}, 10, 2 );

echo View::make( 'month' )->get_html();
