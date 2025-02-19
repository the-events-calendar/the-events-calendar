<?php
/**
 * Class Tribe__Events__Aggregator__Record__Abstract
 *
 * Abstract for EA records.
 */

use Tribe\Events\Aggregator\Record\Batch_Queue;

// Don't load directly.
defined( 'WPINC' ) || die;

use Tribe__Date_Utils as Dates;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Languages__Locations as Locations;
/**
 * Class Tribe__Events__Aggregator__Record__Abstract
 *
 * Abstract for EA records.
 */
abstract class Tribe__Events__Aggregator__Record__Abstract { //phpcs:ignore TEC.Classes.ValidClassName.NotSnakeCase, PEAR.NamingConventions.ValidClassName.Invalid, Generic.Classes.OpeningBraceSameLine.ContentAfterBrace

	/**
	 * Meta key prefix for ea-record data
	 *
	 * @var string
	 */
	public static $meta_key_prefix = '_tribe_aggregator_';

	/**
	 * Holds the post ID of the record.
	 *
	 * @var string|int
	 */
	public $id;

	/**
	 * Holds the current post object.
	 *
	 * @var WP_Post
	 */
	public $post;

	/**
	 * Holds the post metadata for the current post.
	 *
	 * @var array
	 */
	public $meta;

	/**
	 * Holds the current record ping status.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Holds the cron frequency(ies).
	 *
	 * @var array|stdClass
	 */
	public $frequency;

	/**
	 * Is this a scheduled import?
	 *
	 * @var bool
	 */
	public $is_schedule = false;

	/**
	 * Is this a manual import?
	 *
	 * @var bool
	 */
	public $is_manual = false;

	/**
	 * The error encountered during the last query.
	 *
	 * @var string
	 */
	public $last_wpdb_error = '';

	/**
	 * @var Tribe__Image__Uploader
	 */
	public $image_uploader;

	/**
	 * An associative array of origins and the settings they define a policy for.
	 *
	 * @var array
	 */
	protected $origin_import_policies = [
		'url' => [ 'show_map_link' ],
	];

	/**
	 * @var array
	 */
	public static $unique_id_fields = [
		'meetup'     => [
			'source' => 'meetup_id',
			'target' => 'EventMeetupID',
		],
		'eventbrite' => [
			'source' => 'eventbrite_id',
			'target' => 'EventBriteID',
		],
		'ical'       => [
			'source' => 'uid',
			'target' => 'uid',
		],
		'gcal'       => [
			'source' => 'uid',
			'target' => 'uid',
		],
		'ics'        => [
			'source' => 'uid',
			'target' => 'uid',
		],
		'url'        => [
			'source' => 'id',
			'target' => 'EventOriginalID',
		],
	];

	/**
	 * @var array
	 */
	public static $unique_venue_id_fields = [
		'meetup'     => [
			'source' => 'meetup_id',
			'target' => 'VenueMeetupID',
		],
		'eventbrite' => [
			'source' => 'eventbrite_id',
			'target' => 'VenueEventBriteID',
		],
	];

	/**
	 * @var array
	 */
	public static $unique_organizer_id_fields = [
		'meetup'     => [
			'source' => 'meetup_id',
			'target' => 'OrganizerMeetupID',
		],
		'eventbrite' => [
			'source' => 'eventbrite_id',
			'target' => 'OrganizerEventBriteID',
		],
	];

	/**
	 * Cache variable to store the last child post.
	 *
	 * @var  WP_Post
	 */
	protected $last_child;

	/**
	 * Holds the event count temporarily while event counts (comment_count) is being updated.
	 *
	 * @var int
	 */
	private $temp_event_count = 0;

	/**
	 * The import record origin.
	 *
	 * @var string
	 */
	public $origin;

	/**
	 * Setup all the hooks and filters.
	 *
	 * @param WP_Post|int $post The post object or post ID to load.
	 *
	 * @return void
	 */
	public function __construct( $post = null ) {
		$this->image_uploader = new Tribe__Image__Uploader();
		// If we have an Post we try to Setup.
		$this->load( $post );
	}

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Loads the WP_Post associated with this record.
	 *
	 * @param WP_Post|int $post The post object or post ID to load.
	 */
	public function load( $post = null ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof WP_Post ) {
			return tribe_error( 'core:aggregator:invalid-record-object', [], [ $post ] );
		}

		if ( Records::$post_type !== $post->post_type ) {
			return tribe_error( 'core:aggregator:invalid-record-post_type', [], [ $post ] );
		}

		$this->id = $post->ID;

		// Get WP_Post object.
		$this->post = $post;

		// Map `ping_status` as the `type`.
		$this->type = $this->post->ping_status;

		if ( 'schedule' === $this->type ) {
			// Fetches the Frequency Object.
			$this->frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( [ 'id' => $this->post->post_content ] );

			// Boolean Flag for Scheduled records.
			$this->is_schedule = true;
		} else {
			// Everything that is not a Scheduled Record is set as Manual.
			$this->is_manual = true;
		}

		$this->setup_meta( get_post_meta( $this->id ) );

		return $this;
	}

	/**
	 * Sets up meta fields by de-prefixing them into the array
	 *
	 * @param array $meta Meta array.
	 */
	public function setup_meta( $meta ) {
		foreach ( $meta as $key => $value ) {
			$key                = preg_replace( '/^' . self::$meta_key_prefix . '/', '', $key );
			$this->meta[ $key ] = maybe_unserialize( is_array( $value ) ? reset( $value ) : $value );
		}

		// `source` will be empty when importing .ics files.
		$this->meta['source'] = ! empty( $this->meta['source'] ) ? $this->meta['source'] : '';
		$original_source      = $this->meta['source'];

		// Intelligently prepend "http://" if the protocol is missing from the source URL.
		if ( ! empty( $this->meta['source'] ) && false === strpos( $this->meta['source'], '://' ) ) {
			$this->meta['source'] = 'http://' . $this->meta['source'];
		}

		/**
		 * Provides an opportunity to set or modify the source URL for an import.
		 *
		 * @since 4.5.11
		 *
		 * @param string  $source.
		 * @param string  $original_source.
		 * @param WP_Post $record.
		 * @param array   $meta.
		 */
		$this->meta['source'] = apply_filters(
			'tribe_aggregator_meta_source',
			$this->meta['source'],
			$original_source,
			$this->post,
			$this->meta
		);

		// This prevents lots of isset checks for no reason.
		if ( empty( $this->meta['activity'] ) ) {
			$this->meta['activity'] = new Tribe__Events__Aggregator__Record__Activity();
		}
	}

	/**
	 * Updates import record meta
	 *
	 * @param string $key   Meta key.
	 * @param mixed  $value Meta value.
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
	 * @param string $key Meta key.
	 */
	public function delete_meta( $key ) {
		unset( $this->meta[ $key ] );

		return delete_post_meta( $this->post->ID, self::$meta_key_prefix . $key );
	}

	/**
	 * Returns the Activity object for the record
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	public function activity() {
		if ( empty( $this->meta['activity'] ) ) {
			$activity = new Tribe__Events__Aggregator__Record__Activity();
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
	 * Gets a hash with the information we need to verify if a given record is a duplicate
	 *
	 * @since  4.5.13
	 *
	 * @return string
	 */
	public function get_data_hash() {
		$meta = [
			'file',
			'keywords',
			'location',
			'start',
			'end',
			'radius',
			'source',
			'content_type',
		];

		$data = [
			'type'      => $this->type,
			'origin'    => $this->origin,
			'frequency' => null,
		];

		// If schedule Record, we need it's frequency.
		if ( $this->is_schedule ) {
			$data['frequency'] = $this->frequency->id;
		}

		foreach ( $meta as $meta_key ) {
			if ( ! isset( $this->meta[ $meta_key ] ) ) {
				continue;
			}

			$data[ $meta_key ] = $this->meta[ $meta_key ];
		}

		// Remove the empty Keys.
		$data = array_filter( $data );

		// Sort to avoid any weird MD5 stuff.
		ksort( $data );

		// Create a string to be able to MD5.
		$data_string = maybe_serialize( $data );

		return md5( $data_string );
	}

	/**
	 * Creates an import record.
	 *
	 * @param string $type Type of record to create - manual or schedule.
	 * @param array  $args Post type args.
	 * @param array  $meta Post meta.
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $type = 'manual', $args = [], $meta = [] ) {
		if ( ! in_array( $type, [ 'manual', 'schedule' ] ) ) {
			return tribe_error( 'core:aggregator:invalid-create-record-type', $type );
		}

		$defaults = [
			'parent' => 0,
		];

		$args = (object) wp_parse_args( $args, $defaults );

		$defaults = [
			'frequency'                 => null,
			'hash'                      => wp_generate_password( 32, true, true ),
			'preview'                   => false,
			'allow_multiple_organizers' => true,
		];

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

		// After Creating the Post Load and return.
		return $this->load( $result );
	}

	/**
	 * Edits an import record
	 *
	 * @param int   $post_id Post ID to edit.
	 * @param array $args Post type args.
	 * @param array $meta Post meta.
	 *
	 * @return WP_Post|WP_Error
	 */
	public function save( $post_id, $args = [], $meta = [] ) {
		if ( ! isset( $meta['type'] ) || 'schedule' !== $meta['type'] ) {
			return tribe_error( 'core:aggregator:invalid-edit-record-type', $meta );
		}

		$defaults = [
			'parent' => 0,
		];
		$args     = (object) wp_parse_args( $args, $defaults );

		$defaults = [
			'frequency' => null,
		];
		$meta     = wp_parse_args( $meta, $defaults );

		$post                = $this->prep_post_args( $meta['type'], $args, $meta );
		$post['ID']          = absint( $post_id );
		$post['post_status'] = Records::$status->schedule;

		add_filter( 'wp_insert_post_data', [ $this, 'dont_change_post_modified' ], 10, 2 );
		$result = wp_update_post( $post );
		remove_filter( 'wp_insert_post_data', [ $this, 'dont_change_post_modified' ] );

		if ( ! is_wp_error( $result ) ) {
			$this->maybe_add_meta_via_pre_wp_44_method( $result, $post['meta_input'] );
		}

		// After Creating the Post Load and return.
		return $this->load( $result );
	}

	/**
	 * Filter the post_modified dates to be unchanged
	 * conditionally hooked to wp_insert_post_data and then unhooked after wp_update_post
	 *
	 * @param array $data    New data to be used in the update.
	 * @param array $postarr Existing post data.
	 *
	 * @return array
	 */
	public function dont_change_post_modified( $data, $postarr ) {
		$post                      = get_post( $postarr['ID'] );
		$data['post_modified']     = $postarr['post_modified'];
		$data['post_modified_gmt'] = $postarr['post_modified_gmt'];

		return $data;
	}

	/**
	 * Preps post arguments for create/save.
	 *
	 * @param string $type Type of record to create - manual or schedule.
	 * @param object $args Post type args.
	 * @param array  $meta Post meta.
	 *
	 * @return array
	 */
	public function prep_post_args( $type, $args, $meta = [] ) {
		$post = [
			'post_title'     => $this->generate_title( $type, $this->origin, $meta['frequency'], $args->parent ),
			'post_type'      => Records::$post_type,
			'ping_status'    => $type,
			// The Mime Type needs to be on a %/% format to work on WordPress.
			'post_mime_type' => 'ea/' . $this->origin,
			'post_date'      => current_time( 'mysql' ),
			'post_status'    => Records::$status->draft,
			'post_parent'    => $args->parent,
			'meta_input'     => [],
		];

		// Prefix all keys.
		foreach ( $meta as $key => $value ) {
			// sSkip arrays that are empty.
			if ( is_array( $value ) && empty( $value ) ) {
				continue;
			}

			// Trim scalars.
			if ( is_scalar( $value ) ) {
				$value = trim( $value );
			}

			// If the value is null, let's avoid inserting it.
			if ( null === $value ) {
				continue;
			}

			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		$meta = (object) $meta;

		if ( 'schedule' === $type ) {
			$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( [ 'id' => $meta->frequency ] );
			if ( ! $frequency ) {
				return tribe_error( 'core:aggregator:invalid-record-frequency', $meta );
			}

			// Setup the post_content as the Frequency (makes it easy to fetch by frequency).
			$post['post_content'] = $frequency->id;
		}

		return $post;
	}

	/**
	 * A simple method to create a Title for the Records
	 *
	 * This method accepts any number of params, they must be string compatible
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
		$post = [
			'post_title'     => $this->generate_title( $this->type, $this->origin, $this->meta['frequency'] ),
			'post_type'      => $this->post->post_type,
			'ping_status'    => $this->post->ping_status,
			'post_mime_type' => $this->post->post_mime_type,
			'post_date'      => current_time( 'mysql' ),
			'post_status'    => Records::$status->schedule,
			'post_parent'    => 0,
			'meta_input'     => [],
		];


		foreach ( $this->meta as $key => $value ) {
			// Don't propagate these meta keys to the scheduled record.
			if (
				'preview' === $key
				|| 'activity' === $key
				|| 'ids_to_import' === $key
			) {
				continue;
			}

			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		// associate this child with the schedule.
		$post['meta_input'][ self::$meta_key_prefix . 'recent_child' ] = $this->post->ID;

		$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( [ 'id' => $this->meta['frequency'] ] );
		if ( ! $frequency ) {
			return tribe_error( 'core:aggregator:invalid-record-frequency', $this->meta );
		}

		// Setups the post_content as the Frequency (makes it easy to fetch by frequency).
		$post['post_content'] = $frequency->id;

		$this->watch_for_db_errors();

		// create schedule post.
		$schedule_id = wp_insert_post( $post );

		// if the schedule creation failed, bail.
		if ( is_wp_error( $schedule_id ) ) {
			return tribe_error( 'core:aggregator:save-schedule-failed' );
		}

		$this->maybe_add_meta_via_pre_wp_44_method( $schedule_id, $post['meta_input'] );

		if ( $this->db_errors_happened() ) {
			wp_delete_post( $schedule_id );

			return tribe_error( 'core:aggregator:save-schedule-failed' );
		}

		$update_args = [
			'ID'          => $this->post->ID,
			'post_parent' => $schedule_id,
		];

		// update the parent of the import we are creating the schedule for. If that fails, delete the.
		// corresponding schedule and bail.
		if ( ! wp_update_post( $update_args ) ) {
			wp_delete_post( $schedule_id, true );

			return tribe_error( 'core:aggregator:save-schedule-failed' );
		}

		$this->post->post_parent = $schedule_id;

		return Records::instance()->get_by_post_id( $schedule_id );
	}

	/**
	 * Creates a child record based on the import record
	 *
	 * @return boolean|Tribe_Error|Tribe__Events__Aggregator__Record__Abstract
	 */
	public function create_child_record() {
		$frequency_id = 'on_demand';

		if ( ! empty( $this->meta['frequency'] ) ) {
			$frequency_id = $this->meta['frequency'];
		}

		$post = [
			// Stores the Key under `post_title` which is a very forgiving type of column on `wp_post`.
			'post_title'     => $this->generate_title( $this->type, $this->origin, $frequency_id, $this->post->ID ),
			'post_type'      => $this->post->post_type,
			'ping_status'    => $this->post->ping_status,
			'post_mime_type' => $this->post->post_mime_type,
			'post_date'      => current_time( 'mysql' ),
			'post_status'    => Records::$status->draft,
			'post_parent'    => $this->id,
			'post_author'    => $this->post->post_author,
			'meta_input'     => [],
		];

		foreach ( $this->meta as $key => $value ) {
			if ( 'activity' === $key ) {
				// don't copy the parent activity into the child record.
				continue;
			}
			$post['meta_input'][ self::$meta_key_prefix . $key ] = $value;
		}

		// initialize the queue meta entry and set its status to fetching.
		$post['meta_input'][ self::$meta_key_prefix . Tribe__Events__Aggregator__Record__Queue::$queue_key ] = 'fetch';

		$frequency = Tribe__Events__Aggregator__Cron::instance()->get_frequency( [ 'id' => $frequency_id ] );
		if ( ! $frequency ) {
			return tribe_error( 'core:aggregator:invalid-record-frequency', $post['meta_input'] );
		}

		// Setup the post_content as the Frequency (makes it easy to fetch by frequency).
		$post['post_content'] = $frequency->id;

		$this->watch_for_db_errors();

		// create schedule post.
		$child_id = wp_insert_post( $post );

		// if the schedule creation failed, bail.
		if ( is_wp_error( $child_id ) ) {
			return tribe_error( 'core:aggregator:save-child-failed' );
		}

		$this->maybe_add_meta_via_pre_wp_44_method( $child_id, $post['meta_input'] );

		if ( $this->db_errors_happened() ) {
			wp_delete_post( $child_id );

			return tribe_error( 'core:aggregator:save-child-failed' );
		}

		// track the most recent child that was spawned.
		$this->update_meta( 'recent_child', $child_id );

		return Records::instance()->get_by_post_id( $child_id );
	}

	/**
	 * If using WP < 4.4, we need to add meta to the post via update_post_meta
	 *
	 * @param int   $id   Post id to add data to.
	 * @param array $meta Meta to add to the post.
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
	 * @param array $args Arguments to pass to the API.
	 *
	 * @return stdClass|WP_Error|int A response object, a `WP_Error` instance on failure or a record
	 *                               post ID if the record had to be re-scheduled due to HTTP request
	 *                               limit.
	 */
	public function queue_import( $args = [] ) {
		$aggregator = tribe( 'events-aggregator.main' );

		$is_previewing = (
			! empty( $_GET['action'] )
			&& (
				'tribe_aggregator_create_import' === $_GET['action']
				|| 'tribe_aggregator_preview_import' === $_GET['action']
			)
		);

		$error = null;

		$defaults = [
			'type'                => $this->meta['type'],
			'origin'              => $this->meta['origin'],
			'source'              => $this->meta['source'] ?? '',
			'callback'            => $is_previewing ? null : home_url( '/event-aggregator/insert/?key=' . urlencode( $this->meta['hash'] ) ),
			'resolve_geolocation' => 1,
		];

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

		if ( ! empty( $this->meta['allow_multiple_organizers'] ) ) {
			$defaults['allow_multiple_organizers'] = $this->meta['allow_multiple_organizers'];
		}

		if ( empty( $this->meta['next_batch_hash'] ) ) {
			$next_batch_hash             = $this->generate_next_batch_hash();
			$defaults['next_batch_hash'] = $next_batch_hash;
			$this->update_meta( 'next_batch_hash', $next_batch_hash );
		}

		if ( $is_previewing ) {
			$defaults['preview'] = true;
		}

		$args = wp_parse_args( $args, $defaults );

		if ( ! empty( $args['start'] ) ) {
			$args['start'] = ! is_numeric( $args['start'] )
				? Dates::maybe_format_from_datepicker( $args['start'] )
				: Dates::build_date_object( $args['start'] )->format( Dates::DBDATETIMEFORMAT );
		}

		if ( ! empty( $args['end'] ) ) {
			$args['end'] = ! is_numeric( $args['end'] )
				? Dates::maybe_format_from_datepicker( $args['end'] )
				: Dates::build_date_object( $args['end'] )->format( Dates::DBDATETIMEFORMAT );
		}

		// Set site for origin(s) that need it for new token handling.
		if ( in_array( $args['origin'], [ 'eventbrite', 'facebook-dev' ], true ) ) {
			$args['site'] = site_url();
		}

		/**
		 * Allows customizing whether to resolve geolocation for events by the EA service.
		 *
		 * @since 4.6.25
		 *
		 * @param boolean $resolve_geolocation Whether the EA Geocode Address API is enabled for geocoding addresses.
		 * @param array   $args                Queued record import arguments to be sent to EA service.
		 */
		$resolve_geolocation = apply_filters( 'tribe_aggregator_resolve_geolocation', true, $args );

		if ( false === $resolve_geolocation ) {
			$args['resolve_geolocation'] = 0;
		}

		// create the import on the Event Aggregator service.
		$response = $aggregator->api( 'import' )->create( $args );

		// if the Aggregator API returns a WP_Error, set this record as failed.
		if ( is_wp_error( $response ) ) {
			// if the error is just a reschedule set this record as pending.
			/** @var WP_Error $response */
			if ( 'core:aggregator:http_request-limit' === $response->get_error_code() ) {
				$this->should_queue_import( true );

				return $this->set_status_as_pending();
			}

			$error = $response;

			tribe( 'logger' )->log_debug( 'Error during the queue of the record.', 'EA Queue Import' );

			return $this->set_status_as_failed( $error );
		}

		// if the Aggregator response has an unexpected format, set this record as failed.
		if ( empty( $response->message_code ) ) {
			tribe( 'logger' )->log_debug( 'Response code is empty.', 'EA Abstract' );

			return $this->set_status_as_failed( tribe_error( 'core:aggregator:invalid-service-response' ) );
		}

		// if the Import creation was unsuccessful, set this record as failed.
		if (
			'success:create-import' != $response->message_code
			&& 'queued' != $response->message_code
		) {
			$data = ! empty( $response->data ) ? $response->data : [];

			$error = new WP_Error(
				$response->message_code,
				Tribe__Events__Aggregator__Errors::build(
					esc_html( $response->message ),
					$data
				),
				$data
			);


			tribe( 'logger' )->log_debug( 'Error when the creation of the import is taking place.', 'EA Queue Import' );

			return $this->set_status_as_failed( $error );
		}

		// if the Import creation didn't provide an import id, the response was invalid so mark as failed.
		if ( empty( $response->data->import_id ) ) {
			tribe( 'logger' )->log_debug( 'Response import ID was not provided.', 'EA Abstract' );

			return $this->set_status_as_failed( tribe_error( 'core:aggregator:invalid-service-response' ) );
		}

		// only set as pending if we aren't previewing the record.
		if ( ! $is_previewing ) {
			// if we get here, we're good! Set the status to pending.
			$this->set_status_as_pending();
		}

		$service_supports_batch_push = ! empty( $response->batch_push );

		/**
		 * Whether batch pushing is supported for this record or not.
		 *
		 * @since 4.6.15
		 *
		 * @param bool                                        $service_supports_batch_push Whether the Service supports batch pushing or not.
		 * @param Tribe__Events__Aggregator__Record__Abstract $this.
		 */
		$allow_batch_push = apply_filters( 'tribe_aggregator_allow_batch_push', $service_supports_batch_push, $this );
		if ( $allow_batch_push ) {
			$this->update_meta( 'allow_batch_push', true );
		}

		// Store the import id.
		$this->update_meta( 'import_id', $response->data->import_id );
		$this->should_queue_import( false );

		return $response;
	}

	/**
	 * Returns the record import data either fetching it locally or trying to retrieve
	 * it from EA Service.
	 *
	 * @return stdClass|WP_Error An object containing the response data or a `WP_Error` on failure.
	 */
	public function get_import_data() {
		/** @var Tribe__Events__Aggregator $aggregator */
		$aggregator = tribe( 'events-aggregator.main' );

		$data = [];

		// For now only apply this to the URL type.
		if ( 'url' === $this->type ) {
			$data = [
				'start' => $this->meta['start'],
				'end'   => $this->meta['end'],
			];
		}

		/** @var Tribe__Events__Aggregator__API__Import $import_api */
		$import_api = $aggregator->api( 'import' );

		if ( empty( $this->meta['import_id'] ) ) {
			return tribe_error( 'core:aggregator:record-not-finalized' );
		}

		/**
		 * Allow filtering of the Import data Request Args
		 *
		 * @since 4.6.18
		 *
		 * @param array                                       $data   Which Arguments.
		 * @param Tribe__Events__Aggregator__Record__Abstract $record Record we are dealing with.
		 */
		$data = apply_filters( 'tribe_aggregator_get_import_data_args', $data, $this );

		$import_data = $import_api->get( $this->meta['import_id'], $data );

		$import_data = $this->maybe_cast_to_error( $import_data );

		return $import_data;
	}

	/**
	 * Delete record
	 *
	 * @param bool $force Whether to force the deletion or not.
	 *
	 * @return WP_Post|false|null — Post data on success, false or null on failure.
	 */
	public function delete( $force = false ) {
		if ( $this->is_manual ) {
			return tribe_error( 'core:aggregator:delete-record-failed', [ 'record' => $this ], [ $this->id ] );
		}

		return wp_delete_post( $this->id, $force );
	}

	/**
	 * Sets a status on the record
	 *
	 * @param string $status Status to set.
	 *
	 * @return int
	 */
	public function set_status( $status ) {
		if ( ! isset( Records::$status->{$status} ) ) {
			return false;
		}

		// Status of Scheduled Imports cannot change.
		if ( $this->post instanceof WP_Post && Records::$status->schedule === $this->post->post_status ) {
			return false;
		}

		$updated_id = wp_update_post(
			[
				'ID'          => $this->id,
				'post_status' => Records::$status->{$status},
			]
		);

		if ( $updated_id !== $this->id || ! is_wp_error( $updated_id ) ) {
			// Reload the properties of the post if the status of the record was changed.
			$this->load( $this->id );

			// If a parent exists and an error occur register the last update time on the parent record.
			if ( ! empty( $this->post->post_parent ) ) {
				$status = wp_update_post(
					[
						'ID'            => $this->post->post_parent,
						'post_modified' => Dates::build_date_object()->format( Dates::DBDATETIMEFORMAT ),
					]
				);
			}
		}

		return $status;
	}

	/**
	 * Marks a record as failed.
	 *
	 * @param ?WP_Error $error Error message to log.
	 *
	 * @return ?WP_Error
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
	 * @param array $args WP_Query Arguments.
	 *
	 * @return WP_Query|WP_Error
	 */
	public function query_child_records( $args = [] ) {
		$defaults = [];
		$args     = (object) wp_parse_args( $args, $defaults );

		// Force the parent.
		$args->post_parent = $this->id;

		return Records::instance()->query( $args );
	}

	/**
	 * A quick method to fetch the Child Records by Status
	 *
	 * @param string $status Which status, must be a valid EA status.
	 * @param int    $qty    How many records to fetch.
	 * @param array  $args   WP_Query Arguments.
	 *
	 * @return WP_Query|WP_Error|bool
	 */
	public function get_child_record_by_status( $status = 'success', $qty = -1, array $args = [] ) {
		$statuses = Records::$status;

		if ( ! isset( $statuses->{$status} ) && 'trash' !== $status ) {
			return false;
		}

		$args = array_merge(
			$args,
			[
				'post_status'    => $statuses->{$status},
				'posts_per_page' => $qty,
			]
		);

		$query = $this->query_child_records( $args );

		if ( ! $query->have_posts() ) {
			return false;
		}

		// Return the First Post when it exists.
		return $query;
	}

	/**
	 * Gets errors on the record post.
	 *
	 * @param array $args WP_Comment_Query arguments.
	 *
	 * @return @return WP_Comment[]|int[]|int List of comments or number of found comments if $count argument is true.
	 */
	public function get_errors( $args = [] ) {
		$defaults = [
			'post_id' => $this->id,
			'type'    => Tribe__Events__Aggregator__Errors::$comment_type,
		];

		$args = wp_parse_args( $args, $defaults );

		return get_comments( $args );
	}

	/**
	 * Logs an error to the comments of the Record post
	 *
	 * @param WP_Error $error Error message to log.
	 *
	 * @return bool
	 */
	public function log_error( WP_Error $error ) {
		/**
		 * Allow switching the logging of errors from EA off.
		 *
		 * Please don't turn this particular filter off without knowing what you are doing, it might cause problems and
		 * will cause Support to likely be trying to help you without the information they might need.
		 *
		 * @since 5.12.1
		 *
		 * @param bool     $should_log_errors If we should log the errors or not.
		 * @param WP_Error $error             Which error we are logging.
		 */
		$should_log_errors = tribe_is_truthy( apply_filters( 'tec_aggregator_records_should_log_error', true, $error ) );
		if ( ! $should_log_errors ) {
			return false;
		}

		$today = getdate();
		$args  = [
			'number'     => 1,
			'date_query' => [
				[
					'year'  => $today['year'],
					'month' => $today['mon'],
					'day'   => $today['mday'],
				],
			],
		];

		// Tries To Fetch Comments for today.
		$todays_errors = $this->get_errors( $args );

		if ( ! empty( $todays_errors ) ) {
			return false;
		}

		$args = [
			'comment_post_ID' => $this->id,
			'comment_author'  => $error->get_error_code(),
			'comment_content' => $error->get_error_message(),
			'comment_type'    => Tribe__Events__Aggregator__Errors::$comment_type,
		];

		return wp_insert_comment( $args );
	}

	/**
	 * Verifies if this Schedule Record can create a new Child Record
	 *
	 * @return boolean
	 */
	public function is_schedule_time() {
		if ( tribe_is_truthy( getenv( 'TRIBE_DEBUG_OVERRIDE_SCHEDULE' ) ) ) {
			return true;
		}

		// If we are not on a Schedule Type.
		if ( ! $this->is_schedule ) {
			return false;
		}

		// If we are not dealing with the Record Schedule.
		if ( Records::$status->schedule !== $this->post->post_status ) {
			return false;
		}

		// In some cases the scheduled import may be inactive and should not run during cron.
		if ( false === $this->frequency ) {
			return false;
		}

		// It's never time for On Demand schedule, bail!.
		if ( ! isset( $this->frequency->id ) || 'on_demand' === $this->frequency->id ) {
			return false;
		}

		$retry_interval         = $this->get_retry_interval();
		$failure_time_threshold = time() - $retry_interval;

		// If the last import status is an error and it happened before half the frequency ago let's try again.
		if (
			(
				$this->has_own_last_import_status()
				&& $this->failed_before( $failure_time_threshold )
			)
			|| $this->last_child()->failed_before( $failure_time_threshold )
		) {
			return true;
		}

		$current = time();
		$last    = strtotime( $this->post->post_modified_gmt );
		$next    = $last + $this->frequency->interval;

		// let's add some randomization of -5 to 0 minutes (this makes sure we don't push a schedule beyond when it should fire off).
		$next += ( mt_rand( -5, 0 ) * 60 );

		// Only do anything if we have one of these metas.
		if ( ! empty( $this->meta['schedule_day'] ) || ! empty( $this->meta['schedule_time'] ) ) {
			// Setup to avoid notices.
			$maybe_next = 0;

			// Now depending on the type of frequency we build the.
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

			// If our Next date based on Last run is bigger than the scheduled time it means we bail.
			if ( $maybe_next > $next ) {
				$next = $maybe_next;
			}
		}

		return $current > $next;
	}

	/**
	 * Verifies if this Record can pruned
	 *
	 * @return boolean
	 */
	public function has_passed_retention_time() {
		// Bail if we are trying to prune a Schedule Record.
		if ( Records::$status->schedule === $this->post->post_status ) {
			return false;
		}

		$current = time();
		$created = strtotime( $this->post->post_date_gmt );

		// Prevents Pending that is younger than 1 hour to be pruned.
		if (
			Records::$status->pending === $this->post->post_status
			&& $current < $created + HOUR_IN_SECONDS
		) {
			return false;
		}

		$prune = $created + Records::instance()->get_retention();

		return $current > $prune;
	}

	/**
	 * Get info about the source, via and title
	 *
	 * @return array
	 */
	public function get_source_info() {
		if ( in_array( $this->origin, [ 'ics', 'csv' ] ) ) {
			if ( empty( $this->meta['source_name'] ) ) {
				$file  = get_post( $this->meta['file'] );
				$title = $file instanceof WP_Post ?
					$file->post_title
					: sprintf(
						/* Translators: %d is the ID of the attachment */
						esc_html__( 'Deleted Attachment: %d', 'the-events-calendar' ),
						$this->meta['file']
					);
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
			if ( in_array( $this->origin, [ 'meetup' ] ) ) {
				$via = '<a href="' . esc_url( $this->meta['source'] )
					. '" target="_blank">'
					. esc_html( $via )
					. '<span class="screen-reader-text">'
					. __( ' (opens in a new window)', 'the-events-calendar' )
					. '</span></a>';
			}
		}

		return [
			'title' => $title,
			'via'   => $via,
		];
	}

	/**
	 * Fetches the status message for the last import attempt on (scheduled) records
	 *
	 * @param string $type            Type of message to fetch.
	 * @param bool   $lookup_children Whether the function should try to read the last children post status to return a coherent.
	 *                                last import status or not, default `false`.
	 *
	 * @return bool|string Either the message corresponding to the last import status or `false` if the last import status
	 *                     is empty or not the one required.
	 */
	public function get_last_import_status( $type = 'error', $lookup_children = false ) {
		$status = $this->has_own_last_import_status() ? $this->meta['last_import_status'] : null;

		if ( empty( $status ) && $lookup_children ) {
			$last_child = $this->get_last_child_post();

			if ( $last_child ) {
				$map = [
					'tribe-ea-failed'  => 'error:import-failed',
					'tribe-ea-success' => 'success:queued',
				];

				$status = Tribe__Utils__Array::get( $map, $last_child->post_status, null );
			}
		}

		if ( ! $status ) {
			return false;
		}

		if ( 0 !== strpos( $status, $type ) ) {
			return false;
		}

		if ( 'error:usage-limit-exceeded' === $status ) {
			return __( 'When this import was last scheduled to run, the daily limit for your Event Aggregator license had already been reached.', 'the-events-calendar' );
		}

		return tribe( 'events-aggregator.service' )->get_service_message( $status );
	}

	/**
	 * Updates the source name on the import record and its parent (if the parent exists)
	 *
	 * @param string $source_name Source name to set on the import record.
	 */
	public function update_source_name( $source_name ) {
		// if we haven't received a source name, bail.
		if ( empty( $source_name ) ) {
			return;
		}

		$this->update_meta( 'source_name', $source_name );

		if ( empty( $this->post->post_parent ) ) {
			return;
		}

		$parent_record = Records::instance()->get_by_post_id( $this->post->post_parent );

		if ( tribe_is_error( $parent_record ) ) {
			return;
		}

		$parent_record->update_meta( 'source_name', $source_name );
	}

	/**
	 * Queues events, venues, and organizers for insertion
	 *
	 * @param array $data              Import data.
	 * @param bool  $start_immediately Whether the data processing should start immediately or not.
	 *
	 * @return array|Tribe__Events__Aggregator__Record__Queue_Interface|WP_Error|Tribe__Events__Aggregator__Record__Activity
	 */
	public function process_posts( $data = [], $start_immediately = false ) {
		if ( ! $start_immediately && 'manual' === $this->type ) {
			/** @var Tribe__Events__Aggregator__Service $service */
			$service = tribe( 'events-aggregator.service' );
			$service->confirm_import( $this->meta );
		}

		// CSV should be processed right away as does not have support for batch pushing.
		$is_not_csv = empty( $data ) || empty( $data['origin'] ) || 'csv' !== $data['origin'];
		// if this is a batch push record then set its queue to fetching.
		// to feed the UI something coherent.
		if ( $is_not_csv && ! $this->is_polling() ) {
			// @todo let's revisit this to return when more UI is exposed.
			$queue = new Batch_Queue( $this );

			if ( $start_immediately ) {
				$queue->process();

				return $queue->activity();
			}

			return $queue;
		}

		$items = $this->prep_import_data( $data );

		if ( is_wp_error( $items ) ) {
			tribe( 'logger' )->log_debug( 'Error while preparing the items of the request.', 'EA Process Posts.' );

			$this->set_status_as_failed( $items );

			return $items;
		}

		$queue = Tribe__Events__Aggregator__Record__Queue_Processor::build_queue( $this, $items );

		if ( $start_immediately && is_array( $items ) ) {
			$queue->process();
		}

		return $queue->activity();
	}

	/**
	 * Returns whether or not the record has a queue
	 *
	 * @return bool
	 */
	public function has_queue() {
		return ! empty( $this->meta[ Tribe__Events__Aggregator__Record__Queue::$queue_key ] );
	}

	/**
	 * Returns count of events in the queue.
	 *
	 * @param string $type Type of event to count. "total" will count all events.
	 *
	 * @return int
	 */
	public function get_event_count( $type = null ) {
		if ( $type === null ) {
			return 0;
		}

		if ( empty( $this->meta['activity'] ) || ! $this->meta['activity'] instanceof Tribe__Events__Aggregator__Record__Activity ) {
			return 0;
		}

		$activity_type = 'event';

		if ( ! empty( $this->meta['content_type'] ) ) {
			$activity_type = $this->meta['content_type'];
		}

		switch ( $type ) {
			case 'total':
				return $this->meta['activity']->count( $activity_type, 'created' ) + $this->meta['activity']->count( $activity_type, 'updated' );

			default:
				return $this->meta['activity']->count( $activity_type, $type );
		}
	}

	/**
	 * Handles import data before queuing
	 *
	 * Ensures the import record source name is accurate, checks for errors, and limits import items
	 * based on selection
	 *
	 * @param array $data Import data.
	 *
	 * @return array|WP_Error
	 */
	public function prep_import_data( $data = [] ) {
		if ( empty( $data ) ) {
			$data = $this->get_import_data();
		}

		if ( is_wp_error( $data ) ) {
			tribe( 'logger' )->log_debug( 'Data of the import has errors.', 'EA Prepare Import' );

			$this->set_status_as_failed( $data );

			return $data;
		}

		$this->update_source_name( empty( $data->data->source_name ) ? null : $data->data->source_name );

		if ( empty( $this->meta['finalized'] ) ) {
			return tribe_error( 'core:aggregator:record-not-finalized' );
		}

		if ( ! isset( $data->data->events ) ) {
			return 'fetch';
		}

		return $this->filter_data_by_selected( $data->data->events );
	}

	/**
	 * Inserts events, venues, and organizers for the Import Record
	 *
	 * @param array $items Dummy data var to allow children to optionally react to passed in data.
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity The import activity record.
	 */
	public function insert_posts( $items = [] ) {
		add_filter( 'tribe-post-origin', [ Records::instance(), 'filter_post_origin' ], 10 );

		/**
		 * Fires before events and linked posts are inserted in the database.
		 *
		 * @since 4.5.13
		 *
		 * @param array $items An array of items to insert.
		 * @param array $meta  The record meta information.
		 */
		do_action( 'tribe_aggregator_before_insert_posts', $items, $this->meta );

		// sets the default user ID to that of the first user that can edit events.
		$default_user_id = $this->get_default_user_id();

		// Creates an Activity to log what Happened.
		$activity                = new Tribe__Events__Aggregator__Record__Activity();
		$initial_created_events  = $activity->count( Tribe__Events__Main::POSTTYPE );
		$expected_created_events = $initial_created_events + count( $items );

		$unique_field = $this->get_unique_field();
		$existing_ids = $this->get_existing_ids_from_import_data( $items );

		// cache.
		$possible_parents = [];
		$found_organizers = [];
		$found_venues     = [];

		$origin                   = $this->meta['origin'];
		$show_map_setting         = tribe_is_truthy( tribe( 'events-aggregator.settings' )->default_map( $origin ) );
		$update_authority_setting = tribe( 'events-aggregator.settings' )->default_update_authority( $origin );

		$import_settings        = tribe( 'events-aggregator.settings' )->default_settings_import( $origin );
		$should_import_settings = tribe_is_truthy( $import_settings ) ? true : false;

		$args = [
			'post_status' => tribe( 'events-aggregator.settings' )->default_post_status( $origin ),
		];

		if ( ! empty( $this->meta['post_status'] ) && 'do_not_override' !== $this->meta['post_status'] ) {
			$args['post_status'] = $this->meta['post_status'];
		}

		/**
		 * When an event/venue/organizer is being updated/inserted in the context of an import then any change
		 * should not be tracked as if made by the user. So doing would result results in posts
		 * "locked", under the "Import events but preserve local changes to event fields" event
		 * authority, after an update/insertion.
		 */
		add_filter( 'tribe_tracker_enabled', '__return_false' );

		foreach ( $items as $item ) {
			$event = Tribe__Events__Aggregator__Event::translate_service_data( $item );

			// Configure the Post Type (enforcing).
			$event['post_type'] = Tribe__Events__Main::POSTTYPE;

			// Set the event ID if it can be set.
			if (
				$this->origin !== 'url'
				&& $unique_field
				&& isset( $event[ $unique_field['target'] ] )
				&& isset( $existing_ids[ $event[ $unique_field['target'] ] ] )
			) {
				$event_post_id = $existing_ids[ $event[ $unique_field['target'] ] ]->post_id;
				if ( tribe_is_event( $event_post_id ) ) {
					$event['ID'] = $event_post_id;
				}
			}

			// Checks if we need to search for Global ID.
			if ( ! empty( $item->global_id ) ) {
				$global_event = Tribe__Events__Aggregator__Event::get_post_by_meta( 'global_id', $item->global_id );

				// If we found something we will only update that Post.
				if ( $global_event ) {
					$event['ID'] = $global_event->ID;
				}
			}

			// Only set the post status if there isn't an ID.
			if ( empty( $event['ID'] ) ) {

				$event['post_status'] = Tribe__Utils__Array::get( $args, 'post_status', $this->meta['post_status'] );

				/**
				 * Allows services to provide their own filtering of event post statuses before import, especially
				 * to handle the (do not override) status.
				 *
				 * @since 4.8.2
				 *
				 * @param string                                      $post_status The event's post status before being filtered.
				 * @param array                                       $event       The WP event data about to imported and saved to the DB.
				 * @param Tribe__Events__Aggregator__Record__Abstract $record      The import's EA Import Record.
				 */
				$event['post_status'] = apply_filters( 'tribe_aggregator_new_event_post_status_before_import', $event['post_status'], $event, $this );
			}

			/**
			 * Should events that have previously been imported be overwritten?
			 *
			 * By default, this is turned off (since it would reset the post status, description
			 * and any other fields that have subsequently been edited) but it can be enabled
			 * by returning true on this filter.
			 *
			 * @var bool $overwrite
			 * @var int  $event_id
			 */
			if ( ! empty( $event['ID'] ) && 'retain' === $update_authority_setting ) {
				// Log this Event was Skipped.
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
				if ( $event['hide_from_listings'] === true ) {
					$event['EventHideFromUpcoming'] = 'yes';
				}
				unset( $event['hide_from_listings'] );
			}

			if ( $should_import_settings && isset( $event['sticky'] ) ) {
				if ( $event['sticky'] === true ) {
					$event['EventShowInCalendar'] = 'yes';
					$event['menu_order']          = -1;
				}
				unset( $event['sticky'] );
			}

			if ( ! $should_import_settings ) {
				unset( $event['feature_event'] );
			}

			// set the parent.
			if (
				! empty( $event['ID'] )
				&& ( $id = wp_get_post_parent_id( $event['ID'] ) ) // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
			) {
				$event['post_parent'] = $id;
			} elseif (
				! empty( $event['parent_uid'] )
				&& ( $k = array_search( $event['parent_uid'], $possible_parents ) ) // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
			) {
				$event['post_parent'] = $k;
			}

			// Do we have an existing venue for this event that we should preserve?.
			// @todo [BTRIA-588]: Review - should we care about the potential for multiple venue IDs?.
			if (
				! empty( $event['ID'] )
				&& 'preserve_changes' === $update_authority_setting
				&& $existing_venue_id = tribe_get_venue_id( $event['ID'] ) // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
			) {
				$event['EventVenueID'] = $existing_venue_id;
				unset( $event['Venue'] );
			}

			// Use Geocoding for imported venues.
			add_filter( 'tec_events_pro_use_geocode_results', '__return_true' );

			// if we should create a venue or use existing.
			if ( ! empty( $event['Venue']['Venue'] ) ) {
				$event['Venue']['Venue'] = trim( $event['Venue']['Venue'] );

				$is_valid_origin = in_array( $this->origin, [ 'ics', 'csv', 'gcal', 'ical' ], true );
				if ( ! empty( $item->venue->global_id ) || $is_valid_origin ) {
					// Pre-set for ICS based imports.
					$venue = false;
					if ( ! empty( $item->venue->global_id ) ) {
						// Did we find a Post with a matching Global ID in History.
						$venue = Tribe__Events__Aggregator__Event::get_post_by_meta( 'global_id_lineage', $item->venue->global_id );
					}

					// Save the Venue Data for Updating.
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

					// If the data is coming from Meetup, then fix the country.
					// Meetup sends the country as a lowercase two-digit country code.
					if (
						'meetup' == $origin
						&& isset( $item->venue->country )
					) {
						$country = tribe( Locations::class )->get_country_based_on_code( $item->venue->country );

						$event['Venue']['Country'] = $venue_data['Country'] = $country; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
					}

					// If "State" is empty, it will not show up on the venue editing screen.
					if (
						! isset( $event['Venue']['State'] )
						&& isset( $item->venue->stateprovince )
					) {
						$event['Venue']['State'] = $venue_data['State'] = $item->venue->stateprovince; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
					}

					if ( $venue ) {
						$venue_id                   = $event['EventVenueID'] = $venue_data['ID'] = $venue->ID; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
						$found_venues[ $venue->ID ] = $event['Venue']['Venue'];

						// Here we might need to update the Venue depending on the main GlobalID.
						if ( 'retain' === $update_authority_setting ) {
							// When we get here we say that we skipped a Venue.
							$activity->add( 'venue', 'skipped', $venue->ID );
						} else {
							if ( 'preserve_changes' === $update_authority_setting ) {
								$venue_data = Tribe__Events__Aggregator__Event::preserve_changed_fields( $venue_data );
							}

							// Update the Venue.
							Tribe__Events__Venue::instance()->update( $venue->ID, $venue_data );

							// Tell that we updated the Venue to the activity tracker.
							$activity->add( 'venue', 'updated', $venue->ID );
						}
					} else {
						/**
						 * Allows filtering the venue ID while searching for it.
						 *
						 * Use this filter to define custom ways to find a matching Venue provided the EA
						 * record information; returning a non `null` value here will short-circuit the
						 * check Event Aggregator would make.
						 *
						 * @since 4.6.15
						 *
						 * @param int|null $venue_id The matching venue ID if any.
						 * @param array    $venue    The venue data from the record.
						 */
						$venue_id = apply_filters( 'tribe_aggregator_find_matching_venue', null, $event['Venue'] );

						if ( null === $venue_id ) {
							// we search the venues already found in this request for this venue title.
							$venue_id = array_search( $event['Venue']['Venue'], $found_venues );
						}

						if ( ! $venue_id ) {
							$venue_unique_field = $this->get_unique_field( 'venue' );

							/**
							 * Whether Venues should be additionally searched by title when no match could be found
							 * using other methods.
							 *
							 * @since 4.6.5
							 *
							 * @param bool                                        $lookup_venues_by_title.
							 * @param stdClass                                    $item    The event data that is being currently processed, it includes the Venue data.
							 *                                                             if any.
							 * @param Tribe__Events__Aggregator__Record__Abstract $record  The current record that is processing events.
							 */
							$lookup_venues_by_title = apply_filters( 'tribe_aggregator_lookup_venues_by_title', true, $item, $this );

							if ( ! empty( $venue_unique_field ) ) {
								$target = $venue_unique_field['target'];
								$value  = $venue_data[ $target ];
								$venue  = Tribe__Events__Aggregator__Event::get_post_by_meta( "_Venue{$target}", $value );
							}

							if ( empty( $venue_unique_field ) || ( $lookup_venues_by_title && empty( $venue ) ) ) {
								$venue_query = new WP_Query(
									[
										'post_type'      => Tribe__Events__Venue::POSTTYPE,
										'title'          => $event['Venue']['Venue'],
										'post_status'    => 'any',
										'posts_per_page' => 1,
										'no_found_rows'  => true,
										'ignore_sticky_posts' => true,
										'update_post_term_cache' => false,
										'update_post_meta_cache' => false,
										'orderby'        => 'post_date ID',
										'order'          => 'ASC',

									]
								);
								$venue = ! empty( $venue_query->post ) ? $venue_query->post : null;
							}

							if ( $venue ) {
								$venue_id                  = $venue->ID;
								$found_venues[ $venue_id ] = $event['Venue']['Venue'];
							}
						}

						// We didn't find any matching Venue for the provided one.
						if ( ! $venue_id ) {
							$event['Venue']['ShowMap']     = $show_map_setting;
							$event['Venue']['ShowMapLink'] = $show_map_setting;

							$venue_id = $event['EventVenueID'] = Tribe__Events__Venue::instance()->create(  // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
								$event['Venue'],
								Tribe__Utils__Array::get( $event, 'post_status', $args['post_status'] )
							);

							$found_venues[ $event['EventVenueID'] ] = $event['Venue']['Venue'];

							// Log this Venue was created.
							$activity->add( 'venue', 'created', $event['EventVenueID'] );

							// Create the Venue Global ID.
							if ( ! empty( $item->venue->global_id ) ) {
								update_post_meta( $event['EventVenueID'], Tribe__Events__Aggregator__Event::$global_id_key, $item->venue->global_id );
							}

							// Create the Venue Global ID History.
							if ( ! empty( $item->venue->global_id_lineage ) ) {
								foreach ( $item->venue->global_id_lineage as $gid ) {
									add_post_meta( $event['EventVenueID'], Tribe__Events__Aggregator__Event::$global_id_lineage_key, $gid );
								}
							}
						} else {
							$event['EventVenueID'] = $venue_data['ID'] = $venue_id; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found

							// Here we might need to update the Venue depending we found something based on old code.
							if ( 'retain' === $update_authority_setting ) {
								// When we get here we say that we skipped an Venue.
								$activity->add( 'venue', 'skipped', $venue_id );
							} else {
								if ( 'preserve_changes' === $update_authority_setting ) {
									$venue_data = Tribe__Events__Aggregator__Event::preserve_changed_fields( $venue_data );
								}

								// Update the Venue.
								Tribe__Events__Venue::instance()->update( $venue_id, $venue_data );

								// Tell that we updated the Venue to the activity tracker.
								$activity->add( 'venue', 'updated', $venue_id );
							}
						}
					}
				}

				// Remove the Venue to avoid duplicates.
				unset( $event['Venue'] );
			}

			// Do we have an existing organizer(s) for this event that we should preserve?.
			if (
				! empty( $event['ID'] )
				&& 'preserve_changes' === $update_authority_setting
				&& $existing_organizer_ids = tribe_get_organizer_ids( $event['ID'] ) // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
			) {
				$event['Organizer'] = $existing_organizer_ids;
				unset( $event['Organizer'] );
			}

			if ( ! empty( $event['Organizer'] ) ) {
				$event_organizers = [];

				// make sure organizers is an array.
				if ( $item->organizer instanceof stdClass ) {
					$item->organizer = [ $item->organizer ];
				}

				foreach ( $event['Organizer'] as $key => $organizer_data ) {

					// if provided a valid Organizer ID right away use it.
					if ( ! empty( $organizer_data['OrganizerID'] ) ) {
						if ( tribe_is_organizer( $organizer_data['OrganizerID'] ) ) {
							$event_organizers[] = (int) $organizer_data['OrganizerID'];
							continue;
						}
						unset( $organizer_data['OrganizerID'] );
					}

					// if we should create an organizer or use existing.
					if ( ! empty( $organizer_data['Organizer'] ) ) {
						$organizer_data['Organizer'] = trim( $organizer_data['Organizer'] );

						if (
							! empty( $item->organizer[ $key ]->global_id )
							|| in_array( $this->origin, [ 'ics', 'ical', 'csv', 'gcal' ] )
						) {
							// Pre-set for ICS based imports.
							$organizer = false;
							if ( ! empty( $item->organizer[ $key ]->global_id ) ) {
								// Did we find a Post with a matching Global ID in History.
								$organizer = Tribe__Events__Aggregator__Event::get_post_by_meta(
									'global_id_lineage',
									$item->organizer[ $key ]->global_id
								);
							}

							if ( isset( $item->organizer[ $key ]->description ) ) {
								$organizer_data['Description'] = $item->organizer[ $key ]->description;
							}

							if ( isset( $item->organizer[ $key ]->excerpt ) ) {
								$organizer_data['Excerpt'] = $item->organizer[ $key ]->excerpt;
							}

							if ( $organizer ) {
								$organizer_id       = $organizer_data['ID'] = $organizer->ID; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
								$event_organizers[] = $organizer_id;

								// If we have a Image Field for the Organizers from Service.
								if ( ! empty( $item->organizer[ $key ]->image ) ) {
									$this->import_organizer_image( $organizer_id, $item->organizer[ $key ]->image, $activity );
								}

								$found_organizers[ $organizer->ID ] = $organizer_data['Organizer'];

								// Here we might need to update the Organizer depending we found something based on old code.
								if ( 'retain' === $update_authority_setting ) {
									// When we get here we say that we skipped an Organizer.
									$activity->add( 'organizer', 'skipped', $organizer->ID );
								} else {
									if ( 'preserve_changes' === $update_authority_setting ) {
										$organizer_data = Tribe__Events__Aggregator__Event::preserve_changed_fields( $organizer_data );
									}

									// Update the Organizer.
									Tribe__Events__Organizer::instance()->update( $organizer->ID, $organizer_data );

									// Tell that we updated the Organizer to the activity tracker.
									$activity->add( 'organizer', 'updated', $organizer->ID );
								}
							} else {
								/**
								 * Allows filtering the organizer ID while searching for it.
								 *
								 * Use this filter to define custom ways to find a matching Organizer provided the EA
								 * record information; returning a non `null` value here will short-circuit the
								 * check Event Aggregator would make.
								 *
								 * @since 4.6.15
								 *
								 * @param int|null $organizer_id The matching organizer ID if any.
								 * @param array    $organizer    The venue data from the record.
								 */
								$organizer_id = apply_filters( 'tribe_aggregator_find_matching_organizer', null, $organizer_data['Organizer'] );

								if ( null === $organizer_id ) {
									// we search the organizers already found in this request for this organizer title.
									$organizer_id = array_search( $organizer_data['Organizer'], $found_organizers );
								}

								if ( ! $organizer_id ) {
									$organizer_unique_field = $this->get_unique_field( 'organizer' );

									if ( ! empty( $organizer_unique_field ) ) {
										$target    = $organizer_unique_field['target'];
										$value     = $organizer_data[ $target ];
										$organizer = Tribe__Events__Aggregator__Event::get_post_by_meta( "_Organizer{$target}", $value );
									} else {
										$organizer_query = new WP_Query(
											[
												'post_type' => Tribe__Events__Organizer::POSTTYPE,
												'title'   => $organizer_data['Organizer'],
												'post_status' => 'any',
												'posts_per_page' => 1,
												'no_found_rows' => true,
												'ignore_sticky_posts' => true,
												'update_post_term_cache' => false,
												'update_post_meta_cache' => false,
												'orderby' => 'post_date ID',
												'order'   => 'ASC',

											]
										);
										$organizer = ! empty( $organizer_query->post ) ? $organizer_query->post : null;
									}
								}

								if ( ! $organizer_id ) {
									if ( $organizer ) {
										$organizer_id                      = $organizer->ID;
										$found_organizers[ $organizer_id ] = $organizer_data['Organizer'];
									}
								}

								// We didn't find any matching Organizer for the provided one.
								if ( ! $organizer_id ) {
									$organizer_id = $event_organizers[] = Tribe__Events__Organizer::instance()->create( $organizer_data, $event['post_status'] ); // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found

									$found_organizers[ $organizer_id ] = $organizer_data['Organizer'];

									// Log this Organizer was created.
									$activity->add( 'organizer', 'created', $organizer_id );

									// Create the Organizer Global ID.
									if ( ! empty( $item->organizer[ $key ]->global_id ) ) {
										update_post_meta(
											$organizer_id,
											Tribe__Events__Aggregator__Event::$global_id_key,
											$item->organizer[ $key ]->global_id
										);
									}

									// Create the Organizer Global ID History.
									if ( ! empty( $item->organizer[ $key ]->global_id_lineage ) ) {
										foreach ( $item->organizer[ $key ]->global_id_lineage as $gid ) {
											add_post_meta(
												$organizer_id,
												Tribe__Events__Aggregator__Event::$global_id_lineage_key,
												$gid
											);
										}
									}
								} else {
									$event_organizers[] = $organizer_data['ID'] = $organizer_id; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found

									// Here we might need to update the Organizer depending we found something based on old code.
									if ( 'retain' === $update_authority_setting ) {
										// When we get here we say that we skipped an Organizer.
										$activity->add( 'organizer', 'skipped', $organizer_id );

									} else {
										if ( 'preserve_changes' === $update_authority_setting ) {
											$organizer_data = Tribe__Events__Aggregator__Event::preserve_changed_fields( $organizer_data );
										}

										// Update the Organizer.
										Tribe__Events__Organizer::instance()->update( $organizer_id, $organizer_data );

										// Tell that we updated the Organizer to the activity tracker.
										$activity->add( 'organizer', 'updated', $organizer_id );
									}
								}
							}
						}
					}
				}

				// Update the organizer submission data.
				$event['Organizer']['OrganizerID'] = $event_organizers;

				// Let's remove this Organizer from the Event information if we found it.
				if ( isset( $key ) && is_numeric( $key ) ) {
					unset( $event['Organizer'][ $key ] );
				}
			}

			/**
			 * Filters the event data before any sort of saving of the event
			 *
			 * @param array $event Event data to save.
			 * @param Tribe__Events__Aggregator__Record__Abstract Importer record.
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
				 * @param array $event Event data to save.
				 * @param Tribe__Events__Aggregator__Record__Abstract Importer record.
				 */
				$event = apply_filters( 'tribe_aggregator_before_update_event', $event, $this );

				$event['ID'] = tribe_update_event( $event['ID'], $event );
				remove_filter( 'tribe_tracker_enabled', '__return_false' );

				// since the Event API only supports the _setting_ of these meta fields, we need to manually.
				// delete them rather than relying on Tribe__Events__API::saveEventMeta().
				if ( isset( $event['EventShowMap'] ) && ! tribe_is_truthy( $event['EventShowMap'] ) ) {
					delete_post_meta( $event['ID'], '_EventShowMap' );
				}

				if ( isset( $event['EventShowMapLink'] ) && ! tribe_is_truthy( $event['EventShowMapLink'] ) ) {
					delete_post_meta( $event['ID'], '_EventShowMapLink' );
				}

				// Log that this event was updated.
				$activity->add( 'event', 'updated', $event['ID'] );
			} else {
				if ( 'url' !== $this->origin && isset( $event[ $unique_field['target'] ] ) ) {
					if ( isset( $existing_ids[ $event[ $unique_field['target'] ] ] ) ) {
						// we should not be here; probably a concurrency issue.
						continue;
					}
				}

				// during cron runs the user will be set to 0; we assign the event to the first user that can edit events.
				if ( ! isset( $event['post_author'] ) ) {
					$event['post_author'] = $default_user_id;
				}

				/**
				 * Filters the event data before inserting event
				 *
				 * @param array                                       $event  Event data to save.
				 * @param Tribe__Events__Aggregator__Record__Abstract $record Importer record.
				 */
				$event = apply_filters( 'tribe_aggregator_before_insert_event', $event, $this );

				$event['ID'] = tribe_create_event( $event );

				// Log this event was created.
				$activity->add( 'event', 'created', $event['ID'] );

				// Create the Event Global ID.
				if ( ! empty( $item->global_id ) ) {
					update_post_meta( $event['ID'], Tribe__Events__Aggregator__Event::$global_id_key, $item->global_id );
				}

				// Create the Event Global ID History.
				if ( ! empty( $item->global_id_lineage ) ) {
					foreach ( $item->global_id_lineage as $gid ) {
						add_post_meta( $event['ID'], Tribe__Events__Aggregator__Event::$global_id_lineage_key, $gid );
					}
				}
			}

			Records::instance()->add_record_to_event( $event['ID'], $this->id, $this->origin );

			// Add post parent possibility.
			if ( empty( $event['parent_uid'] ) && ! empty( $unique_field ) && ! empty( $event[ $unique_field['target'] ] ) ) {
				$possible_parents[ $event['ID'] ] = $event[ $unique_field['target'] ];
			}

			// Save the unique field information.
			if ( ! empty( $event[ $unique_field['target'] ] ) ) {
				update_post_meta( $event['ID'], "_{$unique_field['target']}", $event[ $unique_field['target'] ] );
			}

			// Save the meta data in case of updating to pro later on.
			if ( ! empty( $event['EventRecurrenceRRULE'] ) ) {
				update_post_meta( $event['ID'], '_EventRecurrenceRRULE', $event['EventRecurrenceRRULE'] );
			}

			// Are there any existing event categories for this event?.
			$terms = wp_get_object_terms( $event['ID'], Tribe__Events__Main::TAXONOMY );

			if ( is_wp_error( $terms ) ) {
				$terms = [];
			}

			// If so, should we preserve those categories?.
			if ( ! empty( $terms ) && 'preserve_changes' === $update_authority_setting ) {
				$terms = wp_list_pluck( $terms, 'term_id' );
				unset( $event['categories'] );
			}

			if ( ! empty( $event['categories'] ) ) {
				foreach ( $event['categories'] as $cat ) {
					if ( ! $term = term_exists( $cat, Tribe__Events__Main::TAXONOMY ) ) { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.term_exists_term_exists, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
						$term = wp_insert_term( $cat, Tribe__Events__Main::TAXONOMY );
						if ( ! is_wp_error( $term ) ) {
							$terms[] = (int) $term['term_id'];

							// Track that we created an event category.
							$activity->add( 'cat', 'created', $term['term_id'] );
						}
					} else {
						$terms[] = (int) $term['term_id'];
					}
				}
			}

			$tags = [];
			if ( ! empty( $event['tags'] ) ) {
				foreach ( $event['tags'] as $tag_name ) {
					if ( ! $tag = term_exists( $tag_name, 'post_tag' ) ) { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.term_exists_term_exists, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
						$tag = wp_insert_term( $tag_name, 'post_tag' );
						if ( ! is_wp_error( $tag ) ) {
							$tags[] = (int) $tag['term_id'];

							// Track that we created a post tag.
							$activity->add( 'tag', 'created', $tag['term_id'] );
						}
					} else {
						$tags[] = (int) $tag['term_id'];
					}
				}
			}

			// if we are setting all events to a category specified in saved import.
			if ( ! empty( $this->meta['category'] ) ) {
				$terms[] = (int) $this->meta['category'];
			}

			$normalized_categories = tribe_normalize_terms_list( $terms, Tribe__Events__Main::TAXONOMY );
			$normalized_tags       = tribe_normalize_terms_list( $tags, 'post_tag' );
			wp_set_object_terms( $event['ID'], $normalized_categories, Tribe__Events__Main::TAXONOMY, false );
			wp_set_object_terms( $event['ID'], $normalized_tags, 'post_tag', false );

			// If we have a Image Field from Service.
			if ( ! empty( $event['image'] ) ) {
				$this->import_event_image( $event, $activity );
			}

			// If we have a Image Field for the Venue from Service.
			if ( ! empty( $item->venue->image ) && $venue_id ) {
				$this->import_venue_image( $venue_id, $item->venue->image, $activity );
			}

			// update the existing IDs in the context of this batch.
			if ( $unique_field && isset( $event[ $unique_field['target'] ] ) ) {
				$existing_ids[ $event[ $unique_field['target'] ] ] = (object) [
					'post_id'    => $event['ID'],
					'meta_value' => $event[ $unique_field['target'] ],
				];
			}

			/**
			 * Fires after a single event has been created/updated, and  with it its linked
			 * posts, with import data.
			 *
			 * @since 4.6.16
			 *
			 * @param array $event  Which Event data was sent.
			 * @param array $item   Raw version of the data sent from EA.
			 * @param self  $record The record we are dealing with.
			 */
			do_action( 'tribe_aggregator_after_insert_post', $event, $item, $this );
		}

		remove_filter( 'tribe-post-origin', [ Records::instance(), 'filter_post_origin' ], 10 );

		/**
		 * Fires after events and linked posts have been inserted in the database.
		 *
		 * @since 4.5.13
		 *
		 * @param array                                       $items    An array of items to insert.
		 * @param array                                       $meta     The record meta information.
		 * @param Tribe__Events__Aggregator__Record__Activity $activity The record insertion activity report.
		 */
		do_action( 'tribe_aggregator_after_insert_posts', $items, $this->meta, $activity );

		/**
		 * Finally resume tracking changes when all events, and linked posts, have been updated/inserted.
		 */
		remove_filter( 'tribe_tracker_enabled', '__return_false' );

		$final_created_events = (int) $activity->count( Tribe__Events__Main::POSTTYPE );

		if ( $expected_created_events === $final_created_events ) {
			$activity->set_last_status( Tribe__Events__Aggregator__Record__Activity::STATUS_SUCCESS );
		} elseif ( $initial_created_events === $final_created_events ) {
			$activity->set_last_status( Tribe__Events__Aggregator__Record__Activity::STATUS_FAIL );
		} else {
			$activity->set_last_status( Tribe__Events__Aggregator__Record__Activity::STATUS_PARTIAL );
		}

		return $activity;
	}

	/**
	 * Gets all ids that already exist in the post meta table from the provided records
	 *
	 * @param array $import_data The import data.
	 *
	 * @return array
	 */
	protected function get_existing_ids_from_import_data( $import_data ) {
		$unique_field = $this->get_unique_field();

		if ( ! $unique_field ) {
			return [];
		}

		if ( ! empty( $this->meta['ids_to_import'] ) && 'all' !== $this->meta['ids_to_import'] ) {
			if ( is_array( $this->meta['ids_to_import'] ) ) {
				$selected_ids = $this->meta['ids_to_import'];
			} else {
				$selected_ids = json_decode( $this->meta['ids_to_import'] );
			}
		} else {
			$source_field = $unique_field['source'];
			$selected_ids = array_filter(
				array_map(
					static function ( $entry ) use ( $source_field ) {
						$array_entry = (array) $entry;
						return $array_entry[ $source_field ] ?? null;
					},
					$import_data
				)
			);
		}

		if ( empty( $selected_ids ) ) {
			return [];
		}

		$event_object = new Tribe__Events__Aggregator__Event();

		return $event_object->get_existing_ids( $this->meta['origin'], $selected_ids );
	}

	/**
	 * Filters the import data by the selected IDs.
	 *
	 * @param array $import_data The import data.
	 *
	 * @return array
	 */
	protected function filter_data_by_selected( $import_data ) {
		$unique_field = $this->get_unique_field();

		if ( ! $unique_field ) {
			return $import_data;
		}

		// It's safer to use Empty to check here, prevents notices.
		if ( empty( $this->meta['ids_to_import'] ) ) {
			return $import_data;
		}

		if ( 'all' === $this->meta['ids_to_import'] ) {
			return $import_data;
		}

		$selected_ids = maybe_unserialize( $this->meta['ids_to_import'] );

		$selected = [];

		foreach ( $import_data as $data ) {
			if ( ! in_array( $data->{$unique_field['source']}, $selected_ids ) ) {
				continue;
			}

			$selected[] = $data;
		}

		return $selected;
	}

	/**
	 * Gets the unique field map for the current origin and the specified post type.
	 *
	 * @param string $post_type The linked post type.
	 *
	 * @return array|null
	 */
	protected function get_unique_field( $post_type = null ) {
		$fields = self::$unique_id_fields;

		switch ( $post_type ) {
			case 'venue':
				$fields = self::$unique_venue_id_fields;
				break;
			case 'organizer':
				$fields = self::$unique_organizer_id_fields;
				break;
			default:
				break;
		}

		if ( ! isset( $fields[ $this->meta['origin'] ] ) ) {
			return null;
		}

		return $fields[ $this->meta['origin'] ];
	}

	/**
	 * Finalizes the import record for insert
	 */
	public function finalize() {
		$this->update_meta( 'finalized', true );

		/**
		 * Fires after a record has been finalized and right before it starts importing.
		 *
		 * @since 4.6.21
		 *
		 * @param int   $id   The Record post ID.
		 * @param array $meta An array of meta for the record.
		 * @param self  $this The Record object itself.
		 */
		do_action( 'tribe_aggregator_record_finalized', $this->id, $this->meta, $this );
	}

	/**
	 * Preserve Event options.
	 *
	 * @param array $event Event data.
	 *
	 * @return array
	 */
	public static function preserve_event_option_fields( $event ) {
		$event_post = get_post( $event['ID'] );
		$post_meta  = Tribe__Events__API::get_and_flatten_event_meta( $event['ID'] );

		// Preserve show map.
		if ( isset( $post_meta['_EventShowMap'] ) && tribe_is_truthy( $post_meta['_EventShowMap'] ) ) {
			$event['EventShowMap'] = $post_meta['_EventShowMap'];
		}
		// Preserve map link.
		if ( isset( $post_meta['_EventShowMapLink'] ) && tribe_is_truthy( $post_meta['_EventShowMapLink'] ) ) {
			$event['EventShowMapLink'] = $post_meta['_EventShowMapLink'];
		}

		// we want to preserve this option if not explicitly being overridden.
		if ( ! isset( $event['EventHideFromUpcoming'] ) && isset( $post_meta['_EventHideFromUpcoming'] ) ) {
			$event['EventHideFromUpcoming'] = $post_meta['_EventHideFromUpcoming'];
		}

		// we want to preserve the existing sticky state unless it is explicitly being overridden.
		if ( ! isset( $event['EventShowInCalendar'] ) && '-1' == $event_post->menu_order ) {
			$event['EventShowInCalendar'] = 'yes';
		}

		// we want to preserve the existing featured state unless it is explicitly being overridden.
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
		// Attempt to grab the event image.
		$image_import = tribe( 'events-aggregator.main' )->api( 'image' )->get( $event['image']->id, $this );

		/**
		 * Filters the returned event image url
		 *
		 * @param array|bool $image Attachment information.
		 * @param array      $event Event array.
		 */
		$image = apply_filters( 'tribe_aggregator_event_image', $image_import, $event );

		// If there was a problem bail out.
		if ( false === $image ) {
			return false;
		}

		// Verify for more Complex Errors.
		if ( is_wp_error( $image ) ) {
			return $image;
		}

		return $image;
	}

	/**
	 * Imports the image contained in the post data `image` field if any.
	 *
	 * @param array $data A post data in array format.
	 *
	 * @return object|bool An object with the image post ID or `false` on failure.
	 */
	public function import_image( $data ) {
		if (
			empty( $data['image'] )
			|| ! (
				filter_var( $data['image'], FILTER_VALIDATE_URL )
				|| filter_var( $data['image'], FILTER_VALIDATE_INT )
			)
		) {
			return false;
		}

		$uploader     = new Tribe__Image__Uploader( $data['image'] );
		$thumbnail_id = $uploader->upload_and_get_attachment_id();

		return false !== $thumbnail_id ? (object) [ 'post_id' => $thumbnail_id ] : false;
	}

	/**
	 * Whether an origin has more granular policies concerning an import setting or not.
	 *
	 * @param string $origin The import origin to check.
	 * @param string $setting The setting to check.
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

	/**
	 * Cast error responses from the Service to WP_Errors to ease processing down the line.
	 *
	 * If a response is a WP_Error already or is not an error response then it will not be modified.
	 *
	 * @since 4.5.9
	 *
	 * @param WP_Error|object $import_data An error created from the Service response.
	 *
	 * @return array|WP_Error
	 */
	protected function maybe_cast_to_error( $import_data ) {
		if ( is_wp_error( $import_data ) ) {
			return $import_data;
		}

		if ( ! empty( $import_data->status ) && 'error' === $import_data->status ) {
			$import_data = (array) $import_data;
			$code        = Tribe__Utils__Array::get( $import_data, 'message_code', 'error:import-failed' );
			/** @var Tribe__Events__Aggregator__Service $service */
			$service     = tribe( 'events-aggregator.service' );
			$message     = Tribe__Utils__Array::get( $import_data, 'message', $service->get_service_message( 'error:import-failed' ) );
			$data        = Tribe__Utils__Array::get( $import_data, 'data', [] );
			$import_data = new WP_Error( $code, $message, $data );
		}

		return $import_data;
	}

	/**
	 * Sets the post associated with this record.
	 *
	 * @since 4.5.11
	 *
	 * @param WP_post|int $post A post object or post ID.
	 */
	public function set_post( $post ) {
		if ( ! $post instanceof WP_Post ) {
			$post = get_post( $post );
		}
		$this->post = $post;
	}

	/**
	 * Returns the user ID of the first user that can edit events or the current user ID if available.
	 *
	 * During cron runs current user ID will be set to 0; here we try to get a legit author user ID to
	 * be used as an author using the first non-0 user ID among the record author, the current user, the
	 * first available event editor.
	 *
	 * @since 4.5.11
	 *
	 * @return int The user ID or `0` (not logged-in user) if not possible.
	 */
	protected function get_default_user_id() {
		$post_type_object = get_post_type_object( Tribe__Events__Main::POSTTYPE );

		// try the record author.
		if ( ! empty( $this->post->post_author ) && user_can( $this->post->post_author, $post_type_object->cap->edit_posts ) ) {
			return $this->post->post_author;
		}

		// try the current user.
		$current_user_id = get_current_user_id();

		if ( ! empty( $current_user_id ) && current_user_can( $post_type_object->cap->edit_posts ) ) {
			return $current_user_id;
		}

		// let's try and find a legit author among the available event authors.
		$authors = get_users( [ 'who' => 'authors' ] );
		foreach ( $authors as $author ) {
			if ( user_can( $author, $post_type_object->cap->edit_posts ) ) {
				return $author->ID;
			}
		}

		return 0;
	}

	/**
	 * Assigns a new post thumbnail to the specified post if needed.
	 *
	 * @since 4.5.13
	 *
	 * @param int $post_id          The ID of the post the thumbnail should be assigned to.
	 * @param int $new_thumbnail_id The new attachment post ID.
	 *
	 * @return bool Whether the post thumbnail ID changed or not.
	 */
	protected function set_post_thumbnail( $post_id, $new_thumbnail_id ) {
		$current_thumbnail_id = has_post_thumbnail( $post_id )
			? (int) get_post_thumbnail_id( $post_id )
			: false;

		if ( empty( $current_thumbnail_id ) || $current_thumbnail_id !== (int) $new_thumbnail_id ) {
			set_post_thumbnail( $post_id, $new_thumbnail_id );

			return true;
		}

		return false;
	}

	/**
	 * Getter/setter to check/set whether the import for this record should be queued on EA Service or not.
	 *
	 * Note this is a passive check: if the meta is not set or set to `false` we assume the import
	 * should not be queued on EA Service.
	 *
	 * @since 4.6.2
	 *
	 * @param bool $should_queue_import If a value is provided here then the `should_queue_import` meta will.
	 *                                  be set to the boolean representation of that value.
	 *
	 * @return bool
	 */
	public function should_queue_import( $should_queue_import = null ) {
		$key = 'should_queue_import';

		if ( null === $should_queue_import ) {
			return isset( $this->meta[ $key ] ) && true == $this->meta[ $key ];
		}

		$this->update_meta( $key, (bool) $should_queue_import );
	}

	/**
	 * Attaches a service-provided image to an organizer.
	 *
	 * @since 4.6.9
	 *
	 * @param int                                         $organizer_id The organizer post ID.
	 * @param string                                      $image_url The URL to the image that should be imported.
	 * @param Tribe__Events__Aggregator__Record__Activity $activity The importer activity so far.
	 *
	 * @return bool Whether the image was attached to the organizer or not.
	 */
	public function import_organizer_image( $organizer_id, $image_url, $activity ) {
		/**
		 * Whether the organizer image should be imported and attached or not.
		 *
		 * @since 4.6.9
		 *
		 * @param bool                                        $import_organizer_image Defaults to `true`.
		 * @param int                                         $organizer_id           The organizer post ID.
		 * @param string                                      $image_url              The URL to the image that should be imported.
		 * @param Tribe__Events__Aggregator__Record__Activity $activity               The importer activity so far.
		 */
		$import_organizer_image = apply_filters( 'tribe_aggregator_import_organizer_image', true, $organizer_id, $image_url, $activity );

		if ( ! $import_organizer_image ) {
			return false;
		}

		if ( ! tribe_is_organizer( $organizer_id ) ) {
			return false;
		}

		return $this->import_and_attach_image_to( $organizer_id, $image_url, $activity );
	}

	/**
	 * Attaches a service-provided image to a venue.
	 *
	 * @since 4.6.9
	 *
	 * @param int                                         $venue_id  The venue post ID.
	 * @param string                                      $image_url URL to the image.
	 * @param Tribe__Events__Aggregator__Record__Activity $activity  The importer activity so far.
	 *
	 * @return bool Whether the image was attached to the venue or not.
	 */
	public function import_venue_image( $venue_id, $image_url, $activity ) {
		/**
		 * Whether the venue image should be imported and attached or not.
		 *
		 * @since 4.6.9
		 *
		 * @param bool                                        $import_venue_image Defaults to `true`.
		 * @param int                                         $venue_id           The venue post ID.
		 * @param string                                      $image_url          The URL to the image that should be imported.
		 * @param Tribe__Events__Aggregator__Record__Activity $activity           The importer activity so far.
		 */
		$import_venue_image = apply_filters( 'tribe_aggregator_import_venue_image', true, $venue_id, $image_url, $activity );

		if ( ! $import_venue_image ) {
			return false;
		}

		if ( ! tribe_is_venue( $venue_id ) ) {
			return false;
		}

		return $this->import_and_attach_image_to( $venue_id, $image_url, $activity );
	}

	/**
	 * Imports and attaches an image as post thumbnail to a post.
	 *
	 * @since 4.6.9
	 *
	 * @param int                                         $post_id   The post ID.
	 * @param string                                      $image_url The url to the image.
	 * @param Tribe__Events__Aggregator__Record__Activity $activity  The importer activity so far.
	 *
	 * @return bool `true` if the image was correctly downloaded and attached, `false` otherwise.
	 */
	protected function import_and_attach_image_to( $post_id, $image_url, $activity ) {
		$args = [
			'ID'         => $post_id,
			'image'      => $image_url,
			'post_title' => get_the_title( $post_id ),
		];

		$image = $this->import_image( $args );

		if ( empty( $image ) ) {
			return false;
		}

		if ( is_wp_error( $image ) || empty( $image->post_id ) ) {
			return false;
		}

		// Set as featured image.
		$image_attached = $this->set_post_thumbnail( $post_id, $image->post_id );

		if ( $image_attached ) {
			// Log this attachment was created.
			$activity->add( 'attachment', 'created', $image->post_id );
		}

		return true;
	}

	/**
	 * Attaches a service-provided image to an event.
	 *
	 * @since 4.6.9
	 *
	 * @param array                                       $event The event data.
	 * @param Tribe__Events__Aggregator__Record__Activity $activity The importer activity so far.
	 *
	 * @return bool Whether the image was attached to the event or not.
	 */
	public function import_event_image( $event, $activity ) {
		// If this is not a valid event no need for additional work.
		if ( empty( $event['ID'] ) || ! tribe_is_event( $event['ID'] ) ) {
			return false;
		}

		/**
		 * Whether the event image should be imported and attached or not.
		 *
		 * @since 4.6.9
		 *
		 * @param bool                                        $import_event_image Defaults to `true`.
		 * @param array                                       $event              The event post ID.
		 * @param Tribe__Events__Aggregator__Record__Activity $activity           The importer activity so far.
		 *
		 * @return bool Either to import or not the image of the event.
		 */
		$import_event_image = apply_filters( 'tribe_aggregator_import_event_image', true, $event, $activity );

		if ( ! $import_event_image ) {
			return false;
		}

		if ( is_object( $event['image'] ) ) {
			$image = $this->import_aggregator_image( $event );
		} else {
			$image = $this->import_image( $event );
		}

		if ( $image && ! is_wp_error( $image ) && ! empty( $image->post_id ) ) {

			// Set as featured image.
			$featured_status = $this->set_post_thumbnail( $event['ID'], $image->post_id );

			if ( $featured_status ) {
				// Log this attachment was created.
				$activity->add( 'attachment', 'created', $image->post_id );

				return true;
			}
		}

		return false;
	}

	/**
	 * Returns this record last child record or the record itself if no children are found.
	 *
	 * @since 4.6.15
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract
	 */
	public function last_child() {
		$last_child_post = $this->get_last_child_post();

		return $last_child_post && $last_child_post instanceof WP_Post
			? Records::instance()->get_by_post_id( $last_child_post->ID )
			: $this;
	}

	/**
	 * Returns this record last child post object.
	 *
	 * @since 4.6.15
	 *
	 * @param bool $force Whether to use the last child cached value or refetch it.
	 *
	 * @return WP_Post|false Either the last child post object or `false` on failure.
	 */
	public function get_last_child_post( $force = false ) {
		if ( $this->post->post_parent ) {
			return $this->post;
		}

		if ( ! $force && null !== $this->last_child ) {
			return $this->last_child;
		}

		$children_query_args = [
			'posts_per_page' => 1,
			'order'          => 'DESC',
			'order_by'       => 'modified',
		];

		if ( ! empty( $this->post ) && $this->post instanceof WP_Post ) {
			$children_query_args['post_parent'] = $this->post->ID;
		}

		$last_children_query = $this->query_child_records( $children_query_args );

		if ( $last_children_query->have_posts() ) {
			return reset( $last_children_query->posts );
		}

		return false;
	}

	/**
	 * Whether this record failed before a specific time.
	 *
	 * @since 4.6.15
	 *
	 * @param string|int $time A timestamp or a string parseable by the `strtotime` function.
	 *
	 * @return bool
	 */
	public function failed_before( $time ) {
		$last_import_status = $this->get_last_import_status( 'error', true );

		if ( empty( $last_import_status ) ) {
			return false;
		}

		if ( ! is_numeric( $time ) ) {
			$time = strtotime( $time );
		}

		return strtotime( $this->post->post_modified ) <= (int) $time;
	}

	/**
	 * Whether the record has its own last import status stored in the meta or
	 * it should be read from its last child record.
	 *
	 * @since 4.6.15
	 *
	 * @return bool
	 */
	protected function has_own_last_import_status() {
		return ! empty( $this->meta['last_import_status'] );
	}

	/**
	 * Returns the default retry interval depending on this record frequency.
	 *
	 * @since 4.6.15
	 *
	 * @return int
	 */
	public function get_retry_interval() {
		if ( $this->frequency->interval === DAY_IN_SECONDS ) {
			$retry_interval = 6 * HOUR_IN_SECONDS;
		} elseif ( $this->frequency->interval < DAY_IN_SECONDS ) {
			// do not retry and let the scheduled import try again next time.
			$retry_interval = 0;
		} else {
			$retry_interval = DAY_IN_SECONDS;
		}

		/**
		 * Filters the retry interval between a failure and a retry for a scheduled record.
		 *
		 * @since 4.6.15
		 *
		 * @param int                                         $retry_interval An interval in seconds; defaults to the record frequency / 2.
		 * @param Tribe__Events__Aggregator__Record__Abstract $this.
		 */
		return apply_filters( 'tribe_aggregator_scheduled_records_retry_interval', $retry_interval, $this );
	}

	/**
	 * Returns the record retry timestamp.
	 *
	 * @since 4.6.15
	 *
	 * @return int|bool Either the record retry timestamp or `false` if the record will
	 *                  not retry to import.
	 */
	public function get_retry_time() {
		$retry_interval = $this->get_retry_interval();

		if ( empty( $retry_interval ) ) {
			return false;
		}

		if ( ! $this->get_last_import_status( 'error', true ) ) {
			return false;
		}

		$last_attempt_time = strtotime( $this->last_child()->post->post_modified_gmt );
		$retry_time        = $last_attempt_time + (int) $retry_interval;

		if ( $retry_time < time() ) {
			$retry_time = false;
		}

		/**
		 * Filters the retry timestamp for a scheduled record.
		 *
		 * @since 4.6.15
		 *
		 * @param int                                         $retry_time A timestamp.
		 * @param Tribe__Events__Aggregator__Record__Abstract $this.
		 */
		return apply_filters( 'tribe_aggregator_scheduled_records_retry_interval', $retry_time, $this );
	}

	/**
	 * Whether the record will try to fetch the import data polling EA Service or
	 * expecting batches of data being pushed to it by EA Service.
	 *
	 * @since 4.6.15
	 *
	 * @return bool
	 */
	public function is_polling() {
		$is_polling = empty( $this->meta['allow_batch_push'] ) || ! tribe_is_truthy( $this->meta['allow_batch_push'] );

		/**
		 * Whether the current record is a Service polling one or not.
		 *
		 * @since 4.6.15
		 *
		 * @param bool                                        $is_polling.
		 * @param Tribe__Events__Aggregator__Record__Abstract $record.
		 */
		return (bool) apply_filters( 'tribe_aggregator_record_is_polling', $is_polling, $this );
	}

	/**
	 *
	 * Generates the hash that will be expected in the for the next batch of events.
	 *
	 * @since 4.6.15
	 *
	 * @return string
	 */
	public function generate_next_batch_hash() {
		return md5( uniqid( '', true ) );
	}
}
