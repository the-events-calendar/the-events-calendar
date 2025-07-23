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

use TEC\Common\REST\TEC\V1\Abstracts\Definition;

/**
 * Event definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Event_Definition extends Definition {
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
	 * Returns the priority of the definition.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 1;
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
			'allOf' => [
				[
					'$ref' => '#/components/schemas/TEC_Post_Entity',
				],
				[
					'type'        => 'object',
					'description' => __( 'An event', 'the-events-calendar' ),
					'title'       => 'Event',
					'properties'  => [
						'tribe_events_cat'       => [
							'type'        => 'array',
							'description' => __( 'The terms assigned to the entity in the tribe_events_cat taxonomy', 'the-events-calendar' ),
							'items'       => [
								'type' => 'integer',
							],
							'example'     => [ 1, 5, 12 ],
						],
						'start_date'             => [
							'type'        => 'string',
							'description' => __( 'The start date of the event', 'the-events-calendar' ),
							'format'      => 'date-time',
							'pattern'     => '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$',
							'example'     => '2021-01-01 00:00:00',
						],
						'start_date_utc'         => [
							'type'        => 'string',
							'description' => __( 'The start date of the event in UTC', 'the-events-calendar' ),
							'format'      => 'date-time',
							'pattern'     => '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$',
							'example'     => '2021-01-01 00:00:00',
						],
						'end_date'               => [
							'type'        => 'string',
							'description' => __( 'The end date of the event', 'the-events-calendar' ),
							'format'      => 'date-time',
							'pattern'     => '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$',
							'example'     => '2021-01-01 00:00:00',
						],
						'end_date_utc'           => [
							'type'        => 'string',
							'description' => __( 'The end date of the event in UTC', 'the-events-calendar' ),
							'format'      => 'date-time',
							'pattern'     => '^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$',
							'example'     => '2021-01-01 00:00:00',
						],
						'dates'                  => [
							'$ref' => '#/components/schemas/DateDetails',
						],
						'timezone'               => [
							'type'        => 'string',
							'description' => __( 'The timezone of the event', 'the-events-calendar' ),
							'example'     => 'Europe/Athens',
						],
						'duration'               => [
							'type'        => 'integer',
							'description' => __( 'The duration of the event in seconds', 'the-events-calendar' ),
							'example'     => 3600,
						],
						'multiday'               => [
							'type'        => 'boolean',
							'description' => __( 'Whether the event is multiday', 'the-events-calendar' ),
							'example'     => false,
						],
						'is_past'                => [
							'type'        => 'boolean',
							'description' => __( 'Whether the event is in the past', 'the-events-calendar' ),
							'example'     => false,
						],
						'is_now'                 => [
							'type'        => 'boolean',
							'description' => __( 'Whether the event is happening now', 'the-events-calendar' ),
							'example'     => false,
						],
						'all_day'                => [
							'type'        => 'boolean',
							'description' => __( 'Whether the event is all day', 'the-events-calendar' ),
							'example'     => false,
						],
						'starts_this_week'       => [
							'type'        => 'boolean',
							'nullable'    => true,
							'description' => __( 'Whether the event starts this week', 'the-events-calendar' ),
							'example'     => false,
						],
						'ends_this_week'         => [
							'type'        => 'boolean',
							'nullable'    => true,
							'description' => __( 'Whether the event ends this week', 'the-events-calendar' ),
							'example'     => false,
						],
						'happens_this_week'      => [
							'type'        => 'boolean',
							'nullable'    => true,
							'description' => __( 'Whether the event happens this week', 'the-events-calendar' ),
							'example'     => false,
						],
						'this_week_duration'     => [
							'type'        => 'integer',
							'nullable'    => true,
							'description' => __( 'The duration of the event in the current week', 'the-events-calendar' ),
							'example'     => 3600,
						],
						'displays_on'            => [
							'type'        => 'array',
							'description' => __( 'The days of the week that the event displays on', 'the-events-calendar' ),
							'items'       => [
								'type'    => 'string',
								'format'  => 'date',
								'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}$',
								'example' => '2021-01-01',
							],
						],
						'featured'               => [
							'type'        => 'boolean',
							'description' => __( 'Whether the event is featured', 'the-events-calendar' ),
							'example'     => false,
						],
						'sticky'                 => [
							'type'        => 'boolean',
							'description' => __( 'Whether the event is sticky', 'the-events-calendar' ),
							'example'     => false,
						],
						'cost'                   => [
							'type'        => 'string',
							'description' => __( 'The cost of the event', 'the-events-calendar' ),
							'example'     => '$10',
						],
						'organizer_names'        => [
							'type'        => 'array',
							'description' => __( 'The names of the organizers of the event', 'the-events-calendar' ),
							'items'       => [
								'type' => 'string',
							],
							'example'     => [ 'John Doe', 'Jane Doe' ],
						],
						'organizers'             => [
							'type'        => 'array',
							'description' => __( 'The organizers of the event', 'the-events-calendar' ),
							'items'       => [
								'$ref' => '#/components/schemas/Organizer',
							],
						],
						'venues'                 => [
							'type'        => 'array',
							'description' => __( 'The venues of the event', 'the-events-calendar' ),
							'items'       => [
								'$ref' => '#/components/schemas/Venue',
							],
						],
						'schedule_details'       => [
							'type'        => 'string',
							'description' => __( 'The schedule details of the event', 'the-events-calendar' ),
							'example'     => '10:00 - 12:00',
						],
						'short_schedule_details' => [
							'type'        => 'string',
							'description' => __( 'The schedule details of the event in HTML', 'the-events-calendar' ),
							'example'     => '<p>10:00 - 12:00</p>',
						],
					],
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
