<?php
// Don't load directly
defined( 'WPINC' ) or die;

abstract class Tribe__Events__Aggregator__Record__Abstract {
	/**
	 * Meta key prefix for ea-record data
	 *
	 * @var string
	 */
	public static $meta_key_prefix = '_tribe_aggregator_';

	public $id;
	public $post;
	public $meta;

	public $type;
	public $frequency;

	public $is_schedule = false;
	public $is_manual = false;

	/**
	 * Setup all the hooks and filters
	 *
	 * @return void
	 */
	public function __construct( $post = null ) {
		// If we have an Post we try to Setup
		$this->load( $post );
	}

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Loads the WP_Post associated with this record
	 */
	public function load( $post = null ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		$this->id = $post->ID;

		// Get WP_Post object
		$this->post = $post;

		// Map `ping_status` as the `type`
		$this->type = $this->post->ping_status;

		if ( 'schedule' === $this->type ) {
			$this->frequency = $this->post->post_content;

			// Boolean Flag for Scheduled records
			$this->is_schedule = true;
		} else {
			// Everything that is not a Scheduled Record is set as Manual
			$this->is_manual = true;
		}

		$this->setup_meta( get_post_meta( $this->id ) );

		return $this;
	}

	/**
	 * Sets up meta fields by de-prefixing them into the array
	 *
	 * @param array $meta Meta array
	 */
	public function setup_meta( $meta ) {
		foreach ( $meta as $key => $value ) {
			$key = preg_replace( '/^' . self::$meta_key_prefix . '/', '', $key );
			$this->meta[ $key ] = is_array( $value ) ? reset( $value ) : $value;
		}
	}

	/**
	 * Creates an import record
	 *
	 * @param string $type Type of record to create - manual or schedule
	 * @param array $args Post type args
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $type = 'manual', $args = array(), $meta = array() ) {
		if ( ! in_array( $type, array( 'manual', 'schedule' ) ) ) {
			return new WP_Error( 'invalid-type', __( 'An invalid Type was used to setup this Record', 'the-events-calendar' ), $type );
		}

		$defaults = array(
			'frequency' => null,
			'parent'    => 0,
		);
		$args = (object) wp_parse_args( $args, $defaults );

		$defaults = array(
		);
		$meta = wp_parse_args( $meta, $defaults );

		$post = array(
			// Stores the Key under `post_title` which is a very forgiving type of column on `wp_post`
			'post_title'     => wp_generate_password( 32, true, true ),
			'post_type'      => Tribe__Events__Aggregator__Records::$post_type,
			'ping_status'    => $type,
			// The Mime Type needs to be on a %/% format to work on WordPress
			'post_mime_type' => 'ea/' . $this->origin,
			'post_date'      => current_time( 'mysql' ),
			'post_status'    => Tribe__Events__Aggregator__Records::$status->draft,
			'post_parent'    => $args->parent,
			'meta_input'     => array(),
		);

		// prefix all keys
		foreach ( $meta as $key => $value ) {
			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		$args = (object) $args;

		if ( 'schedule' === $type ) {
			$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $args->frequency ) );
			if ( ! $frequency ) {
				return new WP_Error( 'invalid-frequency', __( 'An Invalid frequency was used to try to setup a scheduled import', 'the-events-calendar' ), $args );
			}

			// Setups the post_content as the Frequency (makes it easy to fetch by frequency)
			$post['post_content'] = $frequency->id;
			$post['post_status']  = Tribe__Events__Aggregator__Records::$status->schedule;

			// When the next scheduled import should happen
			// @todo
			// $post['post_content_filtered'] =
		}

		// // After Creating the Post Load and return
		return $this->load( wp_insert_post( $post ) );
	}

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = array() ) {
		$aggregator = Tribe__Events__Aggregator::instance();

		$error = null;

		// if the daily limit for import requests has been reached, error out
		if ( 0 >= $aggregator->get_daily_limit_available() ) {
			$error = $this->log_limit_reached_error();
			return $this->set_status_as_failed( $error );
		}

		$defaults = array(
			'type'     => $this->meta['type'],
			'origin'   => $this->meta['origin'],
			'source'   => $this->meta['source'],
			'callback' => site_url( '/event-aggregator/insert/?key=' . urlencode( $this->post->post_title ) ),
		);

		if ( ! empty( $this->meta['frequency'] ) ) {
			$defaults['frequency'] = $this->meta['frequency'];
		}

		if ( ! empty( $this->meta['file'] ) ) {
			$defaults['file'] = $this->meta['file'];
		}

		if ( ! empty( $this->meta['keywords'] ) ) {
			$defaults['keywords'] = $this->meta['keywords'];
		}

		if ( ! empty( $this->meta['location'] ) ) {
			$defaults['location'] = $this->meta['location'];
		}

		if ( ! empty( $this->meta['start'] ) ) {
			$defaults['start'] = $this->meta['start'];
		}

		if ( ! empty( $this->meta['radius'] ) ) {
			$defaults['radius'] = $this->meta['radius'];
		}

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
			'success:create-import' != $response->message_code
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

	public function get_import_data() {
		$aggregator = Tribe__Events__Aggregator::instance();
		return $aggregator->api( 'import' )->get( $this->meta['import_id'] );
	}

	/**
	 * Sets a status on the record
	 *
	 * @return int
	 */
	public function set_status( $status ) {
		if ( ! isset( Tribe__Events__Aggregator__Records::$status->{ $status } ) ) {
			return false;
		}

		return wp_update_post( array(
			'ID' => $this->id,
			'post_status' => Tribe__Events__Aggregator__Records::$status->{ $status },
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

		$this->set_status( 'failed' );

		return $error;
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
		return $this->set_status( 'success' );
	}

	public function query_child_records( $args = array() ) {
		$statuses = Tribe__Events__Aggregator__Records::$status;
		$defaults = array(
			'post_status' => array( $statuses->success, $statuses->failed, $statuses->pending ),
			'post_parent' => $this->id,
			'orderby'     => 'modified',
			'order'       => 'DESC',
		);
		$args = (object) wp_parse_args( $args, $defaults );

		// Enforce the Post Type
		$args->post_type = Tribe__Events__Aggregator__Records::$post_type;

		// Do the actual Query
		$query = new WP_Query( $args );

		return $query;
	}

	public function get_child_record_by_status( $status = 'success', $qty = -1 ){
		$statuses = Tribe__Events__Aggregator__Records::$status;

		if ( ! isset( $statuses->{ $status } ) ) {
			return false;
		}

		$args = array(
			'post_status'    => $statuses->{ $status },
			'posts_per_page' => $qty,
		);
		$query = $this->query_child_records( $args );
		if ( ! $query->have_posts() ) {
			return false;
		}

		// Return the First Post when it exists
		return $query;
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
				$aggregator->get_daily_limit()
			)
		);

		$this->log_error( $error );

		return $error;
	}
}
