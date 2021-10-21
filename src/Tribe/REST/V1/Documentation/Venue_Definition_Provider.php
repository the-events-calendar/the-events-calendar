<?php

class Tribe__Events__REST__V1__Documentation__Venue_Definition_Provider
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
					'description' => __( 'The venue WordPress post ID', 'the-events-calendar' ),
				],
				'global_id'         => [
					'type'        => 'string',
					'description' => __( 'The venue ID used to globally identify in Event Aggregator', 'the-events-calendar' ),
				],
				'global_id_lineage' => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => __( 'An Array containing the lineage of where this organizer comes from, this should not change after the organizer is created.', 'the-events-calendar' ),
				],
				'author'            => [
					'type'        => 'integer',
					'description' => __( 'The venue author WordPress post ID', 'the-events-calendar' ),
				],
				'date'              => [
					'type'        => 'string',
					'description' => __( 'The venue creation date in the site time zone', 'the-events-calendar' ),
				],
				'date_utc'          => [
					'type'        => 'string',
					'description' => __( 'The venue creation date in UTC time', 'the-events-calendar' ),
				],
				'modified'          => [
					'type'        => 'string',
					'description' => __( 'The venue last modification date in the site time zone', 'the-events-calendar' ),
				],
				'modified_utc'      => [
					'type'        => 'string',
					'description' => __( 'The venue last modification date in UTC time', 'the-events-calendar' ),
				],
				'status'            => [
					'type'        => 'string',
					'description' => __( 'The venue status', 'the-events-calendar' ),
				],
				'url'               => [
					'type'        => 'string',
					'description' => __( 'The URL to the venue page', 'the-events-calendar' ),
				],
				'venue'             => [
					'type'        => 'string',
					'description' => __( 'The venue name', 'the-events-calendar' ),
				],
				'description'       => [
					'type'        => 'string',
					'description' => __( 'The venue long description', 'the-events-calendar' ),
				],
				'excerpt'           => [
					'type'        => 'string',
					'description' => __( 'The venue short description', 'the-events-calendar' ),
				],
				'slug'              => [
					'type'        => 'string',
					'description' => __( 'The venue slug', 'the-events-calendar' ),
				],
				'image'             => [
					'type'        => 'string',
					'description' => __( 'The event featured image details if set', 'the-events-calendar' ),
					'$ref'        => '#/components/schemas/Image',
				],
				'show_map'          => [
					'type'        => 'boolean',
					'description' => __( 'Whether the map should be shown for the venue or not', 'the-events-calendar' ),
				],
				'show_map_link'     => [
					'type'        => 'boolean',
					'description' => __( 'Whether the map link should be shown for the venue or not', 'the-events-calendar' ),
				],
				'address'           => [
					'type'        => 'string',
					'description' => __( 'The venue address', 'the-events-calendar' ),
				],
				'city'              => [
					'type'        => 'string',
					'description' => __( 'The venue city', 'the-events-calendar' ),
				],
				'country'           => [
					'type'        => 'string',
					'description' => __( 'The venue country', 'the-events-calendar' ),
				],
				'province'          => [
					'type'        => 'string',
					'description' => __( 'The venue province', 'the-events-calendar' ),
				],
				'state'             => [
					'type'        => 'string',
					'description' => __( 'The venue state', 'the-events-calendar' ),
				],
				'zip'               => [
					'type'        => 'string',
					'description' => __( 'The venue ZIP code', 'the-events-calendar' ),
				],
				'phone'             => [
					'type'        => 'string',
					'description' => __( 'The venue phone number', 'the-events-calendar' ),
				],
				'website'           => [
					'type'        => 'string',
					'description' => __( 'The venue website URL', 'the-events-calendar' ),
				],
				'stateprovince'     => [
					'type'        => 'string',
					'description' => __( 'The venue state or province', 'the-events-calendar' ),
				],
				'geo_lat'           => [
					'type'        => 'number',
					'format'      => 'double',
					'description' => __( 'The venue geo latitude', 'the-events-calendar' ),
				],
				'geo_lng'           => [
					'type'        => 'number',
					'format'      => 'double',
					'description' => __( 'The venue geo longitude', 'the-events-calendar' ),
				],
			],
		];

		/**
		 * Filters the Swagger documentation generated for a venue in the TEC REST API.
		 *
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_venue_documentation', $documentation );

		return $documentation;
	}
}
