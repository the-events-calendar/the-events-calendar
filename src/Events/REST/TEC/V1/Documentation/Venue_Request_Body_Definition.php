<?php
/**
 * Venue request body definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;

/**
 * Venue request body definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Venue_Request_Body_Definition extends Definition {
	/**
	 * Returns the type of the definition.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Venue_Request_Body';
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
					'type'        => 'object',
					'title'       => 'Venue Request Body',
					'description' => __( 'The request body for the venue endpoint', 'the-events-calendar' ),
					'properties'  => [
						'address'        => [
							'type'        => 'string',
							'description' => __( 'The venue address', 'the-events-calendar' ),
						],
						'country'        => [
							'type'        => 'string',
							'description' => __( 'The venue country', 'the-events-calendar' ),
						],
						'city'           => [
							'type'        => 'string',
							'description' => __( 'The venue city', 'the-events-calendar' ),
						],
						'state_province' => [
							'type'        => 'string',
							'description' => __( 'The venue state/province', 'the-events-calendar' ),
						],
						'state'          => [
							'type'        => 'string',
							'description' => __( 'The venue state', 'the-events-calendar' ),
						],
						'province'       => [
							'type'        => 'string',
							'description' => __( 'The venue province', 'the-events-calendar' ),
						],
						'zip'            => [
							'type'        => 'string',
							'description' => __( 'The venue zip code', 'the-events-calendar' ),
						],
						'phone'          => [
							'type'        => 'string',
							'description' => __( 'The venue phone number', 'the-events-calendar' ),
						],
						'website'        => [
							'type'        => 'string',
							'description' => __( 'The venue website', 'the-events-calendar' ),
							'format'      => 'uri',
						],
					],
				],
			],
		];
	}
}
