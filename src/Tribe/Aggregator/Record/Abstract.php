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
	public $last_wpdb_error = '';

	/**
	 * An associative array of origins and the settings they define a policy for.
	 * @var array
	 */
	protected $origin_import_policies = array(
		'url' => array( 'show_map_link' ),
	);

	public static $unique_id_fields = array(
		'facebook' => array(
			'source' => 'facebook_id',
			'target' => 'EventFacebookID',
			'legacy' => 'FacebookID',
		),
		'meetup' => array(
			'source' => 'meetup_id',
			'target' => 'EventMeetupID',
		),
		'ical' => array(
			'source' => 'uid',
			'target' => 'uid',
		),
		'gcal' => array(
			'source' => 'uid',
			'target' => 'uid',
		),
		'ics' => array(
			'source' => 'uid',
			'target' => 'uid',
		),
		'url' => array(
			'source' => 'id',
			'target' => 'EventOriginalID',
		),
	);

	/**
	 * Holds the event count temporarily while event counts (comment_count) is being updated
	 *
	 * @var int
	 */
	private $temp_event_count = 0;

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
			return tribe_error( 'core:aggregator:invalid-record-object', array(), array( $post ) );
		}

		if ( $post->post_type !== Tribe__Events__Aggregator__Records::$post_type ) {
			return tribe_error( 'core:aggregator:invalid-record-post_type', array(), array( $post ) );
		}

		$this->id = $post->ID;

		// Get WP_Post object
		$this->post = $post;

		// Map `ping_status` as the `type`
		$this->type = $this->post->ping_status;

		if ( 'schedule' === $this->type ) {
			// Fetches the Frequency Object
			$this->frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $this->post->post_content ) );

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
			$this->meta[ $key ] = maybe_unserialize( is_array( $value ) ? reset( $value ) : $value );
		}

		// `source` will be empty when importing .ics files
		$this->meta['source'] = ! empty ( $this->meta['source'] ) ? $this->meta['source'] : '';

		// This prevents lots of isset checks for no reason
		if ( empty( $this->meta['activity'] ) ) {
			$this->meta['activity'] = new Tribe__Events__Aggregator__Record__Activity();
		}
	}

	/**
	 * Updates import record meta
	 *
	 * @param string $key Meta key
	 * @param mixed $value Meta value
	 */
	public function update_meta( $key, $value ) {
		$this->meta[ $key ] = $value;

		$field = self::$meta_key_prefix . $key;

		if ( null === $value ) {
			return delete_post_meta( $this->post->ID, $field );
		}

		return update_post_meta( $this->post->ID, $field, $value );
	}

	/**
	 * Deletes import record meta
	 *
	 * @param string $key Meta key
	 */
	public function delete_meta( $key ) {
		return delete_post_meta( $this->post->ID, self::$meta_key_prefix . $key );
	}

	/**
	 * Returns the Activity object for the record
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	public function activity() {
		if ( empty( $this->meta['activity'] ) ) {
			$activity = new Tribe__Events__Aggregator__Record__Activity;
			$this->update_meta( 'activity', $activity );
		}

		return $this->meta['activity'];
	}

	/**
	 * Saves activity data on a record
	 */
	public function save_activity() {
		$this->update_meta( 'activity', $this->activity() );
	}

	/**
	 * Creates an import record
	 *
	 * @param string $type Type of record to create - manual or schedule
	 * @param array $args Post type args
	 * @param array $meta Post meta
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $type = 'manual', $args = array(), $meta = array() ) {
		if ( ! in_array( $type, array( 'manual', 'schedule' ) ) ) {
			return tribe_error( 'core:aggregator:invalid-create-record-type', $type );
		}

		$defaults = array(
			'parent'    => 0,
		);
		$args = (object) wp_parse_args( $args, $defaults );

		$defaults = array(
			'frequency' => null,
			'hash'      => wp_generate_password( 32, true, true ),
			'preview'   => false,
		);

		$meta = wp_parse_args( $meta, $defaults );

		$post = $this->prep_post_args( $type, $args, $meta );

		$this->watch_for_db_errors();

		$result = wp_insert_post( $post );

		if ( is_wp_error( $result ) ) {
			$this->maybe_add_meta_via_pre_wp_44_method( $result, $post['meta_input'] );
		}

		if ( $this->db_errors_happened() ) {
			$error_message = __( 'Something went wrong while inserting the record in the database.', 'the-events-calendar' );
			wp_delete_post( $result );

			return new WP_Error( 'db-error-during-creation', $error_message );
		}

		// After Creating the Post Load and return
		return $this->load( $result );
	}

	/**
	 * Edits an import record
	 *
	 * @param int   $post_id
	 * @param array $args    Post type args
	 * @param array $meta    Post meta
	 *
	 * @return WP_Post|WP_Error
	 */
	public function save( $post_id, $args = array(), $meta = array() ) {
		if ( ! isset( $meta['type'] ) || 'schedule' !== $meta['type'] ) {
			return tribe_error( 'core:aggregator:invalid-edit-record-type', $meta );
		}

		$defaults = array(
			'parent'    => 0,
		);
		$args = (object) wp_parse_args( $args, $defaults );

		$defaults = array(
			'frequency' => null,
		);

		$meta = wp_parse_args( $meta, $defaults );

		$post = $this->prep_post_args( $meta['type'], $args, $meta );
		$post['ID'] = absint( $post_id );
		$post['post_status'] = Tribe__Events__Aggregator__Records::$status->schedule;

		add_filter( 'wp_insert_post_data', array( $this, 'dont_change_post_modified' ), 10, 2 );
		$result = wp_update_post( $post );
		remove_filter( 'wp_insert_post_data', array( $this, 'dont_change_post_modified' ) );

		if ( ! is_wp_error( $result ) ) {
			$this->maybe_add_meta_via_pre_wp_44_method( $result, $post['meta_input'] );
		}

		// After Creating the Post Load and return
		return $this->load( $result );
	}

	/**
	 * Filter the post_modified dates to be unchanged
	 * conditionally hooked to wp_insert_post_data and then unhooked after wp_update_post
	 *
	 * @param array $data new data to be used in the update
	 * @param array $postarr existing post data
	 *
	 * @return array
	 */
	public function dont_change_post_modified( $data, $postarr ) {
		$post = get_post( $postarr['ID'] );
		$data['post_modified'] = $postarr['post_modified'];
		$data['post_modified_gmt'] = $postarr['post_modified_gmt'];

		return $data;
	}

	/**
	 * Preps post arguments for create/save
	 *
	 * @param string $type Type of record to create - manual or schedule
	 * @param array $args Post type args
	 * @param array $meta Post meta
	 *
	 * @return array
	 */
	public function prep_post_args( $type, $args, $meta = array() ) {
		$post = array(
			'post_title'     => $this->generate_title( $type, $this->origin, $meta['frequency'], $args->parent ),
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
			// skip arrays that are empty
			if ( is_array( $value ) && empty( $value ) ) {
				continue;
			}

			// trim scalars
			if ( is_scalar( $value ) ) {
				$value = trim( $value );
			}

			// if the value is blank or null, let's avoid inserting it
			if ( null === $value || '' === $value ) {
				continue;
			}

			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		$args = (object) $args;
		$meta = (object) $meta;

		if ( 'schedule' === $type ) {
			$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $meta->frequency ) );
			if ( ! $frequency ) {
				return tribe_error( 'core:aggregator:invalid-record-frequency', $meta );
			}

			// Setup the post_content as the Frequency (makes it easy to fetch by frequency)
			$post['post_content'] = $frequency->id;
		}

		return $post;
	}

	/**
	 * A simple method to create a Title for the Records
	 *
	 * @param mixed $Nparams This method accepts any number of params, they must be string compatible
	 *
	 * @return string
	 */
	public function generate_title() {
		$parts = func_get_args();
		return __( 'Record: ', 'the-events-calendar' ) . implode( ' ', array_filter( $parts ) );
	}

	/**
	 * Creates a schedule record based on the import record
	 *
	 * @return boolean|Tribe_Error
	 */
	public function create_schedule_record() {
		$post = array(
			'post_title'     => $this->generate_title( $this->type, $this->origin, $this->meta['frequency'] ),
			'post_type'      => $this->post->post_type,
			'ping_status'    => $this->post->ping_status,
			'post_mime_type' => $this->post->post_mime_type,
			'post_date'      => current_time( 'mysql' ),
			'post_status'    => Tribe__Events__Aggregator__Records::$status->schedule,
			'post_parent'    => 0,
			'meta_input'     => array(),
		);

		foreach ( $this->meta as $key => $value ) {
			// don't propagate these meta keys to the scheduled record
			if (
				'preview' === $key
				|| 'activity' === $key
				|| 'ids_to_import' === $key
			) {
				continue;
			}

			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		// associate this child with the schedule
		$post['meta_input'][ self::$meta_key_prefix . 'recent_child' ] = $this->post->ID;

		$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $this->meta['frequency'] ) );
		if ( ! $frequency ) {
			return tribe_error( 'core:aggregator:invalid-record-frequency', $meta );
		}

		// Setups the post_content as the Frequency (makes it easy to fetch by frequency)
		$post['post_content'] = $frequency->id;

		$this->watch_for_db_errors();

		// create schedule post
		$schedule_id = wp_insert_post( $post );

		// if the schedule creation failed, bail
		if ( is_wp_error( $schedule_id ) ) {
			return tribe_error( 'core:aggregator:save-schedule-failed' );
		}

		$this->maybe_add_meta_via_pre_wp_44_method( $schedule_id, $post['meta_input'] );

		if ( $this->db_errors_happened() ) {
			wp_delete_post( $schedule_id );

			return tribe_error( 'core:aggregator:save-schedule-failed' );
		}

		$update_args = array(
			'ID' => $this->post->ID,
			'post_parent' => $schedule_id,
		);

		// update the parent of the import we are creating the schedule for. If that fails, delete the
		// corresponding schedule and bail
		if ( ! wp_update_post( $update_args ) ) {
			wp_delete_post( $schedule_id, true );

			return tribe_error( 'core:aggregator:save-schedule-failed' );
		}

		$this->post->post_parent = $schedule_id;

		return Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $schedule_id );
	}

	/**
	 * Creates a child record based on the import record
	 *
	 * @return boolean|Tribe_Error|Tribe__Events__Aggregator__Record__Abstract
	 */
	public function create_child_record() {
		$post = array(
			// Stores the Key under `post_title` which is a very forgiving type of column on `wp_post`
			'post_title'     => $this->generate_title( $this->type, $this->origin, $this->meta['frequency'], $this->post->ID ),
			'post_type'      => $this->post->post_type,
			'ping_status'    => $this->post->ping_status,
			'post_mime_type' => $this->post->post_mime_type,
			'post_date'      => current_time( 'mysql' ),
			'post_status'    => Tribe__Events__Aggregator__Records::$status->draft,
			'post_parent'    => $this->id,
			'meta_input'     => array(),
		);

		foreach ( $this->meta as $key => $value ) {
			if ( 'activity' === $key ) {
				// don't copy the parent activity into the child record
				continue;
			}
			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $this->meta['frequency'] ) );
		if ( ! $frequency ) {
			return tribe_error( 'core:aggregator:invalid-record-frequency', $post['meta_input'] );
		}

		// Setup the post_content as the Frequency (makes it easy to fetch by frequency)
		$post['post_content'] = $frequency->id;

		$this->watch_for_db_errors();

		// create schedule post
		$child_id = wp_insert_post( $post );

		// if the schedule creation failed, bail
		if ( is_wp_error( $child_id ) ) {
			return tribe_error( 'core:aggregator:save-child-failed' );
		}

		$this->maybe_add_meta_via_pre_wp_44_method( $child_id, $post['meta_input'] );

		if ( $this->db_errors_happened() ) {
			wp_delete_post( $child_id );

			return tribe_error( 'core:aggregator:save-child-failed' );
		}

		// track the most recent child that was spawned
		$this->update_meta( 'recent_child', $child_id );

		return Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $child_id );
	}

	/**
	 * If using WP < 4.4, we need to add meta to the post via update_post_meta
	 *
	 * @param int $id Post id to add data to
	 * @param array $meta Meta to add to the post
	 */
	public function maybe_add_meta_via_pre_wp_44_method( $id, $meta ) {
		if ( -1 !== version_compare( get_bloginfo( 'version' ), '4.4' ) ) {
			return;
		}

		foreach ( $meta as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
	}

	/**
	 * Queues the import on the Aggregator service
	 *
	 * @see Tribe__Events__Aggregator__API__Import::create()
	 *
	 * @return stdClass|WP_Error|int A response object, a `WP_Error` instance on failure or a record
	 *                               post ID if the record had to be re-scheduled due to HTTP request
	 *                               limit.
	 */
	public function queue_import( $args = array() ) {
		$aggregator = tribe( 'events-aggregator.main' );

		$is_previewing = (
			! empty( $_GET['action'] )
			&& (
				'tribe_aggregator_create_import' === $_GET['action']
				|| 'tribe_aggregator_preview_import' === $_GET['action']
			)
		);

		$error = null;

		$defaults = array(
			'type'     => $this->meta['type'],
			'origin'   => $this->meta['origin'],
			'source'   => isset( $this->meta['source'] ) ? $this->meta['source'] : '',
			'callback' => $is_previewing ? null : site_url( '/event-aggregator/insert/?key=' . urlencode( $this->meta['hash'] ) ),
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

		if ( ! empty( $this->meta['end'] ) ) {
			$defaults['end'] = $this->meta['end'];
		}

		if ( ! empty( $this->meta['radius'] ) ) {
			$defaults['radius'] = $this->meta['radius'];
		}

		if ( $is_previewing ) {
			$defaults['preview'] = true;
		}

		$args = wp_parse_args( $args, $defaults );

		// create the import on the Event Aggregator service
		$response = $aggregator->api( 'import' )->create( $args );

		// if the Aggregator API returns a WP_Error, set this record as failed
		if ( is_wp_error( $response ) ) {
			// if the error is just a reschedule set this record as pending
			/** @var WP_Error $response */
			if ( 'core:aggregator:http_request-limit' === $response->get_error_code() ) {
				return $this->set_status_as_pending();
			} else {
				$error = $response;

				return $this->set_status_as_failed( $error );
			}
		}

		// if the Aggregator response has an unexpected format, set this record as failed
		if ( empty( $response->message_code ) ) {
			return $this->set_status_as_failed( tribe_error( 'core:aggregator:invalid-service-response' ) );
		}

		// if the Import creation was unsuccessful, set this record as failed
		if (
			'success:create-import' != $response->message_code
			&& 'queued' != $response->message_code
		) {
			$error = new WP_Error(
				$response->message_code,
				Tribe__Events__Aggregator__Errors::build(
					esc_html__( $response->message, 'the-events-calendar' ),
					empty( $response->data->message_args ) ? array() : $response->data->message_args
				)
			);
			return $this->set_status_as_failed( $error );
		}

		// if the Import creation didn't provide an import id, the response was invalid so mark as failed
		if ( empty( $response->data->import_id ) ) {
			return $this->set_status_as_failed( tribe_error( 'core:aggregator:invalid-service-response' ) );
		}

		// only set as pending if we aren't previewing the record
		if ( ! $is_previewing ) {
			// if we get here, we're good! Set the status to pending
			$this->set_status_as_pending();
		}

		// store the import id
		update_post_meta( $this->id, self::$meta_key_prefix . 'import_id', $response->data->import_id );

		return $response;
	}

	public function get_import_data() {
		$aggregator = tribe( 'events-aggregator.main' );
		$data = array();

		// For now only apply this to the URL type
		if ( 'url' === $this->type ) {
			$data = array(
				'start' => $this->meta['start'],
				'end' => $this->meta['end'],
			);
		}

		return $aggregator->api( 'import' )->get( $this->meta['import_id'], $data );
	}

	public function delete( $force = false ) {
		if ( $this->is_manual ) {
			return tribe_error( 'core:aggregator:delete-record-failed', array( 'record' => $this ), array( $this->id ) );
		}

		return wp_delete_post( $this->id, $force );
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


		$status = wp_update_post( array(
			'ID' => $this->id,
			'post_status' => Tribe__Events__Aggregator__Records::$status->{ $status },
		) );

		if ( ! is_wp_error( $status ) && ! empty( $this->post->post_parent ) ) {
			wp_update_post( array(
				'ID' => $this->post->post_parent,
				'post_modified' => date( Tribe__Date_Utils::DBDATETIMEFORMAT, current_time( 'timestamp' ) ),
			) );
		}

		return $status;
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

	/**
	 * A quick method to fetch the Child Records to the current on this class
	 *
	 * @param  array  $args WP_Query Arguments
	 *
	 * @return WP_Query|WP_Error
	 */
	public function query_child_records( $args = array() ) {
		$defaults = array();
		$args = (object) wp_parse_args( $args, $defaults );

		// Force the parent
		$args->post_parent = $this->id;

		return Tribe__Events__Aggregator__Records::instance()->query( $args );
	}

	/**
	 * A quick method to fetch the Child Records by Status
	 *
	 * @param string $status Which status, must be a valid EA status
	 *
	 * @return WP_Query|WP_Error|bool
	 */
	public function get_child_record_by_status( $status = 'success', $qty = -1 ) {
		$statuses = Tribe__Events__Aggregator__Records::$status;

		if ( ! isset( $statuses->{ $status } ) && 'trash' !== $status ) {
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
			'type'    => Tribe__Events__Aggregator__Errors::$comment_type,
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
		$today = getdate();
		$args = array(
			// Resets the Post ID
			'post_id' => null,
			'number' => 1,
			'date_query' => array(
				array(
					'year'  => $today['year'],
					'month' => $today['mon'],
					'day'   => $today['mday'],
				),
			),
		);

		// Tries To Fetch Comments for today
		$todays_errors = $this->get_errors( $args );

		if ( ! empty( $todays_errors ) ) {
			return false;
		}

		$args = array(
			'comment_post_ID' => $this->id,
			'comment_author'  => $error->get_error_code(),
			'comment_content' => $error->get_error_message(),
			'comment_type'    => Tribe__Events__Aggregator__Errors::$comment_type,
		);

		return wp_insert_comment( $args );
	}

	/**
	 * Verifies if this Schedule Record can create a new Child Record
	 * @return boolean
	 */
	public function is_schedule_time() {
		if ( tribe_is_truthy( getenv( 'TRIBE_DEBUG_OVERRIDE_SCHEDULE' ) ) ) {
			return true;
		}

		// If we are not on a Schedule Type
		if ( ! $this->is_schedule ) {
			return false;
		}

		// If we are not dealing with the Record Schedule
		if ( $this->post->post_status !== Tribe__Events__Aggregator__Records::$status->schedule ) {
			return false;
		}

		// In some cases the scheduled import may be inactive and should not run during cron
		if ( false === $this->frequency ) {
			return false;
		}

		// It's never time for On Demand schedule, bail!
		if ( ! isset( $this->frequency->id ) || 'on_demand' === $this->frequency->id ) {
			return false;
		}

		$current = time();
		$last    = strtotime( $this->post->post_modified_gmt );
		$next    = $last + $this->frequency->interval;

		// Only do anything if we have one of these metas
		if ( ! empty( $this->meta['schedule_day'] ) || ! empty( $this->meta['schedule_time'] ) ) {
			// Setup to avoid notices
			$maybe_next = 0;

			// Now depending on the type of frequency we build the
			switch ( $this->frequency->id ) {
				case 'daily':
					$time_string = date( 'Y-m-d' ) . ' ' . $this->meta['schedule_time'];
					$maybe_next  = strtotime( $time_string );
					break;
				case 'weekly':
					$start_week    = date( 'Y-m-d', strtotime( '-' . date( 'w' ) . ' days' ) );
					$scheduled_day = date( 'Y-m-d', strtotime( $start_week . ' +' . ( (int) $this->meta['schedule_day'] - 1 ) . ' days' ) );
					$time_string   = date( 'Y-m-d', strtotime( $scheduled_day ) ) . ' ' . $this->meta['schedule_time'];
					$maybe_next    = strtotime( $time_string );
					break;
				case 'monthly':
					$time_string = date( 'Y-m-' ) . $this->meta['schedule_day'] . ' ' . $this->meta['schedule_time'];
					$maybe_next  = strtotime( $time_string );
					break;
			}

			// If our Next date based on Last run is bigger than the scheduled time it means we bail
			if ( $maybe_next > $next ) {
				$next = $maybe_next;
			}
		}

		return $current > $next;
	}

	/**
	 * Verifies if this Record can pruned
	 * @return boolean
	 */
	public function has_passed_retention_time() {
		// Bail if we are trying to prune a Schedule Record
		if ( Tribe__Events__Aggregator__Records::$status->schedule === $this->post->post_status ) {
			return false;
		}

		$current = time();
		$created = strtotime( $this->post->post_date_gmt );

		// Prevents Pending that is younger than 1 hour to be pruned
		if (
			Tribe__Events__Aggregator__Records::$status->pending === $this->post->post_status &&
			$current > $created + HOUR_IN_SECONDS
		) {
			return false;
		}

		$prune = $created + Tribe__Events__Aggregator__Records::instance()->get_retention();

		return $current > $prune;
	}

	/**
	 * Get info about the source, via and title
	 *
	 * @return array
	 */
	public function get_source_info() {
		if ( in_array( $this->origin, array( 'ics', 'csv' ) ) ) {
			if ( empty( $this->meta['source_name'] ) ) {
				$file = get_post( $this->meta['file'] );
				$title = $file instanceof WP_Post ? $file->post_title : sprintf( esc_html__( 'Deleted Attachment: %d', 'the-events-calendar' ), $this->meta['file'] );
			} else {
				$title = $this->meta['source_name'];
			}

			$via = $this->get_label();
		} else {
			if ( empty( $this->meta['source_name'] ) ) {
				$title = $this->meta['source'];
			} else {
				$title = $this->meta['source_name'];
			}

			$via = $this->get_label();
			if ( in_array( $this->origin, array( 'facebook', 'meetup' ) ) ) {
				$via = '<a href="' . esc_url( $this->meta['source'] ) . '" target="_blank">' . esc_html( $via ) . '<span class="screen-reader-text">' . __( ' (opens in a new window)', 'the-events-calendar' ) . '</span></a>';
			}
		}

		return array( 'title' => $title, 'via' => $via );
	}

	/**
	 * Fetches the status message for the last import attempt on (scheduled) records
	 *
	 * @param string $type Type of message to fetch
	 *
	 * @return string
	 */
	public function get_last_import_status( $type = 'error' ) {
		$status = empty( $this->meta['last_import_status'] ) ? null : $this->meta['last_import_status'];

		if ( ! $status ) {
			return;
		}

		if ( 0 !== strpos( $status, $type ) ) {
			return;
		}

		if ( 'error:usage-limit-exceeded' === $status ) {
			return __( 'When this import was last scheduled to run, the daily limit for your Event Aggregator license had already been reached.', 'the-events-calendar' );
		}

		return tribe( 'events-aggregator.service' )->get_service_message( $status );
	}

	/**
	 * Updates the source name on the import record and its parent (if the parent exists)
	 *
	 * @param string $source_name Source name to set on the import record
	 */
	public function update_source_name( $source_name ) {
		// if we haven't received a source name, bail
		if ( empty( $source_name ) ) {
			return;
		}

		$this->update_meta( 'source_name', $source_name );

		if ( empty( $this->post->post_parent ) ) {
			return;
		}

		$parent_record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $this->post->post_parent );

		if ( tribe_is_error( $parent_record ) ) {
			return;
		}

		$parent_record->update_meta( 'source_name', $source_name );
	}

	/**
	 * Queues events, venues, and organizers for insertion
	 *
	 * @param array $data Import data
	 *
	 * @return array|WP_Error|Tribe__Events__Aggregator__Record__Queue
	 */
	public function process_posts( $data = array() ) {
		if ( 'manual' === $this->type ) {
			/** @var Tribe__Events__Aggregator__Service $service */
			$service = tribe( 'events-aggregator.service' );
			$service->confirm_import( $this->meta );
		}

		if ( $this->has_queue() ) {
			$queue = new Tribe__Events__Aggregator__Record__Queue( $this );
			return $queue->process();
		}

		$items = $this->prep_import_data( $data );

		if ( is_wp_error( $items ) ) {
			$this->set_status_as_failed( $items );
			return $items;
		}

		$queue = new Tribe__Events__Aggregator__Record__Queue( $this, $items );

		return $queue->process();
	}

	/**
	 * Returns whether or not the record has a queue
	 *
	 * @return bool
	 */
	public function has_queue() {
		return ! empty( $this->meta[ Tribe__Events__Aggregator__Record__Queue::$queue_key ] );
	}

	public function get_event_count( $type = null ) {
		if ( is_null( $type ) ) {
			return 0;
		}

		if ( empty( $this->meta['activity'] ) || ! $this->meta['activity'] instanceof Tribe__Events__Aggregator__Record__Activity ) {
			return 0;
		}

		switch ( $type ) {
			case 'total':
				return $this->meta['activity']->count( 'event', 'created' ) + $this->meta['activity']->count( 'event', 'updated' );
				break;

			default:
				return $this->meta['activity']->count( 'event', $type );
				break;
		}
	}

	/**
	 * Handles import data before queuing
	 *
	 * Ensures the import record source name is accurate, checks for errors, and limits import items
	 * based on selection
	 *
	 * @param array $data Import data
	 *
	 * @return array|WP_Error
	 */
	public function prep_import_data( $data = array() ) {
		if ( empty( $data ) ) {
			$data = $this->get_import_data();
		}

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$this->update_source_name( empty( $data->data->source_name ) ? null : $data->data->source_name );

		if ( empty( $this->meta['finalized'] ) ) {
			return tribe_error( 'core:aggregator:record-not-finalized' );
		}

		if ( ! isset( $data->data->events ) ) {
			return 'fetch';
		}

		$items = $this->filter_data_by_selected( $data->data->events );

		return $items;
	}

	/**
	 * Inserts events, venues, and organizers for the Import Record
	 *
	 * @param array $data Dummy data var to allow children to optionally react to passed in data
	 *
	 * @return array|WP_Error
	 */
	public function insert_posts( $items = array() ) {
		add_filter( 'tribe-post-origin', array( Tribe__Events__Aggregator__Records::instance(), 'filter_post_origin' ), 10 );

		// Creates an Activity to log what Happened
		$activity = new Tribe__Events__Aggregator__Record__Activity();

		$args = array(
			'post_status' => $this->meta['post_status'],
		);

		$unique_field = $this->get_unique_field();
		$existing_ids = $this->get_existing_ids_from_import_data( $items );

		// cache
		$possible_parents = array();
		$found_organizers = array();
		$found_venues     = array();

		$origin = $this->meta['origin'];
		$show_map_setting = tribe_is_truthy( tribe( 'events-aggregator.settings' )->default_map( $origin ) );
		$update_authority_setting = tribe( 'events-aggregator.settings' )->default_update_authority( $origin );

		$import_settings = tribe( 'events-aggregator.settings' )->default_settings_import( $origin );
		$should_import_settings = tribe_is_truthy( $import_settings ) ? true : false;

		$unique_inserted = array();

		foreach ( $items as $item ) {
			$event = Tribe__Events__Aggregator__Event::translate_service_data( $item );

			// Configure the Post Type (enforcing)
			$event['post_type'] = Tribe__Events__Main::POSTTYPE;

			// Set the event ID if it can be set
			if (
				$unique_field
				&& isset( $event[ $unique_field['target'] ] )
				&& isset( $existing_ids[ $event[ $unique_field['target'] ] ] )
			) {
				$event['ID'] = $existing_ids[ $event[ $unique_field['target'] ] ]->post_id;
			}

			// Checks if we need to search for Global ID
			if ( ! empty( $item->global_id ) ) {
				$global_event = Tribe__Events__Aggregator__Event::get_post_by_meta( 'global_id', $item->global_id );

				// If we found something we will only update that Post
				if ( $global_event ) {
					$event['ID'] = $global_event->ID;
				}
			}

			// Only set the post status if there isn't an ID
			if ( empty( $event['ID'] ) ) {
				$event['post_status'] = $args['post_status'];
			}

			/**
			 * Should events that have previously been imported be overwritten?
			 *
			 * By default this is turned off (since it would reset the post status, description
			 * and any other fields that have subsequently been edited) but it can be enabled
			 * by returning true on this filter.
			 *
			 * @var bool $overwrite
			 * @var int  $event_id
			 */
			if ( ! empty( $event['ID'] ) && 'retain' === $update_authority_setting ) {
				// Log this Event was Skipped
				$activity->add( 'event', 'skipped', $event['ID'] );
				continue;
			}

			if ( $show_map_setting ) {
				$event['EventShowMap'] = $show_map_setting || (bool) isset( $event['show_map'] );
				if ( $this->has_import_policy_for( $origin, 'show_map_link' ) ) {
					$event['EventShowMapLink'] = isset( $event['show_map_link'] ) ? (bool) $event['show_map_link'] : $show_map_setting;
				} else {
					$event['EventShowMapLink'] = $show_map_setting;
				}
			}
			unset( $event['show_map'], $event['show_map_link'] );

			if ( $should_import_settings && isset( $event['hide_from_listings'] ) ) {
				if ( $event['hide_from_listings'] == true ) {
					$event['EventHideFromUpcoming'] = 'yes';
				}
				unset( $event['hide_from_listings'] );
			}

			if ( $should_import_settings && isset( $event['sticky'] ) ) {
				if ( $event['sticky'] == true ) {
					$event['EventShowInCalendar'] = 'yes';
					$event['menu_order']          = - 1;
				}
				unset( $event['sticky'] );
			}

			if ( ! $should_import_settings ) {
				unset( $event['feature_event'] );
			}

			// set the parent
			if ( ! empty( $event['ID'] ) && ( $id = wp_get_post_parent_id( $event['ID'] ) ) ) {
				$event['post_parent'] = $id;
			} elseif ( ! empty( $event['parent_uid'] ) && ( $k = array_search( $event['parent_uid'], $possible_parents ) ) ) {
				$event['post_parent'] = $k;
			}

			// Do we have an existing venue for this event that we should preserve?
			// @todo review: should we care about the potential for multiple venue IDs?
			if (
				! empty( $event['ID'] )
				&& 'preserve_changes' === $update_authority_setting
				&& $existing_venue_id = tribe_get_venue_id( $event['ID'] )
			) {
				$event['EventVenueID'] = $existing_venue_id;
				unset( $event['Venue'] );
			}

			// if we should create a venue or use existing
			if ( ! empty( $event['Venue']['Venue'] ) ) {
				if ( ! empty( $item->venue->global_id ) || in_array( $this->origin, array( 'ics', 'csv', 'gcal' ) ) ) {
					// Pre-set for ICS based imports
					$venue = false;
					if ( ! empty( $item->venue->global_id ) ) {
						// Did we find a Post with a matching Global ID in History
						$venue = Tribe__Events__Aggregator__Event::get_post_by_meta( 'global_id_lineage', $item->venue->global_id );
					}

					// Save the Venue Data for Updating
					$venue_data = $event['Venue'];

					if ( isset( $item->venue->description ) ) {
						$venue_data['Description'] = $item->venue->description;
					}

					if ( isset( $item->venue->excerpt ) ) {
						$venue_data['Excerpt'] = $item->venue->excerpt;
					}

					if ( isset( $item->venue->image ) ) {
						$venue_data['FeaturedImage'] = $item->venue->image;
					}

					if ( $venue ) {
						$venue_id = $event['EventVenueID'] = $venue_data['ID'] = $venue->ID;
						$found_venues[ $venue->ID ] = $event['Venue']['Venue'];

						// Here we might need to update the Venue depending on the main GlobalID
						if ( 'retain' === $update_authority_setting ) {
							// When we get here we say that we skipped an Venue
							$activity->add( 'venue', 'skipped', $venue->ID );
						} else {
							if ( 'preserve_changes' === $update_authority_setting ) {
								$venue_data = Tribe__Events__Aggregator__Event::preserve_changed_fields( $venue_data );
							}

							add_filter( 'tribe_tracker_enabled', '__return_false' );

							// Update the Venue
							Tribe__Events__Venue::instance()->update( $venue->ID, $venue_data );

							// Tell that we updated the Venue to the activity tracker
							$activity->add( 'venue', 'updated', $venue->ID );

							remove_filter( 'tribe_tracker_enabled', '__return_false' );
						}
					} else {
						$venue_id = array_search( $event['Venue']['Venue'], $found_venues );
						if ( ! $venue_id ) {
							$venue = get_page_by_title( $event['Venue']['Venue'], 'OBJECT', Tribe__Events__Venue::POSTTYPE );

							if ( $venue ) {
								$venue_id = $venue->ID;
								$found_venues[ $venue_id ] = $event['Venue']['Venue'];
							}
						}

						// We didn't find any matching Venue for the provided one
						if ( ! $venue_id ) {
							$event['Venue']['ShowMap']     = $show_map_setting;
							$event['Venue']['ShowMapLink'] = $show_map_setting;
							$venue_id = $event['EventVenueID'] = Tribe__Events__Venue::instance()->create( $event['Venue'], $this->meta['post_status'] );

							$found_venues[ $event['EventVenueID'] ] = $event['Venue']['Venue'];

							// Log this Venue was created
							$activity->add( 'venue', 'created', $event['EventVenueID'] );

							// Create the Venue Global ID
							if ( ! empty( $item->venue->global_id ) ) {
								update_post_meta( $event['EventVenueID'], Tribe__Events__Aggregator__Event::$global_id_key, $item->venue->global_id );
							}

							// Create the Venue Global ID History
							if ( ! empty( $item->venue->global_id_lineage ) ) {
								foreach ( $item->venue->global_id_lineage as $gid ) {
									add_post_meta( $event['EventVenueID'], Tribe__Events__Aggregator__Event::$global_id_lineage_key, $gid );
								}
							}
						} else {
							$event['EventVenueID'] = $venue_data['ID'] = $venue_id;

							// Here we might need to update the Venue depending we found something based on old code
							if ( 'retain' === $update_authority_setting ) {
								// When we get here we say that we skipped an Venue
								$activity->add( 'venue', 'skipped', $venue_id );
							} else {
								if ( 'preserve_changes' === $update_authority_setting ) {
									$venue_data = Tribe__Events__Aggregator__Event::preserve_changed_fields( $venue_data );
								}

								add_filter( 'tribe_tracker_enabled', '__return_false' );

								// Update the Venue
								Tribe__Events__Venue::instance()->update( $venue_id, $venue_data );

								// Tell that we updated the Venue to the activity tracker
								$activity->add( 'venue', 'updated', $venue_id );

								remove_filter( 'tribe_tracker_enabled', '__return_false' );
							}
						}
					}
				}

				// Remove the Venue to avoid duplicates
				unset( $event['Venue'] );
			}

			// Do we have an existing organizer(s) for this event that we should preserve?
			if (
				! empty( $event['ID'] )
				&& 'preserve_changes' === $update_authority_setting
				&& $existing_organizer_ids = tribe_get_organizer_ids( $event['ID'] )
			) {
				$event['EventOrganizerID'] = $existing_organizer_ids;
				unset( $event['Organizer'] );
			}

			//if we should create an organizer or use existing
			if ( ! empty( $event['Organizer']['Organizer'] ) ) {
				if ( ! empty( $item->organizer->global_id ) || in_array( $this->origin, array( 'ics', 'csv', 'gcal' ) ) ) {
					// Pre-set for ICS based imports
					$organizer = false;
					if ( ! empty( $item->organizer->global_id ) ) {
						// Did we find a Post with a matching Global ID in History
						$organizer = Tribe__Events__Aggregator__Event::get_post_by_meta( 'global_id_lineage', $item->organizer->global_id );
					}

					// Save the Organizer Data for Updating
					$organizer_data = $event['Organizer'];

					if ( isset( $item->organizer->description ) ) {
						$organizer_data['Description'] = $item->organizer->description;
					}

					if ( isset( $item->organizer->excerpt ) ) {
						$organizer_data['Excerpt'] = $item->organizer->excerpt;
					}

					if ( $organizer ) {
						$organizer_id = $event['EventOrganizerID'] = $organizer_data['ID'] = $organizer->ID;
						$found_organizers[ $organizer->ID ] = $event['Organizer']['Organizer'];

						// Here we might need to update the Organizer depending we found something based on old code
						if ( 'retain' === $update_authority_setting ) {
							// When we get here we say that we skipped an Organizer
							$activity->add( 'organizer', 'skipped', $organizer->ID );
						} else {
							if ( 'preserve_changes' === $update_authority_setting ) {
								$organizer_data = Tribe__Events__Aggregator__Event::preserve_changed_fields( $organizer_data );
							}

							add_filter( 'tribe_tracker_enabled', '__return_false' );

							// Update the Organizer
							Tribe__Events__Organizer::instance()->update( $organizer->ID, $organizer_data );

							remove_filter( 'tribe_tracker_enabled', '__return_false' );

							// Tell that we updated the Organizer to the activity tracker
							$activity->add( 'organizer', 'updated', $organizer->ID );
						}
					} else {
						$organizer_id = array_search( $event['Organizer']['Organizer'], $found_organizers );
						if ( ! $organizer_id ) {
							$organizer = get_page_by_title( $event['Organizer']['Organizer'], 'OBJECT', Tribe__Events__Organizer::POSTTYPE );

							if ( $organizer ) {
								$organizer_id = $organizer->ID;
								$found_organizers[ $organizer_id ] = $event['Organizer']['Organizer'];
							}
						}

						// We didn't find any matching Organizer for the provided one
						if ( ! $organizer_id ) {
							$organizer_id = $event['EventOrganizerID'] = Tribe__Events__Organizer::instance()->create( $event['Organizer'], $this->meta['post_status'] );
							$found_organizers[ $event['EventOrganizerID'] ] = $event['Organizer']['Organizer'];

							// Log this Organizer was created
							$activity->add( 'organizer', 'created', $event['EventOrganizerID'] );

							// Create the Organizer Global ID
							if ( ! empty( $item->organizer->global_id ) ) {
								update_post_meta( $event['EventOrganizerID'], Tribe__Events__Aggregator__Event::$global_id_key, $item->organizer->global_id );
							}

							// Create the Organizer Global ID History
							if ( ! empty( $item->organizer->global_id_lineage ) ) {
								foreach ( $item->organizer->global_id_lineage as $gid ) {
									add_post_meta( $event['EventOrganizerID'], Tribe__Events__Aggregator__Event::$global_id_lineage_key, $gid );
								}
							}
						} else {
							$event['EventOrganizerID'] = $organizer_data['ID'] = $organizer_id;

							// Here we might need to update the Organizer depending we found something based on old code
							if ( 'retain' === $update_authority_setting ) {
								// When we get here we say that we skipped an Organizer
								$activity->add( 'organizer', 'skipped', $organizer_id );

							} else {
								if ( 'preserve_changes' === $update_authority_setting ) {
									$organizer_data = Tribe__Events__Aggregator__Event::preserve_changed_fields( $organizer_data );
								}

								add_filter( 'tribe_tracker_enabled', '__return_false' );

								// Update the Organizer
								Tribe__Events__Organizer::instance()->update( $organizer_id, $organizer_data );

								remove_filter( 'tribe_tracker_enabled', '__return_false' );

								// Tell that we updated the Organizer to the activity tracker
								$activity->add( 'organizer', 'updated', $organizer_id );
							}
						}
					}
				}

				// Remove the Organizer to avoid duplicates
				unset( $event['Organizer'] );
			}

			/**
			 * Filters the event data before any sort of saving of the event
			 *
			 * @param array $event Event data to save
			 * @param Tribe__Events__Aggregator__Record__Abstract Importer record
			 */
			$event = apply_filters( 'tribe_aggregator_before_save_event', $event, $this );

			if ( ! empty( $event['ID'] ) ) {
				if ( 'preserve_changes' === $update_authority_setting ) {
					$event = Tribe__Events__Aggregator__Event::preserve_changed_fields( $event );
				}

				add_filter( 'tribe_tracker_enabled', '__return_false' );

				/**
				 * Filters the event data before updating event
				 *
				 * @param array $event Event data to save
				 * @param Tribe__Events__Aggregator__Record__Abstract Importer record
				 */
				$event = apply_filters( 'tribe_aggregator_before_update_event', $event, $this );

				$event['ID'] = tribe_update_event( $event['ID'], $event );

				// since the Event API only supports the _setting_ of these meta fields, we need to manually
				// delete them rather than relying on Tribe__Events__API::saveEventMeta()
				if ( isset( $event['EventShowMap'] ) && ! tribe_is_truthy( $event['EventShowMap'] ) ) {
					delete_post_meta( $event['ID'], '_EventShowMap' );
				}

				if ( isset( $event['EventShowMapLink'] ) && ! tribe_is_truthy( $event['EventShowMapLink'] ) ) {
					delete_post_meta( $event['ID'], '_EventShowMapLink' );
				}

				remove_filter( 'tribe_tracker_enabled', '__return_false' );

				// Log that this event was updated
				$activity->add( 'event', 'updated', $event['ID'] );
			} else {
				/**
				 * Filters the event data before inserting event
				 *
				 * @param array $event Event data to save
				 * @param Tribe__Events__Aggregator__Record__Abstract Importer record
				 */
				$event = apply_filters( 'tribe_aggregator_before_insert_event', $event, $this );
				$event['ID'] = tribe_create_event( $event );

				// Log this event was created
				$activity->add( 'event', 'created', $event['ID'] );

				// Create the Event Global ID
				if ( ! empty( $item->global_id ) ) {
					update_post_meta( $event['ID'], Tribe__Events__Aggregator__Event::$global_id_key, $item->global_id );
				}

				// Create the Event Global ID History
				if ( ! empty( $item->global_id_lineage ) ) {
					foreach ( $item->global_id_lineage as $gid ) {
						add_post_meta( $event['ID'], Tribe__Events__Aggregator__Event::$global_id_lineage_key, $gid );
					}
				}
			}

			Tribe__Events__Aggregator__Records::instance()->add_record_to_event( $event['ID'], $this->id, $this->origin );

			// Add post parent possibility
			if ( empty( $event['parent_uid'] ) ) {
				$possible_parents[ $event['ID'] ] = $event[ $unique_field['target'] ];
			}

			// Check for legacy Unique ID (now we try to use Global ID)
			if ( ! empty( $event[ $unique_field['target'] ] ) ) {
				update_post_meta( $event['ID'], "_{$unique_field['target']}", $event[ $unique_field['target'] ] );
			}

			// Save the meta data in case of updating to pro later on
			if ( ! empty( $event['EventRecurrenceRRULE'] ) ) {
				update_post_meta( $event['ID'], '_EventRecurrenceRRULE', $event['EventRecurrenceRRULE'] );
			}

			// Are there any existing event categories for this event?
			$terms = wp_get_object_terms( $event['ID'], Tribe__Events__Main::TAXONOMY );

			if ( is_wp_error( $terms ) ) {
				$terms = array();
			}

			// If so, should we preserve those categories?
			if ( ! empty( $terms ) && 'preserve_changes' === $update_authority_setting ) {
				$terms = wp_list_pluck( $terms, 'term_id' );
				unset( $event['categories'] );
			}

			if ( ! empty( $event['categories'] ) ) {
				foreach ( $event['categories'] as $cat ) {
					if ( ! $term = term_exists( $cat, Tribe__Events__Main::TAXONOMY ) ) {
						$term = wp_insert_term( $cat, Tribe__Events__Main::TAXONOMY );
						if ( ! is_wp_error( $term ) ) {
							$terms[] = (int) $term['term_id'];

							// Track that we created an event category
							$activity->add( 'cat', 'created', $term['term_id'] );
						}
					} else {
						$terms[] = (int) $term['term_id'];
					}
				}
			}

			$tags = array();
			if ( ! empty( $event['tags'] ) ) {
				foreach ( $event['tags'] as $tag_name ) {
					if ( ! $tag = term_exists( $tag_name, 'post_tag' ) ) {
						$tag = wp_insert_term( $tag_name, 'post_tag' );
						if ( ! is_wp_error( $tag ) ) {
							$tags[] = (int) $tag['term_id'];

							// Track that we created a post tag
							$activity->add( 'tag', 'created', $tag['term_id'] );
						}
					} else {
						$tags[] = (int) $tag['term_id'];
					}
				}
			}

			// if we are setting all events to a category specified in saved import
			if ( ! empty( $this->meta['category'] ) ) {
				$terms[] = (int) $this->meta['category'];
			}

			$normalized_categories = tribe_normalize_terms_list( $terms, Tribe__Events__Main::TAXONOMY );
			$normalized_tags = tribe_normalize_terms_list( $tags, 'post_tag' );
			wp_set_object_terms( $event['ID'], $normalized_categories, Tribe__Events__Main::TAXONOMY, false );
			wp_set_object_terms( $event['ID'], $normalized_tags, 'post_tag', false );

			// If we have a Image Field from Service
			if ( ! empty( $event['image'] ) ) {
				if ( is_object( $event['image'] ) ) {
					$image = $this->import_aggregator_image( $event );
				} else {
					$image = $this->import_image( $event );
				}

				if ( ! is_wp_error( $image ) && ! empty( $image->post_id ) ) {
					// Set as featured image
					$featured_status = set_post_thumbnail( $event['ID'], $image->post_id );

					if ( $featured_status ) {
						// Log this attachment was created
						$activity->add( 'attachment', 'created', $image->post_id );
					}
				}
			}

			// If we have a Image Field for the Organizer from Service
			if ( ! empty( $item->organizer->image ) && $organizer_id ) {
				$args = array(
					'ID' => $organizer_id,
					'image' => $item->organizer->image,
					'post_title' => get_the_title( $organizer_id ),
				);
				$image = $this->import_image( $args );

				if ( ! is_wp_error( $image ) && ! empty( $image->post_id ) ) {
					// Set as featured image
					$featured_status = set_post_thumbnail( $organizer_id, $image->post_id );

					if ( $featured_status ) {
						// Log this attachment was created
						$activity->add( 'attachment', 'created', $image->post_id );
					}
				}
			}

			// If we have a Image Field for the Venue from Service
			if ( ! empty( $item->venue->image ) && $venue_id ) {
				$args = array(
					'ID' => $venue_id,
					'image' => $item->venue->image,
					'post_title' => get_the_title( $venue_id ),
				);
				$image = $this->import_image( $args );

				if ( ! is_wp_error( $image ) && ! empty( $image->post_id ) ) {
					// Set as featured image
					$featured_status = set_post_thumbnail( $venue_id, $image->post_id );

					if ( $featured_status ) {
						// Log this attachment was created
						$activity->add( 'attachment', 'created', $image->post_id );
					}
				}
			}
		}

		remove_filter( 'tribe-post-origin', array( Tribe__Events__Aggregator__Records::instance(), 'filter_post_origin' ), 10 );

		return $activity;
	}

	/**
	 * Gets all ids that already exist in the post meta table from the provided records
	 *
	 * @param array $records Array of records
	 * @param array $data Submitted data
	 *
	 * @return array
	 */
	protected function get_existing_ids_from_import_data( $import_data ) {
		$unique_field = $this->get_unique_field();

		if ( ! $unique_field ) {
			return array();
		}

		$parent_selected_ids = array();

		if ( ! empty( $this->meta['ids_to_import'] ) && 'all' !== $this->meta['ids_to_import'] ) {
			if ( is_array( $this->meta['ids_to_import'] ) ) {
				$selected_ids = $this->meta['ids_to_import'];
			} else {
				$selected_ids = json_decode( $this->meta['ids_to_import'] );
			}
		} else {
			$selected_ids = wp_list_pluck( $import_data, $unique_field['source'] );
		}

		if ( empty( $selected_ids ) ) {
			return array();
		}

		$event_object = new Tribe__Events__Aggregator__Event;
		$existing_ids = $event_object->get_existing_ids( $this->meta['origin'], $selected_ids );

		return $existing_ids;
	}

	protected function filter_data_by_selected( $import_data ) {
		$unique_field = $this->get_unique_field();

		if ( ! $unique_field ) {
			return $import_data;
		}

		// It's safer to use Empty to check here, prevents notices
		if ( empty( $this->meta['ids_to_import'] ) ) {
			return $import_data;
		}

		if ( 'all' === $this->meta['ids_to_import'] ) {
			return $import_data;
		}

		$selected_ids = maybe_unserialize( $this->meta['ids_to_import'] );

		$selected = array();

		foreach ( $import_data as $data ) {
			if ( ! in_array( $data->{$unique_field['source']}, $selected_ids ) ) {
				continue;
			}

			$selected[] = $data;
		}

		return $selected;
	}

	protected function get_unique_field() {
		if ( ! isset( self::$unique_id_fields[ $this->meta['origin'] ] ) ) {
			return null;
		}

		return self::$unique_id_fields[ $this->meta['origin'] ];
	}

	/**
	 * Finalizes the import record for insert
	 */
	public function finalize() {
		$this->update_meta( 'finalized', true );
	}

	/**
	 * preserve Event Options
	 *
	 * @param array $event Event data
	 *
	 * @return array
	 */
	public static function preserve_event_option_fields( $event ) {
		$event_post = get_post( $event['ID'] );
		$post_meta = Tribe__Events__API::get_and_flatten_event_meta( $event['ID'] );

		// we want to preserve this option if not explicitly being overridden
		if ( ! isset( $event['EventHideFromUpcoming'] ) && isset( $post_meta['_EventHideFromUpcoming'] ) ) {
			$event['EventHideFromUpcoming'] = $post_meta['_EventHideFromUpcoming'];
		}

		// we want to preserve the existing sticky state unless it is explicitly being overridden
		if ( ! isset( $event['EventShowInCalendar'] ) && '-1' == $event_post->menu_order ) {
			$event['EventShowInCalendar'] = 'yes';
		}

		// we want to preserve the existing featured state unless it is explicitly being overridden
		if ( ! isset( $event['feature_event'] ) && isset( $post_meta['_tribe_featured'] ) ) {
			$event['feature_event'] = $post_meta['_tribe_featured'];
		}

		return $event;
	}

    /**
     * Imports an image information from EA server and creates the WP attachment object if required.
     *
     * @param array $event An event representation in the format provided by an Event Aggregator response.
     *
     * @return bool|stdClass|WP_Error An image information in the format provided by an Event Aggregator responsr or
     *                                `false` on failure.
     */
	public function import_aggregator_image( $event ) {
		// Attempt to grab the event image
		$image_import = tribe( 'events-aggregator.main' )->api( 'image' )->get( $event['image']->id );

		/**
		 * Filters the returned event image url
		 *
		 * @param array|bool $image       Attachment information
		 * @param array      $event       Event array
		 */
		$image = apply_filters( 'tribe_aggregator_event_image', $image_import, $event );

		// If there was a problem bail out
		if ( false === $image ) {
			return false;
		}

		// Verify for more Complex Errors
		if ( is_wp_error( $image ) ) {
			return $image;
		}

		return $image;
	}

	/**
	 * Imports the image contained in the event `image` field if any.
	 *
	 * @param array $event An event data in array format.
	 *
	 * @return object|bool An object with the image post ID or `false` on failure.
	 */
	public function import_image( $event ) {
		if ( empty( $event['image'] ) || ! filter_var( $event['image'], FILTER_VALIDATE_URL ) ) {
			return false;
		}

		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Set variables for storage, fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $event['image'], $matches );
		if ( ! $matches ) {
			return false;
		}

		$file_array         = array();
		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $event['image'] );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return false;
		}

		$id = media_handle_sideload( $file_array, $event['ID'], $event['post_title'] );

		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );

			return false;
		}

		return (object) array( 'post_id' => $id );
	}

	/**
	 * Whether an origin has more granulat policies concerning an import setting or not.
	 *
	 * @param string $origin
	 * @param string $setting
	 *
	 * @return bool
	 */
	protected function has_import_policy_for( $origin, $setting ) {
		return isset( $this->origin_import_policies[ $origin ] ) && in_array( $setting, $this->origin_import_policies[ $origin ] );
	}

	/**
	 * Starts monitoring the db for errors.
	 */
	protected function watch_for_db_errors() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$this->last_wpdb_error = $wpdb->last_error;

	}

	/**
	 * @return bool Whether a db error happened during the insertion of data or not.
	 */
	protected function db_errors_happened() {
		/** @var wpdb $wpdb */
		global $wpdb;

		return $wpdb->last_error !== $this->last_wpdb_error;
	}
}
