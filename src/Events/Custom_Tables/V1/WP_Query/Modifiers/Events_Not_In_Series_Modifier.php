<?php
/**
 * Modifies a WP Query to only return events that are not related to any Series.
 *
 * This modifier will only apply to queries that have it specified in the query_args
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use WP_Query;

/**
 * Class Events_Series_Relationship_Modifier
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
class Events_Not_In_Series_Modifier extends Base_Modifier {

	const POST_NOT_IN_SERIES = 'post__not_in_series';

	/**
	 * {@inheritdoc}
	 */
	public function hook() {
		add_filter( 'posts_join', [ $this, 'join_on_series_relationships_table' ], 10, 2 );
		add_filter( 'posts_where', [ $this, 'where_event_is_unrelated_to_series' ], 10, 2 );
	}

	/**
	 * {@inheritdoc}
	 */
	public function unhook() {
		remove_filter( 'posts_join', [ $this, 'join_on_series_relationships_table' ] );
		remove_filter( 'posts_where', [ $this, 'where_event_is_unrelated_to_series' ] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function applies_to( WP_Query $query = null ) {
		if ( empty( $query->query_vars[ self::POST_NOT_IN_SERIES ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Adds a new LEFT JOIN clause to the query, relating any wp_post ID to event_post_id in the series relationship
	 * table.
	 *
	 * @since 6.0.0
	 *
	 * @param string   $join  the current join statement
	 * @param WP_Query $query the current query
	 *
	 * @return string
	 */
	public function join_on_series_relationships_table( $join, WP_Query $query ) {
		if ( $query !== $this->query ) {
			return $join;
		}

		remove_filter( 'posts_join', [ $this, 'join_on_series_relationships_table' ] );

		global $wpdb;
		$posts                = $wpdb->posts;
		$series_relationships = Series_Relationships::table_name( true );

		$join .= "\nLEFT JOIN {$series_relationships} ON {$posts}.ID = {$series_relationships}.event_post_id";

		return $join;
	}

	/**
	 * Adds a new WHERE parameter to the query to make sure any sort of filtering happens only in wp_post IDs that do
	 * not exist in the series relationship table.
	 *
	 * @since 6.0.0
	 *
	 * @param string   $where the current where statement
	 * @param WP_Query $query the current query
	 *
	 * @return string
	 */
	public function where_event_is_unrelated_to_series( $where, WP_Query $query ) {
		if ( $query !== $this->query ) {
			return $where;
		}

		remove_filter( 'posts_where', [ $this, 'where_event_is_unrelated_to_series' ] );

		$series_relationships = Series_Relationships::table_name( true );

		$where .= " AND {$series_relationships}.event_post_id IS NULL";

		return $where;
	}
}