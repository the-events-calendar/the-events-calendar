<?php


interface Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface
	extends Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__REST__Endpoints__CREATE_Endpoint_Interface,
	Tribe__REST__Endpoints__DELETE_Endpoint_Interface {

	/**
	 * Inserts a post of the linked post type provided the linked post data or post ID.
	 *
	 * @param int|array $data Either an existing linked post ID or the linked post data.
	 *
	 * @return false|array|WP_Error `false` if the linked post data is empty, the linked post ID (in an array as requested by the
	 *                              linked posts engine) or a `WP_Error` if the linked post insertion failed.
	 */
	public function insert( $data );
}