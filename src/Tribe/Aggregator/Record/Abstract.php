<?php
// Don't load directly
defined( 'WPINC' ) or die;

abstract class Tribe__Events__Aggregator__Record__Abstract {
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
	public $meta;

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

		$this->setup_meta( $meta );
	}

	/**
	 * Sets up meta fields by de-prefixing them into the array
	 *
	 * @param array $meta Meta array
	 */
	public function setup_meta( $meta ) {
		foreach ( $meta as $key => $value ) {
			$key = preg_replace( '/^' . self::$meta_key_prefix . '/', '', $key );
			$this->meta[ $key ] = $value;
		}
	}

	/**
	 * Creates an import record
	 *
	 * @param string $origin EA origin
	 * @param string $type Type of record to create - manual or schedule
	 * @param array $args Post type args
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $origin = false, $type = 'manual', $args = array() ) {
		$defaults = array(
			'frequency' => null,
			'type'      => $type,
		);

		$args = wp_parse_args( $args, $defaults );

		$post = array(
			// Stores the Key under `post_title` which is a very forgiving type of column on `wp_post`
			'post_title'  => wp_generate_password( 32, true, true ),
			'post_type'   => Tribe__Events__Aggregator__Record__Post_Type::$post_type,
			'post_date'   => current_time( 'mysql' ),
			'post_status' => 'draft',
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
		$this->setup_meta( (array) $args );

		return $this->post;
	}

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = array() ) {
		$aggregator = Tribe__Events__Aggregator::instance();

		$error = null;

		// if the daily limit for import requests has been reached, error out
		if ( 0 >= $aggregator->daily_limit_available() ) {
			$error = $this->log_limit_reached_error();
			return $this->set_status_as_failed( $error );
		}

		$defaults = array(
			'type'     => $this->meta['type'],
			'origin'   => $this->meta['origin'],
			'source'   => $this->meta['source'],
			'callback' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// create the import on the Event Aggregator service
		$response = $aggregator->api( 'import' )->create( $args );

		// if the Aggregator API returns a WP_Error, set this record as failed
		if ( is_wp_error( $response ) ) {
			$error = $response;
			return $this->set_status_as_failed( $error );
		}

		// if the Aggregator response has an unexpected format, set this record as failed
		if ( empty( $response->message_code ) ) {
			$error = new WP_Error( 'invalid-response', esc_html__( 'An unexpected response was received from the Event Aggregator service', 'the-events-calendar' ) );
			return $this->set_status_as_failed( $error );
		}

		// if the Import creation was unsuccessful, set this record as failed
		if (
			'success_create-import' != $response->message_code
			&& 'queued' != $response->message_code
		) {
			$error = new WP_Error( $response->message_code, esc_html__( $response->message, 'the-events-calendar' ) );
			return $this->set_status_as_failed( $error );
		}

		// if the Import creation didn't provide an import id, the response was invalid so mark as failed
		if ( empty( $response->data->import_id ) ) {
			$error = new WP_Error( 'invalid-response', esc_html__( 'An unexpected response was received from the Event Aggregator service', 'the-events-calendar' ) );
			return $this->set_status_as_failed( $error );
		}

		// if we get here, we're good! Set the status to pending
		$this->set_status_as_pending();

		// store the import id
		update_post_meta( $this->id, self::$meta_key_prefix . 'import_id', $response->data->import_id );

		// reduce the daily allotment of import creations
		$aggregator->reduce_daily_limit( 1 );

		return $response;
	}

	/**
	 * Sets a status on the record
	 *
	 * @return int
	 */
	public function set_status( $status ) {
		return wp_update_post( array(
			'ID' => $this->id,
			'post_status' => $status,
		) );
	}

	/**
	 * Marks a record as failed
	 *
	 * @return int
	 */
	public function set_status_as_failed( $error = null ) {
		if ( $error && is_wp_error( $error ) ) {
			$this->log_error( $error );
		}

		return $this->set_status( Tribe__Events__Aggregator__Record__Post_Type::$status->failed );
	}

	/**
	 * Marks a record as pending
	 *
	 * @return int
	 */
	public function set_status_as_pending() {
		return $this->set_status( 'pending' );
	}

	/**
	 * Marks a record as successful
	 *
	 * @return int
	 */
	public function set_status_as_success() {
		return $this->set_status( Tribe__Events__Aggregator__Record__Post_Type::$status->success );
	}

	/**
	 * Gets errors on the record post
	 */
	public function get_errors( $args = array() ) {
		$defaults = array(
			'post_id' => $this->id,
		);

		$args = wp_parse_args( $args, $defaults );

		return get_comments( $args );
	}

	/**
	 * Logs an error to the comments of the Record post
	 *
	 * @param WP_Error $error Error message to log
	 *
	 * @return bool
	 */
	public function log_error( $error ) {
		$args = array(
			'comment_post_ID' => $this->id,
			'comment_author'  => $error->get_error_code(),
			'comment_content' => $error->get_error_message(),
		);

		return wp_insert_comment( $args );
	}

	/**
	 * Logs the fact that the daily import limit has been reached
	 *
	 * @return WP_Error
	 */
	public function log_limit_reached_error() {
		$aggregator = Tribe__Events__Aggregator::instance();

		$error = new WP_Error(
			'aggregator-limit-reached',
			sprintf(
				esc_html__( 'The Aggregator import limit of %1$d for the day has already been reached.' ),
				$aggregator->daily_limit()
			)
		);

		$this->log_error( $error );

		return $error;
	}
}
