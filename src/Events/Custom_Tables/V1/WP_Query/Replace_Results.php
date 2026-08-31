<?php
/**
 * Class to replace the results from a list of queries.
 *
 * @since 6.0.0
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query;


use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Traits\With_WP_Query_Introspection;
use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

use function get_post;

/**
 * Class Replace_Results
 *
 * @since   6.0.0
 *
 * @package src\WP_Query
 */
class Replace_Results {
	use With_WP_Query_Introspection;

	/**
	 * Instance to the Provisional Post class.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post provisional_post
	 */
	private $provisional_post;

	/**
	 * Replace_Results constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param  \TEC\Events\Custom_Tables\V1\Models\Provisional_Post $provisional_post
	 */
	public function __construct( Provisional_Post $provisional_post ) {
		$this->provisional_post = $provisional_post;
	}

	/**
	 * If the query was not able to find results for specific occurrence IDs we hydrate the cache
	 * before the results are returned to the next WP_Query call.
	 *
	 * @since 6.0.0
	 * @since 7.8.0 Made $wp_query explicitly nullable.
	 *
	 * @param                 $posts
	 * @param  WP_Query|null  $wp_query
	 *
	 * @return mixed
	 */
	public function replace( $posts, ?WP_Query $wp_query = null ) {
		// This should only apply to posts.
		if ( ! $this->is_query_for_post_type( $wp_query, TEC::POSTTYPE ) ) {
			return $posts;
		}

		// Prevent to operate on not array type of.
		if ( ! is_array( $posts ) ) {
			return $posts;
		}

		// Collect and hydrate the Occurrences part of the results.
		$occurrences = [];
		foreach ( $posts as $index => $post ) {
			$post_id = $post instanceof WP_Post ? $post->ID : (int) $post;

			if ( empty( $post_id ) ) {
				// Maybe we got a `SELECT wp_tec_occurrences.occurrence_id ...` result.
				$post_id = ( (object) $post )->occurrence_id ?? 0;
			}

			if ( $this->provisional_post->is_provisional_post_id( $post_id ) ) {
				$occurrences[ $post_id ] = $index;
			}
		}

		$this->provisional_post->hydrate_caches( array_keys( $occurrences ) );

		// Replace the posts with the occurrences instead.
		foreach ( $occurrences as $occurrence_id => $index ) {
			// The Occurrence cache has been hydrated, we can now get the post.
			$occurrence_post = get_post( $occurrence_id );

			// When querying for Single Events, just return the Single Event real post ID.
			if ( isset( $occurrence_post->_tec_occurrence ) ) {
				if ( ! empty( $occurrence_post->_tec_occurrence->has_recurrence ) ) {
					// Recurring Event.
					$posts[ $index ] = $occurrence_post;
				} else {
					// Single Event.
					$posts[ $index ] = get_post( $occurrence_post->_tec_occurrence->post_id );
				}
			}
		}

		if ( $wp_query !== null && $wp_query->get( 'fields' ) === 'ids' ) {
			return array_filter(
				array_map(
					static function ( $post ) {
						$post = get_post( $post );

						return $post instanceof WP_Post ? $post->ID : 0;
					},
					$posts
				)
			);
		}

		return $posts;
	}

	/**
	 * Hydrates the Custom Tables Query post results early.
	 *
	 * @since 6.0.3
	 *
	 * @param array               $query_posts The posts returned by the query.
	 * @param Custom_Tables_Query $query       The Custom Tables Query instance.
	 *
	 * @return array The posts returned by the query, hydrated.
	 */
	public function hydrate_query_posts( array $query_posts, Custom_Tables_Query $query ): array {
		$occurrence_ids = $this->pluck_occurrences_ids( $query_posts );
		$this->provisional_post->hydrate_caches( $occurrence_ids );

		switch ( $query->get( 'fields' ) ) {
			case 'ids':
				return $occurrence_ids;
			case 'id=>parent':
				$mapped                    = [];
				$unprovided_occurrence_ids = array_map( [
					tribe( ID_Generator::class ),
					'unprovide_id'
				], $occurrence_ids );
				foreach ( Occurrence::where_in( 'occurrence_id', $unprovided_occurrence_ids )->all() as $occurrence ) {
					$mapped[ $occurrence->provisional_id ] = $occurrence->post_id;
				}

				return $mapped;
			case '':
			default:
				return $this->replace( $occurrence_ids, $query );
		}
	}

	/**
	 * Plucks the Occurrence IDs from the query posts taking different input formats into account.
	 *
	 * @since 6.0.4
	 *
	 * @param array $query_posts The posts returned by the query.
	 *
	 * @return array<int> An array of Occurrence IDs.
	 */
	protected function pluck_occurrences_ids( array $query_posts ): array {
		$occurrence_ids = [];
		foreach ( $query_posts as $query_post ) {
			if ( is_numeric( $query_post ) ) {
				$occurrence_ids[] = (int) $query_post;
				continue;
			}
			if ( $query_post instanceof WP_Post ) {
				if ( ! empty( $query_post->_tec_occurrence->provisional_id ) ) {
					$occurrence_ids[] = (int) $query_post->_tec_occurrence->provisional_id;
				} else {
					$occurrence_ids[] = $query_post->ID;
				}
			} else {
				$array_result     = (array) $query_post;
				$occurrence_ids[] = $array_result['occurrence_id'] ?? (int) $query_post;
			}
		}

		return $occurrence_ids;
	}
}
