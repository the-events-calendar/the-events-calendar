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

use TEC\Common\REST\TEC\V1\Contracts\Definition_Interface;

/**
 * Organizer definition provider for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Documentation
 */
class Organizer_Definition implements Definition_Interface {
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
				'id'                => [
					'type'        => 'integer',
					'description' => __( 'The organizer WordPress post ID', 'the-events-calendar' ),
				],
				'global_id'         => [
					'type'        => 'string',
					'description' => __( 'The organizer ID used to globally identify in Event Aggregator', 'the-events-calendar' ),
				],
				'global_id_lineage' => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => __( 'An Array containing the lineage of where this organizer comes from, this should not change after the organizer is created.', 'the-events-calendar' ),
				],
				'author'            => [
					'type'        => 'integer',
					'description' => __( 'The organizer author WordPress post ID', 'the-events-calendar' ),
				],
				'date'              => [
					'type'        => 'string',
					'description' => __( 'The organizer creation date in the site time zone', 'the-events-calendar' ),
				],
				'date_utc'          => [
					'type'        => 'string',
					'description' => __( 'The organizer creation date in UTC time', 'the-events-calendar' ),
				],
				'modified'          => [
					'type'        => 'string',
					'description' => __( 'The organizer last modification date in the site time zone', 'the-events-calendar' ),
				],
				'modified_utc'      => [
					'type'        => 'string',
					'description' => __( 'The organizer last modification date in UTC time', 'the-events-calendar' ),
				],
				'status'            => [
					'type'        => 'string',
					'description' => __( 'The organizer status', 'the-events-calendar' ),
				],
				'url'               => [
					'type'        => 'string',
					'description' => __( 'The URL to the organizer page', 'the-events-calendar' ),
				],
				'organizer'         => [
					'type'        => 'string',
					'description' => __( 'The organizer name', 'the-events-calendar' ),
				],
				'description'       => [
					'type'        => 'string',
					'description' => __( 'The organizer long description', 'the-events-calendar' ),
				],
				'excerpt'           => [
					'type'        => 'string',
					'description' => __( 'The organizer short description', 'the-events-calendar' ),
				],
				'slug'              => [
					'type'        => 'string',
					'description' => __( 'The organizer slug', 'the-events-calendar' ),
				],
				'image'             => [
					'$ref' => '#/components/schemas/Image',
				],
				'phone'             => [
					'type'        => 'string',
					'description' => __( 'The organizer phone number', 'the-events-calendar' ),
				],
				'website'           => [
					'type'        => 'string',
					'description' => __( 'The organizer website', 'the-events-calendar' ),
				],
				'email'             => [
					'type'        => 'string',
					'description' => __( 'The organizer email address', 'the-events-calendar' ),
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
