<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Records {
	/**
	 * Slug of the Post Type used for Event Aggregator Records
	 *
	 * @var string
	 */
	public static $post_type = 'tribe-ea-record';

	/**
	 * Base slugs for all the EA Record Post Statuses
	 *
	 * @var stdClass
	 */
	public static $status = array(
		'success'   => 'tribe-ea-success',
		'failed'    => 'tribe-ea-failed',
		'pending'   => 'tribe-ea-pending',

		// Used to mark which are the Original Scheduled Import
		'schedule' => 'tribe-ea-schedule',

		// Currently Not Displayed
		'draft'     => 'tribe-ea-draft',
	);

	/**
	 * Static Singleton Holder
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setup all the hooks and filters
	 *
	 * @return void
	 */
	private function __construct() {
		// Make it an object for easier usage
		if ( ! is_object( self::$status ) ) {
			self::$status = (object) self::$status;
		}

		// Register the Custom Post Type
		add_action( 'init', array( $this, 'get_post_type' ) );

		// Register the Custom Post Statuses
		add_action( 'init', array( $this, 'get_status' ) );

		// Run the Import when Hitting the Event Aggregator Endpoint
		add_action( 'tribe_aggregator_endpoint_insert', array( $this, 'action_do_import' ) );

		// Delete Link Filter
		add_filter( 'get_delete_post_link', array( $this, 'filter_delete_link' ), 15, 3 );

		// Edit Link Filter
		add_filter( 'get_edit_post_link', array( $this, 'filter_edit_link' ), 15, 3 );

		// Filter facebook events to force an event URL
		add_filter( 'tribe_aggregator_before_save_event', array( 'Tribe__Events__Aggregator__Record__Facebook', 'filter_event_to_force_url' ), 10, 2 );

		// Filter meetup events to force an event URL
		add_filter( 'tribe_aggregator_before_save_event', array( 'Tribe__Events__Aggregator__Record__Meetup', 'filter_event_to_force_url' ), 10, 2 );
	}

	public function filter_edit_link( $link, $post, $context ) {
		$post = get_post( $post );

		if ( $post->post_type !== self::$post_type ) {
			return $link;
		}

		$args = array(
			'tab'    => Tribe__Events__Aggregator__Tabs__Edit::instance()->get_slug(),
			'id'     => absint( $post->ID ),
		);

		return Tribe__Events__Aggregator__Page::instance()->get_url( $args );
	}

	public function filter_delete_link( $link, $post, $context ) {
		$post = get_post( $post );

		if ( $post->post_type !== self::$post_type ) {
			return $link;
		}

		$tab = Tribe__Events__Aggregator__Tabs__Scheduled::instance();
		$args = array(
			'tab'    => $tab->get_slug(),
			'action' => 'delete',
			'ids'   => absint( $post->ID ),
			'nonce'  => wp_create_nonce( 'aggregator_' . $tab->get_slug() . '_request' ),
		);

		return Tribe__Events__Aggregator__Page::instance()->get_url( $args );
	}

	/**
	 * Register and return the Aggregator Record Custom Post Type
	 * Instead of having a method for returning and another registering
	 * we do it all in one single method depending if it exists or not
	 *
	 * @return stdClass|WP_Error
	 */
	public function get_post_type() {
		if ( post_type_exists( self::$post_type ) ) {
			return get_post_type_object( self::$post_type );
		}

		$args = array(
			'description'        => esc_html__( 'Events Aggregator Record', 'the-events-calendar' ),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => array( 'aggregator-record', 'aggregator-records' ),
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'show_in_nav_menus'  => false,
			'menu_position'      => null,
			'supports'           => array(),
		);

		$args['labels'] = array(
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
		);

		return register_post_type( self::$post_type, $args );
	}

	/**
	 * Register and return the Aggregator Record Custom Post Status
	 * Instead of having a method for returning and another registering
	 * we do it all in one single method depending if it exists or not
	 *
	 * @param  string $status Which status object you are looking for
	 *
	 * @return stdClass|WP_Error|array
	 */
	public function get_status( $status = null ) {
		$registered_by_key = (object) array();
		$registered_by_name = (object) array();

		foreach ( self::$status as $key => $name ) {
			$object = get_post_status_object( $name );
			$registered_by_key->{ $key } = $object;
			$registered_by_name->{ $name } = $object;
		}

		// Check if we already have the Status registered
		if ( isset( $registered_by_key->{ $status } ) && is_object( $registered_by_key->{ $status } ) ) {
			return $registered_by_key->{ $status };
		}

		// Check if we already have the Status registered
		if ( isset( $registered_by_name->{ $status } ) && is_object( $registered_by_name->{ $status } ) ) {
			return $registered_by_name->{ $status };
		}

		// Register the Success post status
		$args = array(
			'label'              => esc_html_x( 'Imported', 'event aggregator status', 'the-events-calendar' ),
			'label_count'        => _nx_noop( 'Imported <span class="count">(%s)</span>', 'Imported <span class="count">(%s)</span>', 'event aggregator status', 'the-events-calendar' ),
			'public'             => true,
			'publicly_queryable' => true,
		);
		$object = register_post_status( self::$status->success, $args );
		$registered_by_key->success = $registered_by_name->{'tribe-aggregator-success'} = $object;

		// Register the Failed post status
		$args = array(
			'label'              => esc_html_x( 'Failed', 'event aggregator status', 'the-events-calendar' ),
			'label_count'        => _nx_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'event aggregator status', 'the-events-calendar' ),
			'public'             => true,
			'publicly_queryable' => true,
		);
		$object = register_post_status( self::$status->failed, $args );
		$registered_by_key->failed = $registered_by_name->{'tribe-aggregator-failed'} = $object;

		// Register the Schedule post status
		$args = array(
			'label'              => esc_html_x( 'Schedule', 'event aggregator status', 'the-events-calendar' ),
			'label_count'        => _nx_noop( 'Schedule <span class="count">(%s)</span>', 'Schedule <span class="count">(%s)</span>', 'event aggregator status', 'the-events-calendar' ),
			'public'             => true,
			'publicly_queryable' => true,
		);
		$object = register_post_status( self::$status->schedule, $args );
		$registered_by_key->schedule = $registered_by_name->{'tribe-aggregator-schedule'} = $object;

		// Register the Pending post status
		$args = array(
			'label'              => esc_html_x( 'Pending', 'event aggregator status', 'the-events-calendar' ),
			'label_count'        => _nx_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'event aggregator status', 'the-events-calendar' ),
			'public'             => true,
			'publicly_queryable' => true,
		);
		$object = register_post_status( self::$status->pending, $args );
		$registered_by_key->pending = $registered_by_name->{'tribe-aggregator-pending'} = $object;

		// Register the Pending post status
		$args = array(
			'label'              => esc_html_x( 'Draft', 'event aggregator status', 'the-events-calendar' ),
			'label_count'        => _nx_noop( 'Draft <span class="count">(%s)</span>', 'Draft <span class="count">(%s)</span>', 'event aggregator status', 'the-events-calendar' ),
			'public'             => true,
			'publicly_queryable' => true,
		);
		$object = register_post_status( self::$status->draft, $args );
		$registered_by_key->draft = $registered_by_name->{'tribe-aggregator-draft'} = $object;

		// Check if we already have the Status registered
		if ( isset( $registered_by_key->{ $status } ) && is_object( $registered_by_key->{ $status } ) ) {
			return $registered_by_key->{ $status };
		}

		// Check if we already have the Status registered
		if ( isset( $registered_by_name->{ $status } ) && is_object( $registered_by_name->{ $status } ) ) {
			return $registered_by_name->{ $status };
		}

		return $registered_by_key;
	}

	public function count_by_origin( $type = array( 'schedule', 'manual' ), $raw_statuses = '' ) {
		global $wpdb;

		$where = array(
			'post_type = %s',
			'AND post_status NOT IN ( \'' . self::$status->draft . '\' )',
		);

		$statuses = array();

		// Make it an Array
		$raw_statuses = (array) $raw_statuses;
		foreach ( $raw_statuses as $status ) {
			if ( ! isset( self::$status->{ $status } ) ) {
				continue;
			}

			// Get the Actual Status for the Database
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

		// Prevents Warnings With `array_combine`
		if ( empty( $results ) ) {
			return array();
		}

		$origins = wp_list_pluck( $results, 'origin' );
		$counts = wp_list_pluck( $results, 'count' );

		// Remove ea/ from the `post_mime_type`
		foreach ( $origins as &$origin ) {
			$origin = str_replace( 'ea/', '', $origin );
		}

		return array_combine( $origins, $counts );
	}

	/**
	 * Returns an appropriate Record object for the given origin
	 *
	 * @param string $origin Import origin
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|null
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
			case 'facebook':
			case 'ea/facebook':
				$record = new Tribe__Events__Aggregator__Record__Facebook( $post );
				break;
			case 'meetup':
			case 'ea/meetup':
				$record = new Tribe__Events__Aggregator__Record__Meetup( $post );
				break;
		}

		return $record;
	}

	/**
	 * Returns an appropriate Record object for the given post id
	 *
	 * @param int $post_id WP Post ID of record
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|null
	 */
	public function get_by_post_id( $post ) {
		$post = get_post( $post );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( ! $post instanceof WP_Post ) {
			return tribe_error( 'core:aggregator:invalid-record-object', array(), array( $post ) );
		}

		if ( $post->post_type !== self::$post_type ) {
			return tribe_error( 'core:aggregator:invalid-record-post_type', array(), array( $post ) );
		}

		if ( empty( $post->post_mime_type ) ) {
			return tribe_error( 'core:aggregator:invalid-record-origin', array(), array( $post ) );
		}

		return $this->get_by_origin( $post->post_mime_type, $post );
	}

	/**
	 * Returns an appropriate Record object for the given import id
	 *
	 * @param int $import_id Aggregator import id
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|null
	 */
	public function get_by_import_id( $import_id ) {
		$args = array(
			'post_type' => self::$post_type,
			'meta_key' => $this->prefix_meta( 'import_id' ),
			'meta_value' => $import_id,
			'post_status' => array(
				self::$status->draft,
				self::$status->pending,
				self::$status->success,
			),
		);

		$query = new WP_Query( $args );

		if ( empty( $query->post ) ) {
			return tribe_error( 'core:aggregator:invalid-import-id', array(), array( $import_id ) );
		}

		$post = $query->post;
		if ( empty( $post->post_mime_type ) ) {
			return tribe_error( 'core:aggregator:invalid-record-origin', array(), array( $post ) );
		}

		return $this->get_by_origin( $post->post_mime_type, $post );

	}

	/**
	 * Returns an appropriate Record object for the given event id
	 *
	 * @param  int $event_id   Post ID for the Event
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|null
	 */
	public function get_by_event_id( $event_id ) {
		$event = get_post( $event_id );

		if ( ! $event instanceof WP_Post ) {
			return tribe_error( 'core:aggregator:invalid-event-id', array(), array( $event_id ) );
		}

		$record_id = get_post_meta( $event->ID, Tribe__Events__Aggregator__Event::$record_key, true );

		if ( empty( $record_id ) ) {
			return tribe_error( 'core:aggregator:invalid-import-id', array(), array( $record_id ) );
		}

		return $this->get_by_post_id( $record_id );

	}

	public function query( $args = array() ) {
		$statuses = Tribe__Events__Aggregator__Records::$status;
		$defaults = array(
			'post_status' => array( $statuses->success, $statuses->failed, $statuses->pending ),
			'orderby'     => 'modified',
			'order'       => 'DESC',
		);
		$args = (object) wp_parse_args( $args, $defaults );

		// Enforce the Post Type
		$args->post_type = self::$post_type;

		// Do the actual Query
		$query = new WP_Query( $args );

		return $query;
	}

	/**
	 * Returns whether or not there are any scheduled imports
	 *
	 * @return boolean
	 */
	public function has_scheduled() {
		static $has_scheduled = null;

		if ( null === $has_scheduled ) {
			$args = array(
				'fields' => 'ids',
				'post_status' => $this->get_status( 'schedule' )->name,
				'posts_per_page' => 1,
			);

			$scheduled = $this->query( $args );
			$has_scheduled = ! empty( $scheduled->posts );
		}

		return $has_scheduled;
	}

	/**
	 * Returns whether or not there have been any import requests
	 *
	 * @return boolean
	 */
	public function has_history() {
		static $has_history = null;

		if ( null === $has_history ) {
			$args = array(
				'fields' => 'ids',
				'posts_per_page' => 1,
			);

			$history = $this->query( $args );
			$has_history = ! empty( $history->posts );
		}

		return $has_history;
	}

	/**
	 * Filter the Admin page tile and add Tab Name
	 *
	 * @param  string $admin_title Full Admin Title
	 * @param  string $title       Original Title from the Page
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
	 * Fetches the current active tab
	 *
	 * @return object An instance of the Class used to create the Tab
	 */
	public function get_active() {
		/**
		 * Allow Developers to change the default tab
		 * @param string $slug
		 */
		$default = apply_filters( 'tribe_aggregator_default_tab', 'new' );

		$tab = ! empty( $_GET['tab'] ) && $this->exists( $_GET['tab'] ) ? $_GET['tab'] : $default;

		// Return the active tab or the default one
		return $this->get( $tab );
	}

	public function action_do_import() {
		 // First we convert the array to a json string
		$json = json_encode( $_POST );

		// The we convert the json string to a stdClass()
		$request = json_decode( $json );

		// Empty Required Variables
		if ( empty( $request->data->import_id ) || empty( $_GET['key'] ) ) {
			return wp_send_json_error();
		}

		$import_id = $request->data->import_id;
		$record = $this->get_by_import_id( $import_id );

		// We received an Invalid Import ID
		if ( is_wp_error( $record ) ) {
			return wp_send_json_error();
		}

		// Verify if Hash matches sent Key
		if ( ! isset( $record->meta['hash'] ) || $record->meta['hash'] !== $_GET['key'] ) {
			return wp_send_json_error();
		}

		// Actually import things
		$record->process_posts( $request );

		return wp_send_json_success();
	}

	public function filter_post_origin() {
		return Tribe__Events__Aggregator__Event::$event_origin;
	}

	/**
	 * Adds the import record and origin to the imported event
	 *
	 * @param int $id Event ID
	 * @param int $record_id Import Record ID
	 * @param string $origin Import Origin
	 */
	public function add_record_to_event( $id, $record_id, $origin ) {
		$record = $this->get_by_post_id( $record_id );

		// Set the event origin
		update_post_meta( $id, '_EventOrigin', Tribe__Events__Aggregator__Event::$event_origin );

		// Add the Aggregator origin
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$origin_key, $origin );

		// Add the Aggregator record
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$record_key, $record_id );

		// Add the Aggregator source
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$source_key, $record->meta['source'] );

		// Add the Aggregator import timestamp
		update_post_meta( $id, Tribe__Events__Aggregator__Event::$updated_key, $record->post->post_date );
	}

	/**
	 * Prefixes a String to be the Key for Record meta
	 *
	 * @since  4.3
	 *
	 * @param  string $str Append to the Prefix
	 *
	 * @return string
	 */
	public function prefix_meta( $str = null ) {
		return Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . $str;
	}

	/**
	 * Fetches the Amount of seconds that we will hold a Record Log on the Posts Table
	 *
	 * @since  4.3.2
	 *
	 * @return int
	 */
	public function get_retention() {
		return apply_filters( 'tribe_aggregator_record_retention', WEEK_IN_SECONDS );
	}
}
