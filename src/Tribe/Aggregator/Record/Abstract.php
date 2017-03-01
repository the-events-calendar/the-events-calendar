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

		$result = wp_insert_post( $post );

		if ( ! is_wp_error( $result ) ) {
			$this->maybe_add_meta_via_pre_wp_44_method( $result, $post['meta_input'] );
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
		global $wp_version;

		if ( ! isset( $meta['type'] ) || 'schedule' !== $meta['type'] ) {
			return tribe_error( 'core:aggregator:invalid-edit-record-type', $type );
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

		// meta_input was introduced in 4.4. Deal with old versions
		if ( -1 === version_compare( $wp_version, '4.4' ) && ! is_wp_error( $result ) ) {
			foreach ( $post['meta_input'] as $key => $value ) {
				update_post_meta( $result, $key, $value );
			}
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
	 * @return boolean|WP_Error
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

		// create schedule post
		$schedule_id = wp_insert_post( $post );

		// if the schedule creation failed, bail
		if ( is_wp_error( $schedule_id ) ) {
			return tribe_error( 'core:aggregator:save-schedule-failed' );
		}

		$this->maybe_add_meta_via_pre_wp_44_method( $schedule_id, $post['meta_input'] );

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
	 * @return boolean|WP_Error|Tribe__Events__Aggregator__Record__Abstract
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
			return tribe_error( 'core:aggregator:invalid-record-frequency', $meta );
		}

		// Setup the post_content as the Frequency (makes it easy to fetch by frequency)
		$post['post_content'] = $frequency->id;

		// create schedule post
		$child_id = wp_insert_post( $post );

		// if the schedule creation failed, bail
		if ( is_wp_error( $child_id ) ) {
			return tribe_error( 'core:aggregator:save-child-failed' );
		}

		$this->maybe_add_meta_via_pre_wp_44_method( $child_id, $post['meta_input'] );

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
		return $aggregator->api( 'import' )->get( $this->meta['import_id'] );
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

		$current  = time();
		$modified = strtotime( $this->post->post_modified_gmt );
		$next     = $modified + $this->frequency->interval;

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

		//cache
		$possible_parents = array();
		$found_organizers = array();
		$found_venues     = array();

		//if we have no non recurring events the message may be different
		$non_recurring = false;

		$show_map_setting = Tribe__Events__Aggregator__Settings::instance()->default_map( $this->meta['origin'] );
		$update_authority_setting = Tribe__Events__Aggregator__Settings::instance()->default_update_authority( $this->meta['origin'] );

		$unique_inserted = array();

		foreach ( $items as $item ) {
			$event = Tribe__Events__Aggregator__Event::translate_service_data( $item );

			// set the event ID if it can be set
			if (
				$unique_field
				&& isset( $event[ $unique_field['target'] ] )
				&& isset( $existing_ids[ $event[ $unique_field['target'] ] ] )
			) {
				$event['ID'] = $existing_ids[ $event[ $unique_field['target'] ] ]->post_id;
			}

			// only set the post status if there isn't an ID
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
				$event['EventShowMap']     = $show_map_setting;
				$event['EventShowMapLink'] = $show_map_setting;
			}

			if ( empty( $event['recurrence'] ) ) {
				$non_recurring = true;
			}

			// set the parent
			if ( ! empty( $event['ID'] ) && ( $id = wp_get_post_parent_id( $event['ID'] ) ) ) {
				$event['post_parent'] = $id;
			} elseif ( ! empty( $event['parent_uid'] ) && ( $k = array_search( $event['parent_uid'], $possible_parents ) ) ) {
				$event['post_parent'] = $k;
			}

			//if we should create a venue or use existing
			if ( ! empty( $event['Venue']['Venue'] ) ) {
				$v_id = array_search( $event['Venue']['Venue'], $found_venues );
				if ( false !== $v_id ) {
					$event['EventVenueID'] = $v_id;
				} elseif ( $venue = get_page_by_title( $event['Venue']['Venue'], 'OBJECT', Tribe__Events__Main::VENUE_POST_TYPE ) ) {
					$found_venues[ $venue->ID ] = $event['Venue']['Venue'];
					$event['EventVenueID']      = $venue->ID;
				} else {
					$event['Venue']['ShowMap']     = $show_map_setting;
					$event['Venue']['ShowMapLink'] = $show_map_setting;
					$event['EventVenueID'] = Tribe__Events__Venue::instance()->create( $event['Venue'], $this->meta['post_status'] );

					// Log this Venue was created
					$activity->add( 'venue', 'created', $event['EventVenueID'] );
				}

				// Remove the Venue to avoid duplicates
				unset( $event['Venue'] );
			}

			//if we should create an organizer or use existing
			if ( ! empty( $event['Organizer']['Organizer'] ) ) {
				$o_id = array_search( $event['Organizer']['Organizer'], $found_organizers );
				if ( false !== $o_id ) {
					$event['EventOrganizerID'] = $o_id;
				} elseif ( $organizer = get_page_by_title( $event['Organizer']['Organizer'], 'OBJECT', Tribe__Events__Main::ORGANIZER_POST_TYPE ) ) {
					$found_organizers[ $organizer->ID ] = $event['Organizer']['Organizer'];
					$event['EventOrganizerID']          = $organizer->ID;
				} else {
					$event['EventOrganizerID'] = Tribe__Events__Organizer::instance()->create( $event['Organizer'], $this->meta['post_status'] );

					// Log this Organizer was created
					$activity->add( 'organizer', 'created', $event['EventOrganizerID'] );
				}

				// Remove the Organizer to avoid duplicates
				unset( $event['Organizer'] );
			}

			$event['post_type'] = Tribe__Events__Main::POSTTYPE;

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

				add_filter( 'tribe_aggregator_track_modified_fields', '__return_false' );

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
				if (
					isset( $event['EventShowMap'] )
					&& (
						empty( $event['EventShowMap'] )
						|| 'no' === $event['EventShowMap']
					)
				) {
					delete_post_meta( $event['ID'], '_EventShowMap' );
				}

				if (
					isset( $event['EventShowMapLink'] )
					&& (
						empty( $event['EventShowMapLink'] )
						|| 'no' === $event['EventShowMapLink']
					)
				) {
					delete_post_meta( $event['ID'], '_EventShowMapLink' );
				}

				remove_filter( 'tribe_aggregator_track_modified_fields', '__return_false' );

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
			}

			Tribe__Events__Aggregator__Records::instance()->add_record_to_event( $event['ID'], $this->id, $this->origin );

			//add post parent possibility
			if ( empty( $event['parent_uid'] ) ) {
				$possible_parents[ $event['ID'] ] = $event[ $unique_field['target'] ];
			}

			if ( ! empty( $event[ $unique_field['target'] ] ) ) {
				update_post_meta( $event['ID'], "_{$unique_field['target']}", $event[ $unique_field['target'] ] );
			}

			//Save the meta data in case of updating to pro later on
			if ( ! empty( $event['EventRecurrenceRRULE'] ) ) {
				update_post_meta( $event['ID'], '_EventRecurrenceRRULE', $event['EventRecurrenceRRULE'] );
			}

			$terms = array();
			if ( ! empty( $event['categories'] ) ) {
				foreach ( $event['categories'] as $cat ) {
					if ( ! $term = term_exists( $cat, Tribe__Events__Main::TAXONOMY ) ) {
						$term = wp_insert_term( $cat, Tribe__Events__Main::TAXONOMY );
						if ( ! is_wp_error( $term ) ) {
							$terms[] = (int) $term['term_id'];

							// Track that we created a Term
							$activity->add( 'cat', 'created', $term['term_id'] );
						}
					} else {
						$terms[] = (int) $term['term_id'];
					}
				}
			}

			// if we are setting all events to a category specified in saved import
			if ( ! empty( $this->meta['category'] ) ) {
				$terms[] = (int) $this->meta['category'];
			}

			wp_set_object_terms( $event['ID'], $terms, Tribe__Events__Main::TAXONOMY, false );

			// If we have a Image Field from Service
			if ( ! empty( $event['image'] ) ) {
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
					continue;
				}

				// Verify for more Complex Errors
				if ( is_wp_error( $image ) ) {
					continue;
				}

				if ( ! empty( $image->post_id ) ) {
					// Set as featured image
					$featured_status = set_post_thumbnail( $event['ID'], $image->post_id );

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
}
