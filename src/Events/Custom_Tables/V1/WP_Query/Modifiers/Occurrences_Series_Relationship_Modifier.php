<?php
/**
 * Modifies a Custom Tables Query to only return Occurrences that are related to a set of Series.
 *
 * This modifier will only apply, and work, on custom tables queries.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use TEC\Events_Pro\Custom_Tables\V1\Tables\Series_Relationships;
use Tribe__Events__Main as TEC;
use WP_Query;

/**
 * Class Occurrences_Series_Relationship_Modifier
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
class Occurrences_Series_Relationship_Modifier extends Base_Modifier {
	use With_Series_Normalization;

	/**
	 * {@inheritdoc}
	 */
	public function hook() {
		add_filter( 'posts_join', [ $this, 'join_on_series_relationships_table' ], 10, 2 );
		add_filter( 'posts_where', [ $this, 'where_event_is_related_to_series' ], 10, 2 );
	}

	/**
	 * {@inheritdoc}
	 */
	public function unhook() {
		remove_filter( 'posts_join', [ $this, 'join_on_series_relationships_table' ] );
		remove_filter( 'posts_where', [ $this, 'where_event_is_related_to_series' ] );
	}

	/*
	 * {@inheritdoc}
	 */
	public function applies_to( WP_Query $query = null ) {
		return $query instanceof Custom_Tables_Query
		       && array_filter( (array) $query->get( 'post_type' ) ) === [ TEC::POSTTYPE ]
		       && count( (array) $query->get( 'related_series', [] ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function join_on_series_relationships_table( $join, WP_Query $query ) {
		if ( $query !== $this->query ) {
			return $join;
		}

		$related_series_ids = $this->normalize_query_series_ids($query);

		if ( ! count( $related_series_ids ) ) {
			return $join;
		}

		global $wpdb;
		$posts                = $wpdb->posts;
		$series_relationships = Series_Relationships::table_name( true );

		$join .= "\nJOIN {$series_relationships} ON {$posts}.ID = {$series_relationships}.event_post_id";

		return $join;
	}

	/**
	 * {@inheritdoc}
	 */
	public function where_event_is_related_to_series( $where, WP_Query $query ) {
		if ( $query !== $this->query ) {
			return $where;
		}

		$related_series_ids = $this->normalize_query_series_ids($query);

		if ( ! count( $related_series_ids ) ) {
			return $where;
		}

		$series_relationships = Series_Relationships::table_name( true );
		$related_series       = implode( ',', $related_series_ids );


		$where .= " AND {$series_relationships}.series_post_id IN ({$related_series})";

		return $where;
	}
}
