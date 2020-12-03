<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Activity {
	/**
	 * The below constants are meant to be used to set a status on the activity.
	 * The reasons, check and management of said status are up to the client
	 * object and not managed by the activity instance.
	 *
	 * @see Tribe__Events__Aggregator__Record__Activity::set_last_status()
	 * @see Tribe__Events__Aggregator__Record__Activity::get_last_status()
	 *
	 */
	const STATUS_SUCCESS = 'success';
	const STATUS_FAIL = 'fail';
	const STATUS_PARTIAL = 'partial';
	const STATUS_NULL = 'null';

	/**
	 * Holds a Log of what has been done on This Queue
	 * @var array
	 */
	protected $items = [];

	/**
	 * The status of the last processing operation.
	 *
	 * @var string
	 */
	protected $last_status;

	/**
	 * Allows easier quick shortcodes to access activity
	 * @var array
	 */
	private $map = [];

	public $total = 0;

	/**
	 * Creates an easy way to test valid Actions
	 * @var array
	 */
	private static $actions = [
		'created'   => [],
		'updated'   => [],
		'skipped'   => [],
		'scheduled' => [],
	];

	public function __construct() {
		// The items are registered on the wakeup to avoid saving unnecessary data
		$this->__wakeup();
	}

	/**
	 * Register the Activities Tracked
	 */
	public function __wakeup() {
		// Entry for Events CPT
		$this->register( Tribe__Events__Main::POSTTYPE, array( 'event', 'events' ) );

		// Entry for Organizers CPT
		$this->register( Tribe__Events__Organizer::POSTTYPE, array( 'organizer', 'organizers' ) );

		// Entry for Venues CPT
		$this->register( Tribe__Events__Venue::POSTTYPE, array( 'venue', 'venues' ) );

		// Entry for Terms in Events Cat
		$this->register( Tribe__Events__Main::TAXONOMY, array( 'category', 'categories', 'cat', 'cats' ) );

		// Entry for Tags
		$this->register( 'post_tag', array( 'tag', 'tags' ) );

		// Entry for Attachment
		$this->register( 'attachment', array( 'attachments', 'image', 'images' ) );

		/**
		 * Fires during record activity wakeup to allow other plugins to inject/register activity entries
		 * for other custom post types
		 *
		 * @param Tribe__Events__Aggregator__Record__Activity $this
		 */
		do_action( 'tribe_aggregator_record_activity_wakeup', $this );
	}

	/**
	 * Prevents Mapping to be saved on the DB object
	 * @return array
	 */
	public function __sleep() {
		return array( 'items', 'last_status' );
	}

	/**
	 * Register a Specific Activity and it's mappings
	 *
	 * @param  string $slug Name of this Activity
	 * @param  array  $map  (optional) Other names in which you can access this activity
	 *
	 * @return boolean       [description]
	 */
	public function register( $slug, $map = array() ) {
		if ( empty( $this->items[ $slug ] ) ) {
			// Clone the Default action values
			$this->items[ $slug ] = (object) self::$actions;
		} else {
			$this->items[ $slug ] = (object) array_merge( (array) self::$actions, (array) $this->items[ $slug ] );
		}

		// Add the base mapping
		$this->map[ $slug ] = $slug;

		// Allow short names for the activities
		foreach ( $map as $to ) {
			$this->map[ $to ] = $slug;
		}

		$this->prevent_duplicates_between_item_actions( $slug );

		return true;
	}

	/**
	 * Logs an Activity
	 *
	 * @param string       $slug Name of this Activity
	 * @param string|array $items Type of activity
	 * @param array        $ids   items inside of the action
	 *
	 * @return boolean
	 */
	public function add( $slug, $items, $ids = array() ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		if ( ! isset( $this->map[ $slug ] ) ) {
			return false;
		}

		// Map the Slug
		$slug = $this->map[ $slug ];

		if ( is_scalar( $items ) ) {
			// If it's a scalar and it's not one of the registered actions we skip it
			if ( ! isset( self::$actions[ $items ] ) ) {
				return false;
			}

			// Make the actual Array of items
			$items = array( $items => $ids );
		} else {
			$items = (object) $items;

			// Doesn't contain any of the Possible Actions
			if ( 0 === count( array_intersect_key( self::$actions, (array) $items ) ) ) {
				return false;
			}
		}

		foreach ( $items as $action => $ids ) {
			// Skip Empty ids
			if ( empty( $ids ) ) {
				continue;
			}

			$this->items[ $slug ]->{ $action } = array_unique( array_filter( array_merge( $this->items[ $slug ]->{ $action }, (array) $ids ) ) );
		}

		return true;
	}

	/**
	 * Returns the merged version of two Activities classes
	 *
	 * @param  self   $activity Which activity should be merged here
	 *
	 * @return self
	 */
	public function merge( self $activity ) {
		$items = $activity->get();

		foreach ( $items as $slug => $data ) {
			$this->add( $slug, $data );
		}

		return $this;
	}

	/**
	 * Removes a activity from the Registered ones
	 *
	 * @param  string  $slug   The Slug of the Activity
	 *
	 * @return boolean
	 */
	public function remove( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		if ( ! isset( $this->map[ $slug ] ) ) {
			return false;
		}

		// Map the Slug
		$slug = $this->map[ $slug ];

		// Remove it
		unset( $this->items[ $slug ] );
		return true;
	}

	/**
	 * Fetches a registered Activity
	 *
	 * @param  string  $slug   (optional) The Slug of the Activity
	 * @param  string  $action (optional) Which action
	 *
	 * @return null|array|object
	 */
	public function get( $slug = null, $action = null ) {
		if ( is_null( $slug ) ) {
			return $this->items;
		}

		if ( ! isset( $this->map[ $slug ] ) ) {
			return null;
		}

		// Map the Slug
		$slug = $this->map[ $slug ];

		// Check if it actually exists
		if ( empty( $this->items[ $slug ] ) ) {
			return null;
		}

		$actions = $this->items[ $slug ];

		// If we trying to get a specific action and
		if ( is_null( $action ) ) {
			return $this->items[ $slug ];
		} elseif ( ! empty( $actions->{ $action } ) ) {
			return $actions->{ $action };
		} else {
			return null;
		}
	}

	/**
	 * Fetches a registered Activity counter
	 *
	 * @param  string  $slug   (optional) The Slug of the Activity
	 * @param  string  $action (optional) Which action
	 *
	 * @return int
	 */
	public function count( $slug = null, $action = null ) {
		$actions = $this->get( $slug );

		if ( empty( $actions ) ) {
			return 0;
		}

		// Sum all of the Actions
		if ( is_null( $action ) ) {
			// recursively convert to associative array
			$actions = json_decode( json_encode( $actions ), true );

			return array_sum( array_map( 'count', $actions ) );
		} elseif ( ! empty( $actions->{ $action } ) ) {
			return count( $actions->{ $action } );
		}

		return 0;
	}

	/**
	 * Checks if a given Activity type exists
	 *
	 * @param  string  $slug The Slug of the Tab
	 *
	 * @return boolean
	 */
	public function exists( $slug ) {
		if ( is_null( $slug ) ) {
			return false;
		}

		if ( ! isset( $this->map[ $slug ] ) ) {
			return false;
		}

		// Map the Slug
		$slug = $this->map[ $slug ];

		// Check if it actually exists
		return ! empty( $this->items[ $slug ] ) ;
	}

	/**
	 * Checks the activities for a slug to make sure there are no incoherent duplicate entries due to concurring processes.
	 *
	 * @since 4.5.12
	 *
	 * @param string $slug
	 */
	protected function prevent_duplicates_between_item_actions( $slug ) {
		// sanity check the updated elements: elements cannot be created AND updated
		if ( ! empty( $this->items[ $slug ]->updated ) && ! empty( $this->items[ $slug ]->created ) ) {
			$this->items[ $slug ]->updated = array_diff( $this->items[ $slug ]->updated, $this->items[ $slug ]->created );
		}
	}

	/**
	 * Returns the raw items from the activity.
	 *
	 * @since 4.6.15
	 *
	 * @return array
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Sets the last status on the activity object.
	 *
	 * Ideally set to one of the `STATUS_` constants defined by the class
	 * but allowing arbitrary stati by design. It's up to the client to set
	 * and consume this information.
	 *
	 * @since 4.6.15
	 *
	 * @param string $status
	 */
	public function set_last_status( $status ) {
		$this->last_status = $status;
	}

	/**
	 * Gets the last status on the activity object.
	 *
	 * Ideally set to one of the `STATUS_` constants defined by the class
	 * but allowing arbitrary stati by design. It's up to the client to set
	 * and consume this information.
	 *
	 * @since 4.6.15
	 *
	 * @return string
	 */
	public function get_last_status() {
		return $this->last_status;
	}
}
