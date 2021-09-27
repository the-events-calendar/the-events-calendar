<?php

class Tribe__Events__REST__V1__Documentation__Event_Definition_Provider
	implements Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation() {
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
					'type'        => 'object',
					'description' => __( 'The event featured image details if set', 'the-events-calendar' ),
					'$ref'        => '#/components/schemas/Image',
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
					'type'        => 'object',
					'description' => __( 'The event venue', 'the-events-calendar' ),
					'$ref'        => '#/components/schemas/Venue',
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
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_event_documentation', $documentation );

		return $documentation;
	}
}
