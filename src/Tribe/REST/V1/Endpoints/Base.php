<?php


abstract class Tribe__Events__REST__V1__Endpoints__Base {

	/**
	 * @var Tribe__REST__Messages_Interface
	 */
	protected $messages;

	/**
	 * @var array
	 */
	protected $supported_query_vars = array();

	/**
	 * Tribe__Events__REST__V1__Endpoints__Base constructor.
	 *
	 * @param Tribe__REST__Messages_Interface $messages
	 */
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
			'items' => array(
				'type' => 'integer',
			),
		), $defaults );


		$swaggerized = array();
		foreach ( $args as $name => $info ) {
			if ( isset( $info['swagger_type'] ) ) {
				$type = $info['swagger_type'];
			} else {
				$type = isset( $info['type'] ) ? $info['type'] : false;
			}

			$type = $this->convert_type( $type );

			$read = array(
				'name'             => $name,
				'in'               => isset( $info['in'] ) ? $info['in'] : false,
				'collectionFormat' => isset( $info['collectionFormat'] ) ? $info['collectionFormat'] : false,
				'description'      => isset( $info['description'] ) ? $info['description'] : false,
				'type'             => $type,
				'items'            => isset( $info['items'] ) ? $info['items'] : false,
				'required'         => isset( $info['required'] ) ? $info['required'] : false,
				'default'          => isset( $info['default'] ) ? $info['default'] : false,
			);

			if ( isset( $info['swagger_type'] ) ) {
				$read['type'] = $info['swagger_type'];
			}

			if ( $read['type'] !== 'array' ) {
				unset( $defaults['items'] );
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
	 an*
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
	 *
	 * @since 4.6
	 */
	protected function unrequire_arg( array &$arg ) {
		$arg['required'] = false;
	}

	/**
	 * Parses the arguments populated parsing the request filling out with the defaults.
	 *
	 * @param array $args
	 * @param array $defaults
	 *
	 * @return array
	 *
	 * @since 4.6
	 */
	protected function parse_args( array $args, array $defaults ) {
		foreach ( $this->supported_query_vars as $request_key => $query_var ) {
			if ( isset( $defaults[ $request_key ] ) ) {
				$defaults[ $query_var ] = $defaults[ $request_key ];
			}
		}

		$args = wp_parse_args( array_filter( $args, array( $this, 'is_not_null' ) ), $defaults );

		return $args;
	}

	/**
	 * Whether a value is null or not.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 *
	 * @since 4.6
	 */
	public function is_not_null( $value ) {
		return null !== $value;
	}

	/**
	 * Converts REST format type argument to the correspondant Swagger.io definition.
	 *
	 * @since 4.6
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected function convert_type( $type ) {
		$rest_to_swagger_type_map = array(
			'int'  => 'integer',
			'bool' => 'boolean',
		);

		return Tribe__Utils__Array::get( $rest_to_swagger_type_map, $type, $type );
	}
}
