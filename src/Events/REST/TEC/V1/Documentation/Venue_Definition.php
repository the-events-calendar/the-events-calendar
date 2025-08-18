<?php
/**
 * Venue definition provider for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Abstracts\Definition;
use TEC\Common\REST\TEC\V1\Collections\PropertiesCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\URI;

/**
 * Venue definition provider for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Venue_Definition extends Definition {

	/**
	 * Returns the type of the definition.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'Venue';
	}

	/**
	 * Returns the priority of the definition.
	 *
	 * @since 6.15.0
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 3;
	}

	/**
	 * Returns an array in the format used by Swagger.
	 *
	 * @since 6.15.0
	 *
	 * @return array An array description of a Swagger supported component.
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

		$properties[] = (
			new URI(
				'directions_link',
				fn() => __( 'The venue directions link', 'the-events-calendar' ),
			)
		);

		$documentation = [
			'allOf' => [
				[
					'$ref' => '#/components/schemas/TEC_Post_Entity',
				],
				[
					'title'       => 'Venue',
					'description' => __( 'A venue', 'the-events-calendar' ),
					'type'        => 'object',
					'properties'  => $properties,
				],
			],
		];

		$type = strtolower( $this->get_type() );

		/**
		 * Filters the Swagger documentation generated for a venue in the TEC REST API.
		 *
		 * @since 6.15.0
		 *
		 * @param array            $documentation An associative PHP array in the format supported by Swagger.
		 * @param Venue_Definition $this          The Venue_Definition instance.
		 *
		 * @return array
		 */
		$documentation = (array) apply_filters( "tec_rest_swagger_{$type}_definition", $documentation, $this );

		/**
		 * Filters the Swagger documentation generated for a definition in the TEC REST API.
		 *
		 * @since 6.15.0
		 *
		 * @param array            $documentation An associative PHP array in the format supported by Swagger.
		 * @param Venue_Definition $this          The Venue_Definition instance.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tec_rest_swagger_definition', $documentation, $this );
	}
}
