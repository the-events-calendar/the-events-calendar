<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record {
	/**
	 * Meta key prefix for ea-record data
	 *
	 * @var string
	 */
	public static $meta_key_prefix    = 'ea_';

	public static $key = array(
		'source' => '_tribe_ea_source',
		'origin' => '_tribe_ea_origin',
	);

	public $id;
	public $post;

	/**
	 * Setup all the hooks and filters
	 *
	 * @return void
	 */
	public function __construct( $id = null ) {
		// Make it an object for easier usage
		if ( ! is_object( self::$key ) ) {
			self::$key = (object) self::$key;
		}

		if ( ! empty( $id ) && is_numeric( $id ) ) {
			$this->id = $id;
		}

		if ( $this->id ) {
			$this->load();
		}
	}

	/**
	 * Loads the WP_Post associated with this record
	 */
	public function load() {
		$this->post = get_post( $this->id );
		$meta       = get_post_meta( $this->id );

		foreach ( $meta as $key => $value ) {
			$key = preg_replace( '/^' . self::$meta_key_prefix . '/', '', $key );
			$this->meta[ $key ] = reset( $value );
		}
	}

	/**
	 * Creates an import record
	 *
	 * @param string $origin EA origin
	 * @param string $type Type of record to create - import or schedule
	 * @param array $args Post type args
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $origin = false, $type = 'import', $args = array() ) {
		$defaults = array(
			'frequency' => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$post = array(
			// Stores the Key under `post_title` which is a very forgiving type of column on `wp_post`
			'post_title'  => wp_generate_password( 32, true, true ),
			'post_type'   => Tribe__Events__Aggregator__Record__Post_Type::$post_type,
			'post_date'   => current_time( 'mysql' ),
			'post_status' => 'pending',
			'meta_input'  => array(),
		);

		// prefix all keys
		foreach ( $args as $key => $value ) {
			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		$args = (object) $args;

		if ( 'schedule' === $type ) {
			$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( 'id=' . $args->frequency );
			if ( ! $frequency ) {
				return new WP_Error( 'invalid-frequency', __( 'An Invalid frequency was used to try to setup a scheduled import', 'the-events-calendar' ), $args );
			}

			// Setups the post_content as the Frequency (makes it easy to fetch by frequency)
			$post['post_content'] = $frequency->id;
			$post['post_status']  = Tribe__Events__Aggregator__Record__Post_Type::$status->scheduled;

			// When the next scheduled import should happen
			// @todo
			// $post['post_content_filtered'] =
		}

		$this->id = wp_insert_post( $post );
		$this->post = get_post( $this->id );

		return $this->post;
	}
}
