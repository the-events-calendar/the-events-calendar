<?php
/**
 * Controls an Event query connecting it with the Repository and Context.
 *
 * @package Tribe\Events\Views\V2\Query
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2\Query;

use Tribe__Events__Main as TEC;

/**
 * Class Event_Query_Controller
 *
 * @package Tribe\Events\Views\V2\Query
 * @since 4.9.2
 */
class Event_Query_Controller extends Abstract_Query_Controller {

	/**
	 * {@inheritDoc}
	 */
	public function get_filter_name() {
		return 'events';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_post_types() {
		return [
			TEC::POSTTYPE,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function repository() {
		// @todo refine this to handle order depending on the View.
		return tribe_events()->order_by( 'event_date', 'ASC' );
	}

	/**
	 * Parses the query to add/remove properties.
	 *
	 * @since 4.9.11
	 *
	 * @param \WP_Query $query The current WordPress query object.
	 */
	public function parse_query( \WP_Query $query ) {

		/*
		 * If this method fires on the `tribe_events_parse_query` action, then the `Tribe__Events__Query::parse_query`
		 * method should have set a number of `tribe_` flag properties on the query.
		 * These allow us to know if we should suppress v1 query filters for this query or not.
		 */
		$suppress_filters = array_sum(
			[
				// It must be an event query.
				! empty( $query->tribe_is_event_query ),
				// It must be a query only for the events post type.
				empty( $query->tribe_is_multi_posttype ),
				// It must be a query for an archive of events.
				! empty( $query->is_archive ),
			]
		);

		if ( 3 === $suppress_filters || $query->is_embed ) {
			$query->set( 'tribe_suppress_query_filters', true );
		}
	}
}
