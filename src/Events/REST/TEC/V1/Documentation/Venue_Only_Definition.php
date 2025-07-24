<?php
/**
 * Venue only definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;

/**
 * Venue only definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Venue_Only_Definition extends Definition {

	/**
	 * Returns the type of the definition.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Venue_Only';
	}

	/**
	 * Returns the priority of the definition.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 3;
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
		];

		/**
		 * Filters the Swagger documentation generated for a venue in the TEC REST API.
		 *
		 * @since TBD
		 *
		 * @param array              $documentation An associative PHP array in the format supported by Swagger.
		 * @param Venue_Definition $this          The Venue_Definition instance.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tec_rest_swagger_' . $this->get_type() . '_definition', $documentation, $this );
	}
}
