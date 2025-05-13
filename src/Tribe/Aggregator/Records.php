<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Records {
	/**
	 * Slug of the Post Type used for Event Aggregator Records.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	public static $post_type = 'tribe-ea-record';

	/**
	 * Base slugs for all the EA Record Post Statuses.
	 *
	 * @since 4.3.0
	 *
	 * @var stdClass
	 */
	public static $status = [

		'success'  => 'tribe-ea-success',
		'failed'   => 'tribe-ea-failed',
		'pending'  => 'tribe-ea-pending',

		// Used to mark which are the Original Scheduled Import.
		'schedule' => 'tribe-ea-schedule',

		// Currently Not Displayed.
		'draft'    => 'tribe-ea-draft',
	];

	/**
	 * Static Singleton Holder.
	 *
	 * @since 4.3.0
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * The time, in "Y-m-d H:i:s" format, that's used to query records.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected $after_time;

	/**
	 * Static Singleton Factory Method.
	 *
	 * @since 4.3.0
	 *
	 * @return self
	 */
	public static function instance() {
		return tribe( 'events-aggregator.records' );
	}

	/**
	 * Set up all the hooks and filters.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function __construct() {
		// Make it an object for easier usage.
		if ( ! is_object( self::$status ) ) {
			self::$status = (object) self::$status;
		}
	}

	/**
	 * Adjusting the "Edit Post" link.
	 *
	 * @since 4.3.0
	 *
	 * @param string $link    The edit link.
	 * @param int    $post    The post ID.
	 * @param string $context The link context. If set to 'display' then ampersands are encoded.
	 *
	 * @return string
	 */
	public function filter_edit_link( $link, $post, $context ) {
		$post = get_post( $post );

		if ( $post->post_type !== self::$post_type ) {
			return $link;
		}

		$args = [
			'tab' => Tribe__Events__Aggregator__Tabs__Edit::instance()->get_slug(),
			'id'  => absint( $post->ID ),
		];

		return Tribe__Events__Aggregator__Page::instance()->get_url( $args );
	}

	/**
	 * Adjusting the "Delete Post" link.
	 *
	 * @since 4.3.0
	 *
	 * @param string $link    The delete link.
	 * @param int    $post    The post ID.
	 * @param bool   $context Whether to bypass the Trash and force deletion. Default false.
	 *
	 * @return string
	 */
	public function filter_delete_link( $link, $post, $context ) {
		$post = get_post( $post );

		if ( $post->post_type !== self::$post_type ) {
			return $link;
		}

		$tab = Tribe__Events__Aggregator__Tabs__Scheduled::instance();
		$args = [
			'tab'    => $tab->get_slug(),
			'action' => 'delete',
			'ids'    => absint( $post->ID ),
			'nonce'  => wp_create_nonce( 'aggregator_' . $tab->get_slug() . '_request' ),
		];

		return Tribe__Events__Aggregator__Page::instance()->get_url( $args );
	}

	/**
	 * Register and return the Aggregator Record Custom Post Type.
	 * Instead of having a method for returning and another for registering,
	 * we do it all in one single method depending on if it exists or not.
	 *
	 * @since 4.3.0
	 *
	 * @return stdClass|WP_Error
	 */
	public function get_post_type() {
		if ( post_type_exists( self::$post_type ) ) {
			return get_post_type_object( self::$post_type );
		}

		$args = [
			'description'        => esc_html__( 'Events Aggregator Record', 'the-events-calendar' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => [ 'aggregator-record', 'aggregator-records' ],
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'show_in_nav_menus'  => false,
			'menu_position'      => null,
			'supports'           => [],
		];

		$args['labels'] = [
			'name'               => esc_html_x( 'Aggregator Records', 'post type general name', 'the-events-calendar' ),
			'singular_name'      => esc_html_x( 'Aggregator Record', 'post type singular name', 'the-events-calendar' ),
			'menu_name'          => esc_html_x( 'Aggregator Records', 'admin menu', 'the-events-calendar' ),
			'name_admin_bar'     => esc_html_x( 'Aggregator Record', 'add new on admin bar', 'the-events-calendar' ),
			'add_new'            => esc_html_x( 'Add New', 'record', 'the-events-calendar' ),
			'add_new_item'       => esc_html__( 'Add New Aggregator Record', 'the-events-calendar' ),
			'new_item'           => esc_html__( 'New Aggregator Record', 'the-events-calendar' ),
			'edit_item'          => esc_html__( 'Edit Aggregator Record', 'the-events-calendar' ),
			'view_item'          => esc_html__( 'View Aggregator Record', 'the-events-calendar' ),
			'all_items'          => esc_html__( 'All Aggregator Records', 'the-events-calendar' ),
			'search_items'       => esc_html__( 'Search Aggregator Records', 'the-events-calendar' ),
			'parent_item_colon'  => esc_html__( 'Parent Aggregator Record:', 'the-events-calendar' ),
			'not_found'          => esc_html__( 'No Aggregator Records found.', 'the-events-calendar' ),
			'not_found_in_trash' => esc_html__( 'No Aggregator Records found in Trash.', 'the-events-calendar' ),
		];

		return register_post_type( self::$post_type, $args );
	}

	/**
	 * Register and return the Aggregator Record Custom Post Status.
	 * Instead of having a method for returning and another for registering,
	 * we do it all in one single method depending on if it exists or not.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $status Which status object you are looking for.
	 *
	 * @return stdClass|WP_Error|array
	 */
	public function get_status( $status = null ) {
		$registered_by_key  = (object) [];
		$registered_by_name = (object) [];

		foreach ( self::$status as $key => $name ) {
			$object = get_post_status_object( $name );
			$registered_by_key->{ $key } = $object;
			$registered_by_name->{ $name } = $object;
		}

		// Check if we already have the Status registered.
		if ( isset( $registered_by_key->{ $status } ) && is_object( $registered_by_key->{ $status } ) ) {
			return $registered_by_key->{ $status };
		}

		// Check if we already have the Status registered.
		if ( isset( $registered_by_name->{ $status } ) && is_object( $registered_by_name->{ $status } ) ) {
			return $registered_by_name->{ $status };
		}

		// Register the Success post status.
		$args   = [
			'label'               => esc_html_x( 'Imported', 'event aggregator status', 'the-events-calendar' ),
			// translators: %s is the number of imported records.
			'label_count'         => _nx_noop(
				'Imported <span class="count">(%s)</span>',
				'Imported <span class="count">(%s)</span>',
				'event aggregator status',
				'the-events-calendar'
			),
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => false,
		];
		$object = register_post_status( self::$status->success, $args );
		$registered_by_key->success = $registered_by_name->{'tribe-aggregator-success'} = $object;

		// Register the Failed post status.
		$args   = [
			'label'               => esc_html_x( 'Failed', 'event aggregator status', 'the-events-calendar' ),
			// translators: %s is the number of failed records.
			'label_count'         => _nx_noop(
				'Failed <span class="count">(%s)</span>',
				'Failed <span class="count">(%s)</span>',
				'event aggregator status',
				'the-events-calendar'
			),
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => false,
		];
		$object = register_post_status( self::$status->failed, $args );
		$registered_by_key->failed = $registered_by_name->{'tribe-aggregator-failed'} = $object;

		// Register the Schedule post status.
		$args   = [
			'label'               => esc_html_x( 'Schedule', 'event aggregator status', 'the-events-calendar' ),
			// translators: %s is the number of schedule records.
			'label_count'         => _nx_noop(
				'Schedule <span class="count">(%s)</span>',
				'Schedule <span class="count">(%s)</span>',
				'event aggregator status',
				'the-events-calendar'
			),
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => false,
		];
		$object = register_post_status( self::$status->schedule, $args );
		$registered_by_key->schedule = $registered_by_name->{'tribe-aggregator-schedule'} = $object;

		// Register the Pending post status.
		$args   = [
			'label'               => esc_html_x( 'Pending', 'event aggregator status', 'the-events-calendar' ),
			// translators: %s is the number of pending records.
			'label_count'         => _nx_noop(
				'Pending <span class="count">(%s)</span>',
				'Pending <span class="count">(%s)</span>',
				'event aggregator status',
				'the-events-calendar'
			),
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => false,
		];
		$object = register_post_status( self::$status->pending, $args );
		$registered_by_key->pending = $registered_by_name->{'tribe-aggregator-pending'} = $object;

		// Register the Pending post status.
		$args   = [
			'label'               => esc_html_x( 'Draft', 'event aggregator status', 'the-events-calendar' ),
			// translators: %s is the number of draft records.
			'label_count'         => _nx_noop(
				'Draft <span class="count">(%s)</span>',
				'Draft <span class="count">(%s)</span>',
				'event aggregator status',
				'the-events-calendar'
			),
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => false,
		];
		$object = register_post_status( self::$status->draft, $args );
		$registered_by_key->draft = $registered_by_name->{'tribe-aggregator-draft'} = $object;

		// Check if we already have the Status registered.
		if ( isset( $registered_by_key->{ $status } ) && is_object( $registered_by_key->{ $status } ) ) {
			return $registered_by_key->{ $status };
		}

		// Check if we already have the Status registered.
		if ( isset( $registered_by_name->{ $status } ) && is_object( $registered_by_name->{ $status } ) ) {
			return $registered_by_name->{ $status };
		}

		return $registered_by_key;
	}

	/**
	 * Count the number of imports based on origin.
	 *
	 * @since 4.3.0
	 *
	 * @param array<string> $type         The type of import.
	 * @param string|array  $raw_statuses The statuses of the imports to look in.
	 *
	 * @return array
	 */
	public function count_by_origin( $type = [ 'schedule', 'manual' ], $raw_statuses = '' ) {
		global $wpdb;

		$where = [
			'post_type = %s',
			'AND post_status NOT IN ( \'' . self::$status->draft . '\' )',
		];

		$statuses = [];

		// Make it an Array.
		$raw_statuses = (array) $raw_statuses;
		foreach ( $raw_statuses as $status ) {
			if ( ! isset( self::$status->{ $status } ) ) {
				continue;
			}

			// Get the Actual Status for the Database.
			$statuses[] = self::$status->{ $status };
		}

		if ( ! empty( $type ) ) {
			$where[] = 'AND ping_status IN ( \'' . implode( '\', \'', (array) $type ) . '\' )';
		}

		if ( ! empty( $statuses ) ) {
			$where[] = 'AND post_status IN ( \'' . implode( '\', \'', $statuses ) . '\' )';
		}

		$where = implode( ' ', $where );
		$sql = $wpdb->prepare( "SELECT post_mime_type as origin, COUNT(*) as count
		FROM $wpdb->posts
		WHERE {$where}
		GROUP BY origin;", self::$post_type );

		$results = $wpdb->get_results( $sql );

		// Prevents Warnings With `array_combine`.
		if ( empty( $results ) ) {
			return [];
		}

		$origins = wp_list_pluck( $results, 'origin' );
		$counts = wp_list_pluck( $results, 'count' );

		// Remove ea/ from the `post_mime_type`.
		foreach ( $origins as &$origin ) {
			$origin = str_replace( 'ea/', '', $origin );
		}

		return array_combine( $origins, $counts );
	}

	/**
	 * Returns an appropriate Record object for the given origin.
	 *
	 * @since 4.3.0
	 *
	 * @param string      $origin The record import origin.
	 * @param int|WP_Post $post   The record post or post ID.
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract An instance of the correct record class
	 *                                                     for the origin or an unsupported record
	 *                                                     instance.
	 */
	public function get_by_origin( $origin, $post = null ) {
		$record = null;

		switch ( $origin ) {
			case 'csv':
			case 'ea/csv':
				$record = new Tribe__Events__Aggregator__Record__CSV( $post );
				break;
			case 'eventbrite':
			case 'ea/eventbrite':
				$record = new Tribe__Events__Aggregator__Record__Eventbrite( $post );
				break;
			case 'gcal':
			case 'ea/gcal':
				$record = new Tribe__Events__Aggregator__Record__gCal( $post );
				break;
			case 'ical':
			case 'ea/ical':
				$record = new Tribe__Events__Aggregator__Record__iCal( $post );
				break;
			case 'ics':
			case 'ea/ics':
				$record = new Tribe__Events__Aggregator__Record__ICS( $post );
				break;
			case 'meetup':
			case 'ea/meetup':
				$record = new Tribe__Events__Aggregator__Record__Meetup( $post );
				break;
			case 'url':
			case 'ea/url':
				$record = new Tribe__Events__Aggregator__Record__Url( $post );
				break;
			default:
				// If there is no match then the record type is unsupported.
				$record = new Tribe__Events__Aggregator__Record__Unsupported( $post );
				break;
		}

		/**
		 * Allows filtering of Record object for custom origins and overrides.
		 *
		 * @since 4.6.24
		 *
		 * @param Tribe__Events__Aggregator__Record__Abstract|null $record Record object for given origin.
		 * @param string                                           $origin Import origin.
		 * @param WP_Post|null                                     $post   Record post data.
		 */
		$record = apply_filters( 'tribe_aggregator_record_by_origin', $record, $origin, $post );

		return $record;
	}

	/**
	 * Returns an appropriate Record object for the given post ID.
	 *
	 * @since 4.3.0
	 *
	 * @param int $post The post ID of the record.
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|WP_Error|null
	 */
	public function get_by_post_id( $post ) {
		$post = get_post( $post );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( ! $post instanceof WP_Post ) {
			return tribe_error( 'core:aggregator:invalid-record-object', [], [ $post ] );
		}

		if ( $post->post_type !== self::$post_type ) {
			return tribe_error( 'core:aggregator:invalid-record-post_type', [], [ $post ] );
		}

		if ( empty( $post->post_mime_type ) ) {
			return tribe_error( 'core:aggregator:invalid-record-origin', [], [ $post ] );
		}

		return $this->get_by_origin( $post->post_mime_type, $post );
	}

	/**
	 * Returns an appropriate Record object for the given import ID.
	 *
	 * @since 4.3.0
	 *
	 * @param int   $import_id Post ID of the aggregator import.
	 * @param array $args      An array of arguments to override the default ones.
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|WP_Error
	 */
	public function get_by_import_id( $import_id, array $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'post_type'   => self::$post_type,
				'meta_key'    => $this->prefix_meta( 'import_id' ),
				'meta_value'  => $import_id,
				'post_status' => [
					self::$status->draft,
					self::$status->pending,
					self::$status->success,
				],
			]
		);

		$query = new WP_Query( $args );

		if ( empty( $query->post ) ) {
			return tribe_error( 'core:aggregator:invalid-import-id', [], [ $import_id ] );
		}

		$post = $query->post;

		if ( empty( $post->post_mime_type ) ) {
			return tribe_error( 'core:aggregator:invalid-record-origin', [], [ $post ] );
		}

		return $this->get_by_origin( $post->post_mime_type, $post );
	}

	/**
	 * Returns an appropriate Record object for the given event ID.
	 *
	 * @since 4.3.0
	 *
	 * @param  int $event_id Post ID of the Event.
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|WP_Error
	 */
	public function get_by_event_id( $event_id ) {
		$event = get_post( $event_id );

		if ( ! $event instanceof WP_Post ) {
			return tribe_error( 'core:aggregator:invalid-event-id', [], [ $event_id ] );
		}

		$record_id = get_post_meta( $event->ID, Tribe__Events__Aggregator__Event::$record_key, true );

		if ( empty( $record_id ) ) {
			return tribe_error( 'core:aggregator:invalid-import-id', [], [ $record_id ] );
		}

		return $this->get_by_post_id( $record_id );

	}

	/**
	 * Returns a WP_Query object built using some default arguments for records.
	 *
	 * @param array $args An array of arguments to override the default ones.
	 *
	 * @return WP_Query The built WP_Query object; since it's built with arguments
	 *                  the query will run, actually hitting the database, before
	 *                  returning.
	 */
	public function query( $args = [] ) {
		$statuses = self::$status;
		$defaults = [
			'post_status' => [ $statuses->success, $statuses->failed, $statuses->pending ],
			'orderby'     => 'modified',
			'order'       => 'DESC',
		];

		$args = (array) $args;

		if ( isset( $args['after'] ) ) {
			$before_timestamp = is_numeric( $args['after'] )
				? $args['after']
				: Tribe__Date_Utils::wp_strtotime( $args['after'] );
			$before_datetime  = new DateTime( "@{$before_timestamp}" );
			$this->after_time = $before_datetime->format( 'Y-m-d H:00:00' );

			add_filter( 'posts_where', [ $this, 'filter_posts_where' ] );

			tribe( 'logger' )->log_debug( "Filtering records happening after {$this->after_time}", 'EA Records' );
		}

		$args = (object) wp_parse_args( $args, $defaults );

		// Enforce the post type.
		$args->post_type = self::$post_type;

		// Run and return the query.
		return new WP_Query( $args );
	}

	/**
	 * Returns whether or not there are any scheduled imports.
	 *
	 * @since 4.3.0
	 *
	 * @return boolean
	 */
	public function has_scheduled() {
		static $has_scheduled = null;

		if ( null === $has_scheduled ) {
			$args = [
				'fields'         => 'ids',
				'post_status'    => $this->get_status( 'schedule' )->name,
				'posts_per_page' => 1,
			];

			$scheduled     = $this->query( $args );
			$has_scheduled = ! empty( $scheduled->posts );
		}

		return $has_scheduled;
	}

	/**
	 * Returns whether or not there have been any import requests.
	 *
	 * @since 4.3.0
	 *
	 * @return boolean
	 */
	public function has_history() {
		static $has_history = null;

		if ( null === $has_history ) {
			$args = [
				'fields'         => 'ids',
				'posts_per_page' => 1,
			];

			$history     = $this->query( $args );
			$has_history = ! empty( $history->posts );
		}

		return $has_history;
	}

	/**
	 * Filter the Admin page tile and add Tab Name.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $admin_title Full Admin Title.
	 * @param  string $title       Original Title from the Page.
	 *
	 * @return string
	 */
	public function filter_admin_title( $admin_title, $title ) {
		if ( ! Tribe__Events__Aggregator__Page::instance()->is_screen() ) {
			return $admin_title;
		}

		$tab = $this->get_active();
		return $tab->get_label() . ' &ndash; ' . $admin_title;
	}

	/**
	 * Fetches the current active tab.
	 *
	 * @since 4.3.0
	 *
	 * @return object An instance of the Class used to create the Tab.
	 */
	public function get_active() {
		/**
		 * Allow Developers to change the default tab.
		 * @param string $slug
		 */
		$default = apply_filters( 'tribe_aggregator_default_tab', 'new' );

		$tab = ! empty( $_GET['tab'] ) && $this->exists( $_GET['tab'] ) ? $_GET['tab'] : $default;

		// Return the active tab or the default one.
		return $this->get( $tab );
	}

	/**
	 * Start the import process.
	 *
	 * @since 4.3.0
	 *
	 * @return null
	 */
	public function action_do_import() {
		// First we convert the array to a json string.
		$json = json_encode( $_POST );

		// Then we convert the json string to a stdClass().
		$request = json_decode( $json, true );

		// Empty Required Variables.
		if ( empty( $_GET['key'] ) || empty( $request ) || empty( $request['data'] ) || empty( $request['data']['import_id'] ) ) {
			return wp_send_json_error();
		}

		$import_id = $request['data']['import_id'];
		$record = $this->get_by_import_id( $import_id );

		// We received an Invalid Import ID.
		if ( tribe_is_error( $record ) ) {
			return wp_send_json_error();
		}

		// Verify if Hash matches sent Key.
		if ( ! isset( $record->meta['hash'] ) || $record->meta['hash'] !== $_GET['key'] ) {
			return wp_send_json_error();
		}

		if ( ! empty( $_GET['trigger_new'] ) ) {
			$_GET['tribe_queue_sync'] = true;

			$record->update_meta( 'in_progress', null );
			$record->update_meta( 'queue_id', null );

			$record->set_status_as_pending();
			$record->process_posts( $request, true );
			$record->set_status_as_success();
		} else {
			$record->process_posts( $request, true );
		}

		return wp_send_json_success();
	}

	/**
	 * Return the origin of the import.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public function filter_post_origin() {
		return Tribe__Events__Aggregator__Event::$event_origin;
	}

	/**
	 * Adds the import record and origin to the imported event.
	 *
	 * @since 4.3.0
	 *
	 * @param int    $id        Event ID.
	 * @param int    $record_id Import Record ID.
	 * @param string $origin    Import Origin.
	 */
	public function add_record_to_event( $id, $record_id, $origin ) {
		$record = $this->get_by_post_id( $record_id );

		if ( tribe_is_error( $record ) ) {
			return;
		}

		// Set the event origin.
		update_post_meta( $id, '_EventOrigin', Tribe__Events__Aggregator__Event::$event_origin );

		// Add the Aggregator origin.
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$origin_key, $origin );

		// Add the Aggregator record.
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$record_key, $record_id );

		// Add the Aggregator source.
		if ( isset( $record->meta['source'] ) ) {
			update_post_meta( $id, Tribe__Events__Aggregator__Event::$source_key, $record->meta['source'] );
		}

		// Add the Aggregator import timestamp.
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$updated_key, $record->post->post_date );
	}

	/**
	 * Prefixes a String to be the Key for Record meta.
	 *
	 * @since  4.3.0
	 *
	 * @param  string $str String to append to the Prefix.
	 *
	 * @return string
	 */
	public function prefix_meta( $str = null ) {
		return Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . $str;
	}

	/**
	 * Fetches the Amount of seconds that we will hold a Record Log on the Posts Table.
	 *
	 * @since  4.3.2
	 *
	 * @return int
	 */
	public function get_retention() {
		return apply_filters( 'tribe_aggregator_record_retention', WEEK_IN_SECONDS );
	}

	/**
	 * Filters the records query to only return records after a defined time.
	 *
	 * @since 4.5.11
	 *
	 * @param string $where The original WHERE clause.
	 *
	 * @return string The updated WHERE clause.
	 */
	public function filter_posts_where( $where ) {
		if ( empty( $this->after_time ) ) {
			return $where;
		}

		/** @var wpdb $wpdb */
		global $wpdb;
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_modified >= %s", $this->after_time );

		remove_filter( 'posts_where', [ $this, 'filter_posts_where' ] );
		unset( $this->after_time );

		return $where;
	}

	/**
	 * Hooks all the actions and filters needed by the class.
	 *
	 * @since 4.6.15
	 */
	public function hook() {
		// Register the Custom Post Type.
		add_action( 'init', [ $this, 'get_post_type' ] );

		// Register the Custom Post Statuses.
		add_action( 'init', [ $this, 'get_status' ] );

		// Run the Import when Hitting the Event Aggregator Endpoint.
		add_action( 'tribe_aggregator_endpoint_insert', [ $this, 'action_do_import' ] );

		// Delete Link Filter.
		add_filter( 'get_delete_post_link', [ $this, 'filter_delete_link' ], 15, 3 );

		// Edit Link Filter.
		add_filter( 'get_edit_post_link', [ $this, 'filter_edit_link' ], 15, 3 );

		// Filter Eventbrite to Add Site to URL.
		add_filter(
			'tribe_aggregator_get_import_data_args',
			[ 'Tribe__Events__Aggregator__Record__Eventbrite', 'filter_add_site_get_import_data' ],
			10,
			2
		);

		// Filter ical events to preserve some fields that aren't supported by iCalendar.
		add_filter(
			'tribe_aggregator_before_update_event',
			[ 'Tribe__Events__Aggregator__Record__iCal', 'filter_event_to_preserve_fields' ],
			10,
			2
		);

		// Filter ics events to preserve some fields that aren't supported by ICS.
		add_filter(
			'tribe_aggregator_before_update_event',
			[ 'Tribe__Events__Aggregator__Record__ICS', 'filter_event_to_preserve_fields' ],
			10,
			2
		);

		// Filter gcal events to preserve some fields that aren't supported by Google Calendar.
		add_filter(
			'tribe_aggregator_before_update_event',
			[ 'Tribe__Events__Aggregator__Record__gCal', 'filter_event_to_preserve_fields' ],
			10,
			2
		);

		// Filter meetup events to force an event URL.
		add_filter(
			'tribe_aggregator_before_save_event',
			[ 'Tribe__Events__Aggregator__Record__Meetup', 'filter_event_to_force_url' ],
			10,
			2
		);

		// Filter meetup events to preserve some fields that aren't supported by Meetup.
		add_filter(
			'tribe_aggregator_before_update_event',
			[ 'Tribe__Events__Aggregator__Record__Meetup', 'filter_event_to_preserve_fields' ],
			10,
			2
		);

		// Filter eventbrite events to preserve some fields that aren't supported by Eventbrite.
		add_filter(
			'tribe_aggregator_before_update_event',
			[ 'Tribe__Events__Aggregator__Record__Eventbrite', 'filter_event_to_preserve_fields' ],
			10,
			2
		);

		add_filter(
			'tribe_aggregator_default_eventbrite_post_status',
			[ 'Tribe__Events__Aggregator__Record__Eventbrite', 'filter_set_default_post_status' ]
		);

		add_filter(
			'tribe_aggregator_new_event_post_status_before_import',
			[ 'Tribe__Events__Aggregator__Record__Eventbrite', 'filter_setup_do_not_override_post_status' ],
			10,
			3
		);
	}

	/**
	 * Filter records by source and data hash.
	 *
	 * @since 4.6.25
	 *
	 * @param string $source    Source value.
	 * @param string $data_hash Data hash.
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|false Record object or false if not found.
	 */
	public function find_by_data_hash( $source, $data_hash ) {
		/** @var WP_Query $matches */
		$matches = $this->query(
			[
				'post_status' => $this->get_status( 'schedule' )->name,
				'meta_query'  => [
					[
						'key'   => $this->prefix_meta( 'source' ),
						'value' => $source,
					],
				],
				'fields'      => 'ids',
			]
		);

		if ( empty( $matches->posts ) ) {
			return false;
		}

		foreach ( $matches->posts as $post_id ) {
			$this_record = $this->get_by_post_id( $post_id );

			if ( ! $this_record instanceof Tribe__Events__Aggregator__Record__Abstract ) {
				continue;
			}

			if ( $data_hash === $this_record->get_data_hash() ) {
				return $this_record;
			}
		}

		return false;
	}
}
