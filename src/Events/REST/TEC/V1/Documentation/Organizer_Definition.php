<?php
/**
 * Organizer definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;

/**
 * Organizer definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Organizer_Definition extends Definition {
	/**
	 * Returns the type of the definition.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Organizer';
	}

	/**
	 * Returns the priority of the definition.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 2;
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
					'description' => __( 'An organizer', 'the-events-calendar' ),
					'title'       => 'Organizer',
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

		/**
		 * Filters the Swagger documentation generated for an organizer in the TEC REST API.
		 *
		 * @since TBD
		 *
		 * @param array              $documentation An associative PHP array in the format supported by Swagger.
		 * @param Organizer_Definition $this          The Organizer_Definition instance.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tec_rest_swagger_' . $this->get_type() . '_definition', $documentation, $this );
	}
}
