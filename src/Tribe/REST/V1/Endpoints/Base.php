<?php


abstract class Tribe__Events__REST__V1__Endpoints__Base {

	/**
	 * @var Tribe__REST__Messages_Interface
	 */
	protected $messages;

	public function __construct( Tribe__REST__Messages_Interface $messages ) {
		$this->messages = $messages;
	}

	/**
	 * Converts an array of arguments suitable for the WP REST API to the Swagger format.
	 *
	 * @param array $args
	 * @param array $defaults
	 *
	 * @return array The converted arguments.
	 */
	public function swaggerize_args( array $args = array(), array $defaults = array() ) {
		if ( empty( $args ) ) {
			return $args;
		}

		$no_description = __( 'No description provided', 'the-events-calendar' );
		$defaults = array_merge( array(
			'in'          => 'body',
			'type'        => 'string',
			'description' => $no_description,
			'required'    => false,
			'default'     => '',
		), $defaults );


		$swaggerized = array();
		foreach ( $args as $name => $info ) {
			$read = array(
				'name'             => $name,
				'in'               => isset( $info['in'] ) ? $info['in'] : false,
				'collectionFormat' => isset( $info['collectionFormat'] ) ? $info['collectionFormat'] : false,
				'description'      => isset( $info['description'] ) ? $info['description'] : false,
				'type'             => isset( $info['swagger_type'] ) ? $info['swagger_type'] : false,
				'required'         => isset( $info['required'] ) ? $info['required'] : false,
				'default'          => isset( $info['default'] ) ? $info['default'] : false,
			);

			if ( isset( $info['swagger_type'] ) ) {
				$read['type'] = $info['swagger_type'];
			}

			$swaggerized[] = array_merge( $defaults, array_filter( $read ) );
		}

		return $swaggerized;
	}

	/**
	 * Falls back on an allowed post status in respect to the user user capabilities of publishing.
	 *
	 * @param string $post_status
	 * @param string $post_type
	 *
	 * @return string
	 */
	public function scale_back_post_status( $post_status, $post_type ) {
		$post_type_object = get_post_type_object( $post_type );

		if ( current_user_can( $post_type_object->cap->publish_posts ) ) {
			return ! empty( $post_status ) ? $post_status : 'publish';
		}
		if ( in_array( $post_status, array( 'publish', 'future' ) ) ) {
			return 'pending';
		}

		return ! empty( $post_status ) ? $post_status : 'draft';
	}

	/**
	 * Returns the default value of posts per page.
	 *
	 * Cascading fallback is TEC `posts_per_page` option, `posts_per_page` option and, finally, 20.
	 *
	 * @return int
	 */
	protected function get_default_posts_per_page() {
		$posts_per_page = tribe_get_option( 'posts_per_page', get_option( 'posts_per_page' ) );

		return ! empty( $posts_per_page ) ? $posts_per_page : 20;
	}

	/**
	 * Modifies a request argument marking it as not required.
	 *
	 * @param array $arg
	 */
	protected function unrequire_arg( array &$arg ) {
		$arg['required'] = false;
	}
}