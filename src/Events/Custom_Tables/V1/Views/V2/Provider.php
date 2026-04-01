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
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Customizer as Customizer;
use Tribe__Customizer__Section as Customizer_Section;
use TEC\Common\Contracts\Service_Provider;
use WP_Post;


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
	 * @since TBD Add filter to hydrate posts with next upcoming occurrence dates when Pro is inactive.
	 */
	public function register() {
		$this->container->singleton( Customizer_Compatibility::class, Customizer_Compatibility::class );

		add_filter( 'tribe_events_views_v2_by_day_view_day_results', [
			$this,
			'prepare_by_day_view_day_results',
		], 10, 2 );

		// Hydrate posts with next upcoming occurrence dates when Pro is inactive.
		add_filter(
			'tec_events_custom_tables_v1_custom_tables_query_hydrate_posts',
			[
				$this,
				'hydrate_posts_with_upcoming_occurrence_dates',
			],
			10,
			2
		);

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
	 *
	 * @return array<int,stdClass> The prepared day results.
	 */
	public function prepare_by_day_view_day_results( array $day_results = null, array $event_ids = [] ) {
		return $this->container->make( By_Day_View_Compatibility::class )
		                       ->prepare_day_results( $event_ids );
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
	 * Updates the post meta cache with the next upcoming occurrence dates when Pro is inactive.
	 *
	 * When Pro is deactivated but recurring event data remains, the post meta still reflects the
	 * original first occurrence dates. This method overrides the cached meta with the next upcoming
	 * occurrence's dates so List View and other views display the correct date.
	 *
	 * @since TBD
	 *
	 * @param array $posts The posts returned by the Custom Tables Query.
	 *
	 * @return array The posts, unchanged. Side effect: post meta cache is updated.
	 */
	public function hydrate_posts_with_upcoming_occurrence_dates( $posts ) {
		if ( ! $posts || class_exists( 'Tribe__Events__Pro__Main' ) ) {
			return $posts;
		}

		// Collect post IDs that haven't been hydrated yet.
		$post_ids = [];
		foreach ( $posts as $post ) {
			$post_id = $post instanceof WP_Post ? $post->ID : (int) $post;

			if ( ! $post_id || wp_cache_get( $post_id, 'tec_occurrence_hydrated' ) ) {
				continue;
			}

			$post_ids[] = $post_id;
		}

		if ( ! $post_ids ) {
			return $posts;
		}

		// Batch-fetch all occurrences for these posts in a single query.
		$all_occurrences = Occurrence::where_in( 'post_id', $post_ids )->all();

		// Group occurrences by post_id.
		$by_post = [];
		foreach ( $all_occurrences as $occurrence ) {
			$by_post[ $occurrence->post_id ][] = $occurrence;
		}

		$now = current_time( 'mysql' );

		foreach ( $post_ids as $post_id ) {
			wp_cache_set( $post_id, true, 'tec_occurrence_hydrated' );

			$occurrences = $by_post[ $post_id ] ?? [];

			// Only hydrate events with multiple occurrences (leftover from Pro).
			if ( count( $occurrences ) <= 1 ) {
				continue;
			}

			// Sort by start_date and pick the next upcoming, or fall back to latest.
			usort( $occurrences, static fn( $a, $b ) => $a->start_date <=> $b->start_date );

			$upcoming = array_filter( $occurrences, static fn( $occ ) => $occ->start_date >= $now );

			$occurrence = ! empty( $upcoming ) ? reset( $upcoming ) : end( $occurrences );

			// Update the post meta cache with this occurrence's dates.
			$meta_cache = wp_cache_get( $post_id, 'post_meta' );

			if ( false === $meta_cache ) {
				update_meta_cache( 'post', [ $post_id ] );
				$meta_cache = wp_cache_get( $post_id, 'post_meta' );
			}

			if ( ! is_array( $meta_cache ) ) {
				continue;
			}

			$meta_cache['_EventStartDate']    = [ $occurrence->start_date ];
			$meta_cache['_EventEndDate']      = [ $occurrence->end_date ];
			$meta_cache['_EventStartDateUTC'] = [ $occurrence->start_date_utc ];
			$meta_cache['_EventEndDateUTC']   = [ $occurrence->end_date_utc ];

			wp_cache_set( $post_id, $meta_cache, 'post_meta' );
		}

		return $posts;
	}
}
