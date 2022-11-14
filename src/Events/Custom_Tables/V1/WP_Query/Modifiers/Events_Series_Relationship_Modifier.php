<?php
/**
 * Modifies a WP Query to only return events that are related to a set of Series.
 *
 * This modifier will NOT apply, and work, on custom tables queries.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use Tribe__Events__Main as TEC;
use WP_Query;

/**
 * Class Events_Series_Relationship_Modifier
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
class Events_Series_Relationship_Modifier extends Occurrences_Series_Relationship_Modifier {

	/**
	 * {@inheritdoc}
	 */
	public function applies_to( WP_Query $query = null ) {
		return ! $query instanceof Custom_Tables_Query
		       && array_filter( (array) $query->get( 'post_type' ) ) === [ TEC::POSTTYPE ]
		       && count( (array) $query->get( 'related_series', [] ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function join_on_series_relationships_table( $join, $query ) {
		if ( $query !== $this->query ) {
			return $join;
		}

		remove_filter( 'posts_join', [ $this, 'join_on_series_relationships_table' ] );

		return parent::join_on_series_relationships_table( $join, $query );
	}

	/**
	 * {@inheritdoc}
	 */
	public function where_event_is_related_to_series( $where, $query ) {
		if ( $query !== $this->query ) {
			return $where;
		}

		remove_filter( 'posts_where', [ $this, 'where_event_is_related_to_series' ] );

		return parent::where_event_is_related_to_series( $where, $query );
	}
}
