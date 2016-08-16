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
		),
		'meetup' => array(
			'source' => 'meetup_id',
			'target' => 'EventMeetupID',
		),
		'ical' => array(
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
			return false;
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
			$this->meta[ $key ] = is_array( $value ) ? reset( $value ) : $value;
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
		return update_post_meta( $this->post->ID, self::$meta_key_prefix . $key, $value );
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
		);

		$meta = wp_parse_args( $meta, $defaults );

		$post = $this->prep_post_args( $type, $args, $meta );

		$result = wp_insert_post( $post );

		// meta_input was introduced in 4.4. Deal with old versions
		if ( -1 === version_compare( WP_VERSION, '4.4' ) && ! is_wp_error( $result ) ) {
			foreach ( $post['meta_input'] as $key => $value ) {
				update_post_meta( $result, $key, $value );
			}
		}

		// After Creating the Post Load and return
		return $this->load( $result );
	}

	/**
	 * Edits an import record
	 *
	 * @param array $args Post type args
	 * @param array $meta Post meta
	 *
	 * @return WP_Post|WP_Error
	 */
	public function save( $post_id, $args = array(), $meta = array() ) {
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

		$result = wp_update_post( $post );

		// meta_input was introduced in 4.4. Deal with old versions
		if ( -1 === version_compare( WP_VERSION, '4.4' ) && ! is_wp_error( $result ) ) {
			foreach ( $post['meta_input'] as $key => $value ) {
				update_post_meta( $result, $key, $value );
			}
		}

		// After Creating the Post Load and return
		return $this->load( $result );
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
			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		$args = (object) $args;
		$meta = (object) $meta;

		if ( 'schedule' === $type ) {
			$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $meta->frequency ) );
			if ( ! $frequency ) {
				return tribe_error( 'core:aggregator:invalid-record-frequency', $meta );
			}

			// Setups the post_content as the Frequency (makes it easy to fetch by frequency)
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
			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

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

		return Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $schedule_id );
	}

	/**
	 * Creates a child record based on the import record
	 *
	 * @return boolean|WP_Error
	 */
	public function create_child_record() {
		$post = array(
			// Stores the Key under `post_title` which is a very forgiving type of column on `wp_post`
			'post_title'     => $this->generate_title( $this->type, $this->origin, $this->meta['frequency'], $this->post ),
			'post_type'      => $this->post->post_type,
			'ping_status'    => $this->post->ping_status,
			'post_mime_type' => $this->post->post_mime_type,
			'post_date'      => current_time( 'mysql' ),
			'post_status'    => Tribe__Events__Aggregator__Records::$status->draft,
			'post_parent'    => $this->id,
			'meta_input'     => array(),
		);

		foreach ( $this->meta as $key => $value ) {
			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( array( 'id' => $this->meta['frequency'] ) );
		if ( ! $frequency ) {
			return tribe_error( 'core:aggregator:invalid-record-frequency', $meta );
		}

		// Setups the post_content as the Frequency (makes it easy to fetch by frequency)
		$post['post_content'] = $frequency->id;

		// create schedule post
		$child_id = wp_insert_post( $post );

		// if the schedule creation failed, bail
		if ( is_wp_error( $child_id ) ) {
			return tribe_error( 'core:aggregator:save-child-failed' );
		}

		return Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $child_id );
	}

	/**
	 * Queues the import on the Aggregator service
	 *
	 * @return mixed
	 */
	public function queue_import( $args = array() ) {
		$aggregator = Tribe__Events__Aggregator::instance();

		$is_previewing = (
			! empty( $_GET['action'] )
			&& (
				'tribe_aggregator_create_import' === $_GET['action']
				|| 'tribe_aggregator_preview_import' === $_GET['action']
			)
		);

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
			return $this->set_status_as_failed( tribe_error( 'core:aggregator:invalid-service-response' ) );
		}

		// if the Import creation was unsuccessful, set this record as failed
		if (
			'success:create-import' != $response->message_code
			&& 'queued' != $response->message_code
		) {
			/**
			 * @todo Allow overwritting the message
			 */
			$error = new WP_Error( $response->message_code, esc_html__( $response->message, 'the-events-calendar' ) );
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

		// reduce the daily allotment of import creations
		$aggregator->reduce_daily_limit( 1 );

		return $response;
	}

	public function get_import_data() {
		$aggregator = Tribe__Events__Aggregator::instance();
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

		if ( ! isset( $statuses->{ $status } ) || 'trash' !== $status ) {
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

		$this->log_error( tribe_error( 'core:aggregator:daily-limit-reached', array(), array( $aggregator->get_daily_limit() ) ) );

		return $error;
	}

	/**
	 * Verifies if this
	 *  Schedule Record can create a new Child Record
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

		$current  = time();
		$modified = strtotime( $this->post->post_modified );
		$next     = $modified + $this->frequency->interval;

		if ( $current < $next ) {
			return false;
		}

		return true;
	}

	/**
	 * Adjust the event count for the record
	 *
	 * Note: event count is stored in the comment count field
	 *
	 * @param int $quantity Amount to adjust the event count by
	 * @param int $post_id Post ID to fetch from
	 */
	public function adjust_event_count( $quantity, $post = null ) {
		if ( ! $post ) {
			$post = get_post( $this->post->ID );
		}

		$event_count = $post->comment_count;

		$event_count += (int) $quantity;
		if ( $event_count < 0 ) {
			$event_count = 0;
		}

		$this->temp_event_count = $event_count;
		add_filter( 'pre_wp_update_comment_count_now', array( $this, 'event_count_filter' ) );
		wp_update_comment_count_now( $post->ID );
		remove_filter( 'pre_wp_update_comment_count_now', array( $this, 'event_count_filter' ) );
	}

	/**
	 * Completes the import process for a record
	 *
	 * This occurs after the successful insertion of data
	 *
	 * @param array $results Array of results (created, updated, and skipped) from an insert
	 */
	public function complete_import( $results ) {
		$args = array(
			'ID' => $this->post->ID,
			'post_modified' => date( Tribe__Date_Utils::DBDATETIMEFORMAT, current_time( 'timestamp' ) ),
			'post_status' => Tribe__Events__Aggregator__Records::$status->success,
		);

		wp_update_post( $args );

		// update the comment counts
		if ( ! empty( $results['created'] ) ) {
			// make sure we have the latest data for the post
			$post = get_post( $this->post->ID );

			$this->adjust_event_count( (int) $results['created'], $post );

			if ( ! empty( $post->post_parent ) ) {
				$this->adjust_event_count( (int) $results['created'], get_post( $post->post_parent ) );
			}
		}
	}

	/**
	 * Filters the comment count before updating the comment count on an import record
	 *
	 * @return int
	 */
	public function event_count_filter() {
		$event_count = $this->temp_event_count;
		$this->temp_event_count = 0;
		return $event_count;
	}

	/**
	 * Inserts events, venues, and organizers for the Import Record
	 *
	 * @param array $data Dummy data var to allow children to optionally react to passed in data
	 *
	 * @return array|WP_Error
	 */
	public function insert_posts( $data = array() ) {
		add_filter( 'tribe-post-origin', array( Tribe__Events__Aggregator__Records::instance(), 'filter_post_origin' ), 10 );

		$import_data = $this->get_import_data();

		if ( empty( $this->meta['finalized'] ) ) {
			return tribe_error( 'core:aggregator:record-not-finalized' );
		}

		if ( is_wp_error( $import_data ) ) {
			return $import_data;
		}

		$results = array(
			'updated' => 0,
			'created' => 0,
			'skipped' => 0,
		);

		$args = array(
			'post_status' => $this->meta['post_status'],
		);

		$items = $this->filter_data_by_selected( $import_data->data->events );

		$unique_field = $this->get_unique_field();
		$existing_ids = $this->get_existing_ids_from_import_data( $items );

		$count_scanned_events = 0;

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
			$count_scanned_events++;
			$event = Tribe__Events__Aggregator__Event::translate_service_data( $item );

			// set the event ID if it can be set
			if (
				$unique_field
				&& isset( $event[ $unique_field['target'] ] )
				&& isset( $existing_ids[ $event[ $unique_field['target'] ] ] )
			) {
				$event['ID'] = $existing_ids[ $event[ $unique_field['target'] ] ]->post_id;
			}

			$event['post_status'] = $args['post_status'];

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
				$results['skipped']++;
				continue;
			}

			if ( empty( $event[ 'recurrence' ] ) ) {
				$non_recurring = true;
			}

			// set the parent
			if ( ! empty( $event[ 'ID' ] ) && ( $id = wp_get_post_parent_id( $event[ 'ID' ] ) ) ) {
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
				}
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
				}
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
				$event = apply_filters( 'tribe_aggregator_before_update_event', $event, $record );

				$event['ID'] = tribe_update_event( $event['ID'], $event );
				remove_filter( 'tribe_aggregator_track_modified_fields', '__return_false' );

				// Count it as a updated Event
				$results['updated']++;
			} else {
				/**
				 * Filters the event data before inserting event
				 *
				 * @param array $event Event data to save
				 * @param Tribe__Events__Aggregator__Record__Abstract Importer record
				 */
				$event = apply_filters( 'tribe_aggregator_before_insert_event', $event, $this );
				$event['ID'] = tribe_create_event( $event );

				// Count it as a created Event
				$results['created']++;
			}

			self::add_record_to_event( $event['ID'], $this->id, $this->origin );

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
						$terms[] = (int) $term['term_id'];
					} else {
						$terms[] = (int) $term['term_id'];
					}
				}
			}

			//if we are setting all events to a category specified in saved import
			if ( ! empty( $this->meta['category'] ) ) {
				$terms[] = (int) $this->meta['category'];
			}

			wp_set_object_terms( $event['ID'], $terms, Tribe__Events__Main::TAXONOMY, false );

			if ( ! empty( $event['image'] ) ) {
				$this->import_event_image( $event['ID'], $event );
			}
		}

		$this->complete_import( $results );

		remove_filter( 'tribe-post-origin', array( Tribe__Events__Aggregator__Records::instance(), 'filter_post_origin' ), 10 );

		return $results;
	}

	/**
	 * Adds the import record and origin to the imported event
	 *
	 * @param int $id Event ID
	 * @param int $record_id Import Record ID
	 * @param string $origin Import Origin
	 */
	public static function add_record_to_event( $id, $record_id, $origin ) {
		// Add the Aggregator Origin
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$origin_key, $origin );

		// Add the Aggregator Record
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$record_key, $record_id );
	}

	/**
	 * Attempts to pull in the event image, if there is one, and attach it to the
	 * specified event post.
	 *
	 * @param $event_id
	 * @param $facebook_event
	 */
	protected function import_event_image( $event_id, $import_data ) {
		// Attempt to grab the event image
		/**
		 * Filters the returned event image url
		 *
		 * @param array|bool $image
		 * @param int $event_id Event ID
		 * @param array $import_data Event data
		 */
		$image = apply_filters( 'tribe_aggregator_event_image', Tribe__Events__Aggregator::instance()->api( 'image' )->get( $import_data['image']->id ), $event_id, $import_data );

		// If there was a problem bail out
		if ( false === $image ) {
			return;
		}

		// Set as featured image
		return set_post_thumbnail( $event_id, $image->post_id );
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

		if ( 'all' !== $this->meta['ids_to_import'] ) {
			if ( is_array( $this->meta['ids_to_import'] ) ) {
				$selected_ids = $this->meta['ids_to_import'];
			} else {
				$selected_ids = json_decode( $this->meta['ids_to_import'] );
			}
		} else {
			$selected_ids = wp_list_pluck( $import_data, $unique_field['source'] );
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

		if ( 'all' === $this->meta['ids_to_import'] ) {
			return $import_data;
		}

		$selected_ids = $this->meta['ids_to_import'];

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
}
