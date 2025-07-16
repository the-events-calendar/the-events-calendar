<?php
/**
 * Event definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Contracts\Definition_Interface;

/**
 * Event definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Event_Definition implements Definition_Interface {
	/**
	 * Returns the type of the definition.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Event';
	}

	/**
	 * Returns an array in the format used by Swagger.
	 *
	 * @since TBD
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation(): array {
		$documentation = [
			'type'       => 'object',
			'properties' => [
				'id'                     => [
					'type'        => 'integer',
					'description' => __( 'The event WordPress post ID', 'the-events-calendar' ),
				],
				'global_id'              => [
					'type'        => 'string',
					'description' => __( 'The event ID used to globally identify in Event Aggregator', 'the-events-calendar' ),
				],
				'global_id_lineage'      => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => __( 'An Array containing the lineage of where this event comes from, this should not change after the event is created.', 'the-events-calendar' ),
				],
				'author'                 => [
					'type'        => 'integer',
					'description' => __( 'The event author WordPress post ID', 'the-events-calendar' ),
				],
				'date'                   => [
					'type'        => 'string',
					'description' => __( 'The event creation date in the site time zone', 'the-events-calendar' ),
				],
				'date_utc'               => [
					'type'        => 'string',
					'description' => __( 'The event creation date in UTC time', 'the-events-calendar' ),
				],
				'modified'               => [
					'type'        => 'string',
					'description' => __( 'The event last modification date in the site time zone', 'the-events-calendar' ),
				],
				'modified_utc'           => [
					'type'        => 'string',
					'description' => __( 'The event last modification date in UTC time', 'the-events-calendar' ),
				],
				'status'                 => [
					'type'        => 'string',
					'description' => __( 'The event status', 'the-events-calendar' ),
				],
				'url'                    => [
					'type'        => 'string',
					'description' => __( 'The URL to the event page', 'the-events-calendar' ),
				],
				'rest_url'               => [
					'type'        => 'string',
					'description' => __( 'The TEC REST API link to fetch this event', 'the-events-calendar' ),
				],
				'title'                  => [
					'type'        => 'string',
					'description' => __( 'The event name', 'the-events-calendar' ),
				],
				'description'            => [
					'type'        => 'string',
					'description' => __( 'The event long description', 'the-events-calendar' ),
				],
				'excerpt'                => [
					'type'        => 'string',
					'description' => __( 'The event short description', 'the-events-calendar' ),
				],
				'slug'                   => [
					'type'        => 'string',
					'description' => __( 'The event slug', 'the-events-calendar' ),
				],
				'image'                  => [
					'$ref' => '#/components/schemas/Image',
				],
				'all_day'                => [
					'type'        => 'boolean',
					'description' => __( 'Whether or not this event is an all day Event', 'the-events-calendar' ),
				],
				'start_date'             => [
					'type'        => 'string',
					'description' => __( 'The event start date in the event or site time zone', 'the-events-calendar' ),
				],
				'start_date_details'     => [
					'type'        => 'array',
					'description' => __( 'An array of each component of the event start date', 'the-events-calendar' ),
					'items'       => [ '$ref' => '#/components/schemas/DateDetails' ],
				],
				'end_date'               => [
					'type'        => 'string',
					'description' => __( 'The event end date in the event or site time zone', 'the-events-calendar' ),
				],
				'end_date_details'       => [
					'type'        => 'array',
					'description' => __( 'An array of each component of the event end date', 'the-events-calendar' ),
					'items'       => [ '$ref' => '#/components/schemas/DateDetails' ],
				],
				'utc_start_date'         => [
					'type'        => 'string',
					'description' => __( 'The event start date in UTC time', 'the-events-calendar' ),
				],
				'utc_start_date_details' => [
					'type'        => 'array',
					'description' => __( 'An array of each component of the event start date in UTC time', 'the-events-calendar' ),
					'items'       => [ '$ref' => '#/components/schemas/DateDetails' ],
				],
				'utc_end_date'           => [
					'type'        => 'string',
					'description' => __( 'The event end date in UTC time', 'the-events-calendar' ),
				],
				'utc_end_date_details'   => [
					'type'        => 'array',
					'description' => __( 'An array of each component of the event end date in UTC time', 'the-events-calendar' ),
					'items'       => [ '$ref' => '#/components/schemas/DateDetails' ],
				],
				'timezone'               => [
					'type'        => 'string',
					'description' => __( 'The event time zone string', 'the-events-calendar' ),
				],
				'timezone_abbr'          => [
					'type'        => 'string',
					'description' => __( 'The abbreviated event time zone string', 'the-events-calendar' ),
				],
				'cost'                   => [
					'type'        => 'string',
					'description' => __( 'The event cost including the currency symbol', 'the-events-calendar' ),
				],
				'cost_details'           => [
					'type'        => 'array',
					'description' => __( 'The event cost details', 'the-events-calendar' ),
					'items'       => [ '$ref' => '#/components/schemas/CostDetails' ],
				],
				'website'                => [
					'type'        => 'string',
					'description' => __( 'The event website URL', 'the-events-calendar' ),
				],
				'show_map'               => [
					'type'        => 'boolean',
					'description' => __( 'Whether the map should be shown for the event or not', 'the-events-calendar' ),
				],
				'show_map_link'          => [
					'type'        => 'boolean',
					'description' => __( 'Whether the map link should be shown for the event or not', 'the-events-calendar' ),
				],
				'hide_from_listings'     => [
					'type'        => 'boolean',
					'description' => __( 'Whether an event should be hidden from the calendar view or not', 'the-events-calendar' ),
				],
				'sticky'                 => [
					'type'        => 'boolean',
					'description' => __( 'Whether an event is sticky in the calendar view or not', 'the-events-calendar' ),
				],
				'featured'               => [
					'type'        => 'boolean',
					'description' => __( 'Whether the event is featured in the calendar or not', 'the-events-calendar' ),
				],
				'categories'             => [
					'type'        => 'array',
					'description' => __( 'The event categories', 'the-events-calendar' ),
					'items'       => [ '$ref' => '#/components/schemas/Term' ],
				],
				'tags'                   => [
					'type'        => 'array',
					'description' => __( 'The event tags', 'the-events-calendar' ),
					'items'       => [ '$ref' => '#/components/schemas/Term' ],
				],
				'venue'                  => [
					'$ref' => '#/components/schemas/Venue',
				],
				'organizer'              => [
					'type'        => 'array',
					'description' => __( 'The event organizers', 'the-events-calendar' ),
					'items'       => [ '$ref' => '#/components/schemas/Organizer' ],
				],
			],
		];

		/**
		 * Filters the Swagger documentation generated for an event in the TEC REST API.
		 *
		 * @since TBD
		 *
		 * @param array              $documentation An associative PHP array in the format supported by Swagger.
		 * @param Event_Definition $this          The Event_Definition instance.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tec_rest_swagger_' . $this->get_type() . '_definition', $documentation, $this );
	}
}
