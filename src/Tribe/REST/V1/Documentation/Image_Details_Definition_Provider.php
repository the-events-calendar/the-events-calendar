<?php

class Tribe__Events__REST__V1__Documentation__Image_Details_Definition_Provider
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
				'ID'     => array( 'type' => 'int', 'description' => __( 'The image WordPress post ID', 'the-events-calendar' ) ),
				'width'  => array( 'type' => 'int', 'description' => __( 'The image natural width in pixels', 'the-events-calendar' ) ),
				'height' => array( 'type' => 'int', 'description' => __( 'The image natura height in pixels', 'the-events-calendar' ) ),
				'url'    => array( 'type' => 'string', 'description' => __( 'The link to the image on the site', 'the-events-calendar' ) ),
				'sizes'  => array( 'type' => 'array', 'description' => __( 'The details about each size available for the image', 'the-events-calendar' ), '$ref' => '#/definitions/ImageSizeDetails' ),
			),
		);

		/**
		 * Filters the Swagger documentation generated for an image deatails in the TEC REST API.
		 *
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_image_details_documentation', $documentation );

		return $documentation;
	}
}