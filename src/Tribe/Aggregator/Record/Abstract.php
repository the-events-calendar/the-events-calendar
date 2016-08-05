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
			'source' => '_uid',
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
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $type = 'manual', $args = array(), $meta = array() ) {
		if ( ! in_array( $type, array( 'manual', 'schedule' ) ) ) {
			return new WP_Error( 'invalid-type', __( 'An invalid Type was used to setup this Record', 'the-events-calendar' ), $type );
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
				return new WP_Error( 'invalid-frequency', __( 'An Invalid frequency was used to try to setup a scheduled import', 'the-events-calendar' ), $meta );
			}

			// Setups the post_content as the Frequency (makes it easy to fetch by frequency)
			$post['post_content'] = $frequency->id;
		}

		// // After Creating the Post Load and return
		return $this->load( wp_insert_post( $post ) );
	}

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
			return new WP_Error(
				'invalid-frequency',
				__( 'An Invalid frequency was used to try to setup a scheduled import', 'the-events-calendar' ),
				$meta
			);
		}

		// Setups the post_content as the Frequency (makes it easy to fetch by frequency)
		$post['post_content'] = $frequency->id;

		// create schedule post
		$schedule_id = wp_insert_post( $post );

		// if the schedule creation failed, bail
		if ( is_wp_error( $schedule_id ) ) {
			return new WP_Error(
				'tribe-aggregator-save-schedule-failed',
				__( 'Unable to save schedule. Please try again.', 'the-events-calendar' )
			);
		}

		$update_args = array(
			'ID' => $this->post->ID,
			'post_parent' => $schedule_id,
		);

		// update the parent of the import we are creating the schedule for. If that fails, delete the
		// corresponding schedule and bail
		if ( ! wp_update_post( $update_args ) ) {
			wp_delete_post( $schedule_id, true );

			return new WP_Error(
				'tribe-aggregator-save-schedule-failed',
				__( 'Unable to save schedule. Please try again.', 'the-events-calendar' )
			);
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
			return new WP_Error(
				'invalid-frequency',
				__( 'An Invalid frequency was used to try to setup a scheduled import', 'the-events-calendar' ),
				$meta
			);
		}

		// Setups the post_content as the Frequency (makes it easy to fetch by frequency)
		$post['post_content'] = $frequency->id;

		// create schedule post
		$child_id = wp_insert_post( $post );

		// if the schedule creation failed, bail
		if ( is_wp_error( $child_id ) ) {
			return new WP_Error(
				'tribe-aggregator-save-child-failed',
				__( 'Unable to save schedule. Please try again.', 'the-events-calendar' )
			);
		}

		return Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $child_id );
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
			'callback' => site_url( '/event-aggregator/insert/?key=' . urlencode( $this->meta['hash'] ) ),
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
		$defaults = array(

		);
		$args = (object) wp_parse_args( $args, $defaults );

		// Force the parent
		$args->post_parent = $this->id;

		return Tribe__Events__Aggregator__Records::instance()->query( $args );
	}

	public function get_child_record_by_status( $status = 'success', $qty = -1 ) {
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
	 * @return array|WP_Error
	 */
	public function insert_posts() {
		$import_data = $this->get_import_data();

		if ( empty( $this->meta['finalized'] ) ) {
			return new WP_Error(
				'tribe-aggregator-record-not-finalized',
				__( 'Posts cannot be inserted from an unfinalized import record', 'the-events-calendar' )
			);
		}

		$results = array(
			'updated' => 0,
			'created' => 0,
			'skipped' => 0,
		);

		$args = array(
			'post_status' => $this->meta['post_status'],
			'recurring_overwrite' => false,
		);

		// @todo set args[recurring_overwrite] using the change authority setting

		$overwrite = $args['recurring_overwrite'];

		$unique_field = $this->get_unique_field();
		$existing_ids = $this->get_existing_ids_from_import_data( $import_data->data->events );

		$count_scanned_events = 0;

		//cache
		$possible_parents = array();
		$found_organizers = array();
		$found_venues     = array();

		//if we have no non recurring events the message may be different
		$non_recurring = false;

		$show_map_setting = Tribe__Events__Aggregator__Settings::instance()->default_map( $this->meta['origin'] );
		$update_authority_setting = Tribe__Events__Aggregator__Settings::instance()->default_update_authority( $this->meta['origin'] );

		foreach ( $import_data->data->events as $item ) {
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

			//set the parent
			if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
				if ( ! empty( $event[ 'ID' ] ) && ( $id = wp_get_post_parent_id( $event[ 'ID' ] ) ) ) {
					$event['post_parent'] = $id;
				} elseif ( ! empty( $event['parent_uid'] ) && ( $k = array_search( $event['parent_uid'], $possible_parents ) ) ) {
					$event['post_parent'] = $k;
				}
				//PRO version will already be set to parent of an existing series during check for duplicate
			} elseif ( ! empty( $event['parent_uid'] ) ) {
				if ( $k = array_search( $event['parent_uid'], $possible_parents ) ) {
					$event['post_parent'] = $k;
				}
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

			if ( ! empty( $event['ID'] ) ) {
				if ( 'preserve_changes' === $update_authority_setting ) {
					$event = Tribe__Events__Aggregator__Event::preserve_changed_fields( $event );
				}

				add_filter( 'tribe_aggregator_track_modified_fields', '__return_false' );
				$event['ID'] = tribe_update_event( $event['ID'], $event );
				remove_filter( 'tribe_aggregator_track_modified_fields', '__return_false' );
				$results['updated']++;
			} else {
				$event['ID'] = tribe_create_event( $event );
				$results['created']++;
			}

			//add post parent possibility
			if ( empty( $event['parent_uid'] ) ) {
				$possible_parents[ $event['ID'] ] = $event[ $unique_field['target'] ];
			}

			if ( ! empty( $event[ $unique_field['target'] ] ) ) {
				update_post_meta( $event['ID'], "_{$unique_field['target']}", $event[ $unique_field['target'] ] );
			}

			//Save the meta data in case of updating to pro later on
			if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
				if ( ! empty( $event['recurrence'] ) ) {
					update_post_meta( $event['ID'], '_EventRecurrenceRRULE', $event['recurrence'] );
				}
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
			if ( ! empty( $args['import_category'] ) ) {
				$terms[] = (int) $args['import_category'];
			}

			wp_set_object_terms( $event['ID'], $terms, Tribe__Events__Main::TAXONOMY, false );
		}

		$this->complete_import( $results );

		return $results;
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

		if ( 'all' !== $this->meta['ids_to_import'] ) {
			$selected_ids = json_decode( $this->meta['ids_to_import'] );
		} else {
			$selected_ids = wp_list_pluck( $import_data, $unique_field['source'] );
		}

		$event_object = new Tribe__Events__Aggregator__Event;
		$existing_ids = $event_object->get_existing_ids( $this->meta['origin'], $selected_ids );

		return $existing_ids;
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
