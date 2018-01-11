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
		$documentation = array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type' => 'integer',
					'description' => __( 'The venue WordPress post ID', 'the-events-calendar' ),
				),
				'global_id' => array(
					'type' => 'string',
					'description' => __( 'The venue ID used to globally identify in Event Aggregator', 'the-events-calendar' ),
				),
				'global_id_lineage' => array(
					'type' => 'array',
					'items' => array( 'type' => 'string' ),
					'description' => __( 'An Array containing the lineage of where this organizer comes from, this should not change after the organizer is created.', 'the-events-calendar' ),
				),
				'author' => array(
					'type' => 'integer',
					'description' => __( 'The venue author WordPress post ID', 'the-events-calendar' ),
				),
				'date' => array(
					'type' => 'string',
					'description' => __( 'The venue creation date in the site timezone', 'the-events-calendar' ),
				),
				'date_utc' => array(
					'type' => 'string',
					'description' => __( 'The venue creation date in UTC time', 'the-events-calendar' ),
				),
				'modified' => array(
					'type' => 'string',
					'description' => __( 'The venue last modification date in the site timezone', 'the-events-calendar' ),
				),
				'modified_utc' => array(
					'type' => 'string',
					'description' => __( 'The venue last modification date in UTC time', 'the-events-calendar' ),
				),
				'status' => array(
					'type' => 'string',
					'description' => __( 'The venue status', 'the-events-calendar' ),
				),
				'url' => array(
					'type' => 'string',
					'description' => __( 'The URL to the venue page', 'the-events-calendar' ),
				),
				'venue' => array(
					'type' => 'string',
					'description' => __( 'The venue name', 'the-events-calendar' ),
				),
				'description' => array(
					'type' => 'string',
					'description' => __( 'The venue long description', 'the-events-calendar' ),
				),
				'excerpt' => array(
					'type' => 'string',
					'description' => __( 'The venue short description', 'the-events-calendar' ),
				),
				'slug' => array(
					'type' => 'string',
					'description' => __( 'The venue slug', 'the-events-calendar' ),
				),
				'image' => array(
					'type' => 'string',
					'description' => __( 'The event featured image details if set', 'the-events-calendar' ),
					'$ref' => '#/definitions/Image',
				),
				'show_map' => array(
					'type' => 'boolean',
					'description' => __( 'Whether the map should be shown for the venue or not', 'the-events-calendar' ),
				),
				'show_map_link' => array(
					'type' => 'boolean',
					'description' => __( 'Whether the map link should be shown for the venue or not', 'the-events-calendar' ),
				),
				'address' => array(
					'type' => 'string',
					'description' => __( 'The venue address', 'the-events-calendar' ),
				),
				'city' => array(
					'type' => 'string',
					'description' => __( 'The venue city', 'the-events-calendar' ),
				),
				'country' => array(
					'type' => 'string',
					'description' => __( 'The venue country', 'the-events-calendar' ),
				),
				'province' => array(
					'type' => 'string',
					'description' => __( 'The venue province', 'the-events-calendar' ),
				),
				'state' => array(
					'type' => 'string',
					'description' => __( 'The venue state', 'the-events-calendar' ),
				),
				'zip' => array(
					'type' => 'string',
					'description' => __( 'The venue ZIP code', 'the-events-calendar' ),
				),
				'phone' => array(
					'type' => 'string',
					'description' => __( 'The venue phone number', 'the-events-calendar' ),
				),
				'website' => array(
					'type' => 'string',
					'description' => __( 'The venue website URL', 'the-events-calendar' ),
				),
				'stateprovince' => array(
					'type' => 'string',
					'description' => __( 'The venue state or province', 'the-events-calendar' ),
				),
				'geo_lat' => array(
					'type'   => 'number',
					'format' => 'double',
					'description' => __( 'The venue geo latitude', 'the-events-calendar' ),
				),
				'geo_lng' => array(
					'type'   => 'number',
					'format' => 'double',
					'description' => __( 'The venue geo longitude', 'the-events-calendar' ),
				),
			),
		);

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
