<?php

class Tribe__Events__REST__V1__Documentation__Term_Definition_Provider
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
				'ID'          => array( 'type' => 'int', 'description' => __( 'The WordPress term ID', 'the-events-calendar' ) ),
				'name'        => array( 'type' => 'string', 'description' => __( 'The term name', 'the-events-calendar' ) ),
				'slug'        => array( 'type' => 'string', 'description' => __( 'The term slug', 'the-events-calendar' ) ),
				'taxonomy'    => array( 'type' => 'string', 'description' => __( 'The taxonomy the term belongs to', 'the-events-calendar' ) ),
				'description' => array( 'type' => 'string', 'description' => __( 'The term description', 'the-events-calendar' ) ),
				'parent'      => array( 'type' => 'int', 'description' => __( 'The term parent term if any', 'the-events-calendar' ) ),
				'count'       => array( 'type' => 'int', 'description' => __( 'The number of posts associated with the term', 'the-events-calendar' ) ),
				'link'        => array( 'type' => 'string', 'description' => __( 'The URL to the term archive page', 'the-events-calendar' ) ),
			),
		);

		/**
		 * Filters the Swagger documentation generated for an term in the TEC REST API.
		 *
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_term_documentation', $documentation );

		return $documentation;
	}
}