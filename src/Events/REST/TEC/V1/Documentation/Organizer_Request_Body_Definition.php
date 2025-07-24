<?php
/**
 * Organizer request body definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;

/**
 * Organizer request body definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Organizer_Request_Body_Definition extends Definition {
	/**
	 * Returns the type of the definition.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Organizer_Request_Body';
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
					'title'       => 'Organizer Request Body',
					'description' => __( 'The request body for the organizer endpoint', 'the-events-calendar' ),
					'properties'  => [
						'phone'   => [
							'type'        => 'string',
							'description' => __( 'The organizer\'s phone number', 'the-events-calendar' ),
							'format'      => 'tel',
						],
						'website' => [
							'type'        => 'string',
							'description' => __( 'The organizer\'s website', 'the-events-calendar' ),
							'format'      => 'uri',
						],
						'email'   => [
							'type'        => 'string',
							'description' => __( 'The organizer\'s email address', 'the-events-calendar' ),
							'format'      => 'email',
						],
					],
				],
			],
		];
	}
}
