<?php
/**
 * Event request body definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;

/**
 * Event request body definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Event_Request_Body_Definition extends Definition {
	/**
	 * Returns the type of the definition.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Event_Request_Body';
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
	 * Returns the documentation for the definition.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_documentation(): array {
		return [
			'allOf' => [
				[
					'$ref' => '#/components/schemas/TEC_Post_Entity_Request_Body',
				],
				[
					'title'       => 'Event Request Body',
					'description' => __( 'The request body for the event endpoint', 'the-events-calendar' ),
					'type'        => 'object',
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
						'all_day'                => [
							'type'        => 'boolean',
							'description' => __( 'Whether the event is all day', 'the-events-calendar' ),
							'example'     => false,
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
					],
				],
			],
		];
	}
}
