<?php


abstract class Tribe__Events__REST__V1__Endpoints__Linked_Post_Base
	extends Tribe__Events__REST__V1__Endpoints__Base
	implements Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface {
	/**
	 * @var Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $post_repository;

	/**
	 * @var Tribe__Events__REST__V1__Validator__Interface
	 */
	protected $validator;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Single_Venue constructor.
	 *
	 * @param Tribe__REST__Messages_Interface                  $messages
	 * @param Tribe__Events__REST__Interfaces__Post_Repository $post_repository
	 * @param Tribe__Events__REST__V1__Validator__Interface    $validator
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $post_repository,
		Tribe__Events__REST__V1__Validator__Interface $validator
	) {
		parent::__construct( $messages );
		$this->post_repository = $post_repository;
		$this->validator = $validator;
	}

	/**
	 * Inserts a post of the linked post type.
	 *
	 * @param int|array $data Either an existing linked post ID or the linked post data.
	 *
	 * @return false|array|WP_Error `false` if the linked post data is empty, the linked post ID (in an array as requested by the
	 *                              linked posts engine) or a `WP_Error` if the linked post insertion failed.
	 */
	public function insert( $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		if ( is_numeric( $data ) && $this->is_post_type( $data ) ) {
			return array( $this->get_id_index() => $data );
		}

		if ( ! empty( $data['id'] ) ) {
			if ( $this->is_post_type( $data['id'] ) ) {
				return array( $this->get_id_index() => $data['id'] );
			}
			$message = $this->messages->get_message( 'invalid-linked-post-id' );

			return new WP_Error( 'invalid-linked-post-id', $message, array( 'status' => 400 ) );
		}

		$data_request = new WP_REST_Request();
		$data_request->set_param( 'args', $this->CREATE_args() );

		$body_params = (array) $data;
		foreach ( $body_params as $key => $value ) {
			$data_request->set_param( $key, $value );
		}

		$post_id = $this->create( $data_request, true );

		if ( $post_id instanceof WP_Error ) {
			return $post_id;
		}

		return array( $this->get_id_index() => $post_id );
	}

	/**
	 * Whether the data represents a valid post type ID.
	 *
	 * @param mixed $data
	 *
	 * @return bool
	 */
	abstract protected function is_post_type( $data );

	/**
	 * Returns the linked post ID index used by the linked posts engine to identify a linked post based on its post ID.
	 *
	 * @return string
	 */
	public function get_id_index() {
		return tribe( 'tec.linked-posts' )->get_post_type_id_field_index( $this->get_post_type() );
	}

	/**
	 * Returns the post type handled by this linked post endpoint.
	 *
	 * @return string
	 */
	abstract protected function get_post_type();
}
