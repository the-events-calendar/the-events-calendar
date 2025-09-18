<?php
/**
 * Modifies a query that is only fetching the Event post type to integrate with the plugin custom tables..
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\Traits\With_WP_Query_Introspection;
use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use TEC\Events_Pro\Custom_Tables\V1\WP_Query\Modifiers\Events_Not_In_Series_Modifier;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

/**
 * Class Events_Only_Modifier
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
class Events_Only_Modifier extends Base_Modifier {
	use With_WP_Query_Introspection;

	/**
	 * {@inheritDoc}
	 */
	public function applies_to( WP_Query $query = null ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		return $query !== null
		       && ! $query instanceof Custom_Tables_Query
		       && $this->is_query_for_post_type( $query, TEC::POSTTYPE );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 */
	public function hook() {
		add_filter( 'posts_pre_query', [ $this, 'filter_posts_pre_query' ], 100, 2 );
		add_filter( 'tec_events_custom_tables_v1_events_only_modifier_before_get_posts', [ $this, 'filter_ct_query' ]  );
	}

	/**
	 * Applies any necessary alterations on Custom_Tables_Query before it runs get_posts();
	 *
	 * @since 6.0.13
	 *
	 * @param Custom_Tables_Query $query A reference to the query object used to get_posts() for Custom Table queries.
	 *
	 * @return Custom_Tables_Query The modified query object.
	 */
	public function filter_ct_query( Custom_Tables_Query $query ): Custom_Tables_Query {
		// These query vars conflict, and will result in unintended results. See TEC-4695.
		if ( ! isset( $query->query['term'] )
		     && isset( $query->query_vars['term'], $query->query['tax_query'] ) ) {
			unset( $query->query_vars['term'] );
		}

		return $query;
	}

	/**
	 * Pre-fills the query posts with results fetched from the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query|null           $wp_query    A reference to the `WP_Query` instance that is currently running.
	 * @param array<WP_Post|int>|null $posts       The filter input value, it could have already be filtered by other
	 *                                             plugins at this stage.
	 *
	 * @return null|array<WP_Post|int> The filtered value of the posts, injected before the query actually runs.
	 */
	public function filter_posts_pre_query( $posts = null, $wp_query = null ) {
		if ( ! ( $wp_query instanceof WP_Query && $this->is_target_query( $wp_query ) ) ) {
			return $posts;
		}

		// This modifier should stop filtering queries, since this is the one to filter.
		$this->unhook();

		if ( null !== $posts ) {
			// If something already intervened in the filter, then bail and do not touch the query at all.
			return $posts;
		}

		$query = Custom_Tables_Query::from_wp_query( $wp_query );

		/**
		 * Filters the Custom_Tables_Query instance after all values have been initialized, prior to a get_posts() call.
		 * This will give an opportunity to inspect and make any final changes before returning the object.
		 *
		 * @since 6.0.13
		 *
		 * @param Custom_Tables_Query $query    A reference to the Custom Tables Query object that
		 *                                      is applying the filter.
		 */
		$query = apply_filters( 'tec_events_custom_tables_v1_events_only_modifier_before_get_posts', $query );

		$posts = $query->get_posts();

		// Replace two iterations with one.
		$ids = array_reduce(
			$posts,
			static function ( $carry, $post ) {
				$id = (int) ( $post instanceof WP_Post ? $post->ID : $post );
				if ( $id > 0 ) {
					$carry[] = $id;
				}
				return $carry;
			},
			[]
		);

		// It's really important for us to Prime the Post cache so we don't have a ton of queries executed one by one.
		_prime_post_caches( array_unique( $ids ) );

		$this->done_filters[ current_filter() ] = array_map( 'get_post', $posts );

		$this->done();

		/**
		 * Allow filtering just for when applied the Events Only Modifier.
		 *
		 * @since 6.0.2
		 *
		 * @param WP_Query|null           $wp_query    A reference to the `WP_Query` instance that is currently running.
		 * @param array<WP_Post|int>|null $posts       The filter input value, it could have already be filtered by other
		 *                                             plugins at this stage.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_events_only_modifier_filter_posts_pre_query', $posts, $wp_query );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 */
	public function unhook() {
		remove_filter( 'posts_pre_query', [ $this, 'filter_posts_pre_query' ], 100 );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query|null $query A reference to the `WP_Query` instance to check.
	 *
	 * @return bool Whether the `WP_Query` instance is the target one or not.
	 */
	protected function is_target_query( ?WP_Query $query = null ) {
		/**
		 * Filters whether this modifier should modify the query.
		 *
		 * @since 6.0.5
		 *
		 * @param bool          $should_filter Should filter, defaults to rely on internal logic whether to modify.
		 * @param WP_Query      $query         The query object.
		 * @param Base_Modifier $modifier      The modifier being used to filter the query.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_query_modifier_applies_to_query', parent::is_target_query( $query ), $query, $this );
	}
}
