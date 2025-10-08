<?php
/**
 * Provides integration with Views V2.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */

namespace TEC\Events\Custom_Tables\V1\Views\V2;

use Exception;
use stdClass;
use Tribe__Customizer as Customizer;
use Tribe__Customizer__Section as Customizer_Section;
use TEC\Common\Contracts\Service_Provider;


/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */
class Provider extends Service_Provider {


	/**
	 * Registers the handlers and modifiers required to make the plugin correctly work
	 * with Views v2.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( Customizer_Compatibility::class, Customizer_Compatibility::class );

		add_filter( 'tribe_events_views_v2_by_day_view_day_results', [
			$this,
			'prepare_by_day_view_day_results',
		], 10, 3 );

		// When Pro is inactive, hydrate posts with their selected occurrence dates
		if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
			add_filter( 'tec_events_custom_tables_v1_custom_tables_query_hydrate_posts', [
				$this,
				'hydrate_posts_with_occurrence_dates',
			], 10, 2 );
		}

		// Handle Customizer styles.
		add_filter( 'tribe_customizer_global_elements_css_template', [
			$this,
			'update_global_customizer_styles',
		], 10, 3 );
	}

	/**
	 * Returns the prepared `By_Day_View` day results.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,stdClass>|null $day_results  Either the prepared day results, or `null`
	 *                                               if the day results have not been prepared yet.
	 * @param array<int>               $event_ids    A list of the Event post IDs that should be prepared.
	 * @param object|null              $view         The view instance (Day_View, Month_View, etc.).
	 *
	 * @return array<int,stdClass> The prepared day results.
	 */
	public function prepare_by_day_view_day_results( array $day_results = null, array $event_ids = [], $view = null ) {
		// Extract date range from the view context if available
		$start_date = null;
		$end_date   = null;

		if ( $view && method_exists( $view, 'get_context' ) ) {
			$context = $view->get_context();

			// For Month View / Week View, get the date range
			if ( isset( $context->start_date ) && isset( $context->end_date ) ) {
				$start_date = $context->start_date;
				$end_date   = $context->end_date;
			}
		}

		return $this->container->make( By_Day_View_Compatibility::class )
		                       ->prepare_day_results( $event_ids, $start_date, $end_date );
	}

	/**
	 * Filters the Global Elements section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 6.0.0
	 *
	 * @param Customizer_Section $section      The Global Elements section.
	 * @param Customizer         $customizer   The current Customizer instance.
	 * @param string             $css_template The CSS template, as produced by the Global Elements.
	 *
	 * @return string The filtered CSS template.
	 *
	 * @throws Exception If the Color util is built incorrectly.
	 *
	 */
	public function update_global_customizer_styles( $css_template, $section, $customizer ) {
		return $this->container->make( Customizer_Compatibility::class )
		                       ->update_global_customizer_styles( $css_template, $section, $customizer );;
	}

	/**
	 * Hydrates event posts with their selected occurrence dates when Pro is inactive.
	 *
	 * When Pro is not active, each recurring event is limited to a single occurrence by the query.
	 * This method updates the post meta cache so that tribe_get_event() returns the correct
	 * dates for the selected occurrence, not the original event's first occurrence dates.
	 *
	 * @since TBD
	 *
	 * @param array $posts The posts returned by Custom_Tables_Query.
	 * @param object $query The Custom_Tables_Query instance.
	 *
	 * @return array The posts with updated meta cache.
	 */
	public function hydrate_posts_with_occurrence_dates( $posts, $query ) {
		// Safety check: if Pro is active, don't hydrate (Pro handles its own occurrence logic)
		if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
			return $posts;
		}

		if ( empty( $posts ) ) {
			return $posts;
		}

		// Get the date conditions that were used in the query
		$date_conditions = $query->get( '_tec_occurrence_date_conditions', '' );
		$used_simple_limit = $query->get( '_tec_used_simple_limit', false );

		// If the query used simple limit (no date conditions from meta_query),
		// the main query's WHERE clause (date_overlaps, ends_after, etc.) already
		// selected the right occurrence. We don't need to re-fetch or hydrate.
		if ( empty( $date_conditions ) && $used_simple_limit ) {
			return $posts;
		}

		foreach ( $posts as $post ) {
			// During found_posts, $post is a post ID (integer)
			// During posts_results, $post is a WP_Post object or stdClass
			if ( $post instanceof \WP_Post ) {
				$post_id = $post->ID;
			} elseif ( is_object( $post ) && isset( $post->ID ) ) {
				$post_id = (int) $post->ID;
			} else {
				$post_id = (int) $post;
			}

			if ( empty( $post_id ) ) {
				continue;
			}

			// Get the occurrence using the same date filtering as the query
			// This ensures we get the right occurrence (e.g., first future one for List View)
			global $wpdb;
			$table = \TEC\Events\Custom_Tables\V1\Tables\Occurrences::table_name( true );

			// Build the query with date conditions
			// Note: date_conditions already includes quotes and is safe to use directly (not via prepare)
			$occ_date_conditions = str_replace( 'occ.', 'o.', $date_conditions );

			// Prepare the post_id part first
			$post_id_condition = $wpdb->prepare( 'o.post_id = %d', $post_id );

			// Build the full SQL (date conditions are already escaped from meta_query processing)
			$sql = "SELECT o.*
				FROM {$table} o
				WHERE {$post_id_condition}
				{$occ_date_conditions}
				ORDER BY o.start_date ASC, o.occurrence_id ASC
				LIMIT 1";

			$occurrence_row = $wpdb->get_row( $sql );

			if ( ! $occurrence_row ) {
				continue;
			}

			// Update the post meta cache with this occurrence's dates
			$cache_key = $post_id;
			$meta_cache = wp_cache_get( $cache_key, 'post_meta' );

			if ( false === $meta_cache ) {
				$meta_cache = update_meta_cache( 'post', [ $post_id ] );
				$meta_cache = isset( $meta_cache[ $post_id ] ) ? $meta_cache[ $post_id ] : [];
			}

			// Override the date fields with the selected occurrence's dates
			$meta_cache['_EventStartDate'] = [ $occurrence_row->start_date ];
			$meta_cache['_EventEndDate'] = [ $occurrence_row->end_date ];
			$meta_cache['_EventStartDateUTC'] = [ $occurrence_row->start_date_utc ];
			$meta_cache['_EventEndDateUTC'] = [ $occurrence_row->end_date_utc ];

			wp_cache_set( $cache_key, $meta_cache, 'post_meta' );
		}

		return $posts;
	}
}
