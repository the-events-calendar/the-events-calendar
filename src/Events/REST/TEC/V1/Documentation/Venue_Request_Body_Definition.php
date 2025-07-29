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
use TEC\Common\REST\TEC\V1\Collections\PropertiesCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\URI;

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
		$properties = new PropertiesCollection();

		$properties[] = (
			new Text(
				'address',
				fn() => __( 'The venue address', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new Text(
				'country',
				fn() => __( 'The venue country', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new Text(
				'city',
				fn() => __( 'The venue city', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new Text(
				'state_province',
				fn() => __( 'The venue state/province', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new Text(
				'state',
				fn() => __( 'The venue state', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new Text(
				'province',
				fn() => __( 'The venue province', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new Text(
				'zip',
				fn() => __( 'The venue zip code', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new Text(
				'phone',
				fn() => __( 'The venue phone number', 'the-events-calendar' ),
			)
		);

		$properties[] = (
			new URI(
				'website',
				fn() => __( 'The venue website', 'the-events-calendar' ),
			)
		);

		/**
		 * Filters the Swagger documentation generated for a venue request body in the TEC REST API.
		 *
		 * @since TBD
		 *
		 * @param array                $documentation An associative PHP array in the format supported by Swagger.
		 * @param PropertiesCollection $properties    The properties collection.
		 *
		 * @return array
		 */
		return apply_filters(
			'tec_events_rest_v1_venue_request_body_definition',
			[
				'allOf' => [
					[
						'$ref' => '#/components/schemas/TEC_Post_Entity_Request_Body',
					],
					[
						'type'        => 'object',
						'title'       => 'Venue Request Body',
						'description' => __( 'The request body for the venue endpoint', 'the-events-calendar' ),
						'properties'  => $properties,
					],
				],
			],
			$properties
		);
	}
}
