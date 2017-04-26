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
		$documentation = array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type' => 'int',
					'description' => __( 'The organizer WordPress post ID', 'the-events-calendar' ),
				),
				'global_id' => array(
					'type' => 'string',
					'description' => __( 'The organizer ID used to globally identify in Event Aggregator', 'the-events-calendar' ),
				),
				'global_id_lineage' => array(
					'type' => 'array',
					'description' => __( 'An Array containing the lineage of where this organizer comes from, this should not change after the organizer is created.', 'the-events-calendar' ),
				),
				'author' => array(
					'type' => 'int',
					'description' => __( 'The organizer author WordPress post ID', 'the-events-calendar' ),
				),
				'date' => array(
					'type' => 'string',
					'description' => __( 'The organizer creation date in the site timezone', 'the-events-calendar' ),
				),
				'date_utc' => array(
					'type' => 'string',
					'description' => __( 'The organizer creation date in UTC time', 'the-events-calendar' ),
				),
				'modified' => array(
					'type' => 'string',
					'description' => __( 'The organizer last modification date in the site timezone', 'the-events-calendar' ),
				),
				'modified_utc' => array(
					'type' => 'string',
					'description' => __( 'The organizer last modification date in UTC time', 'the-events-calendar' ),
				),
				'url' => array(
					'type' => 'string',
					'description' => __( 'The URL to the organizer page', 'the-events-calendar' ),
				),
				'organizer' => array(
					'type' => 'string',
					'description' => __( 'The organizer name', 'the-events-calendar' ),
				),
				'description' => array(
					'type' => 'string',
					'description' => __( 'The organizer long description', 'the-events-calendar' ),
				),
				'excerpt' => array(
					'type' => 'string',
					'description' => __( 'The organizer short description', 'the-events-calendar' ),
				),
				'image' => array(
					'type' => 'string',
					'description' => __( 'The event featured image details if set', 'the-events-calendar' ),
					'$ref' => '#/definitions/Image',
				),
				'phone' => array(
					'type' => 'string',
					'description' => __( 'The organizer phone number', 'the-events-calendar' ),
				),
				'website' => array(
					'type' => 'string',
					'description' => __( 'The organizer website', 'the-events-calendar' ),
				),
				'email' => array(
					'type' => 'string',
					'description' => __( 'The organizer email address', 'the-events-calendar' ),
				),
			),
		);

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