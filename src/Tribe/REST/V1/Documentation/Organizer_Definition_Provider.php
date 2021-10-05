<?php

class Tribe__Events__REST__V1__Documentation__Organizer_Definition_Provider
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
					'type'        => 'string',
					'description' => __( 'The event featured image details if set', 'the-events-calendar' ),
					'$ref'        => '#/components/schemas/Image',
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
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_organizer_documentation', $documentation );

		return $documentation;
	}
}
