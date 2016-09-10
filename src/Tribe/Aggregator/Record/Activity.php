<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Activity {
	/**
	 * Holds a Log of what has been done on This Queue
	 * @var array
	 */
	protected $items = array();

	/**
	 * Allows easier quick shortcodes to access activity
	 * @var array
	 */
	private $map = array();

	/**
	 * Creates an easy way to test valid Actions
	 * @var array
	 */
	private static $actions = array(
		'created' => array(),
		'updated' => array(),
		'skipped' => array(),
	);

	public function __construct() {
		// The items are registred on the wakeup to avoid saving uncessary data
		$this->__wakeup();
	}

	public function __wakeup() {
		// Entry for Events CPT
		$this->register( Tribe__Events__Main::POSTTYPE, array( 'event', 'events' ) );

		// Entry for Organizers CPT
		$this->register( Tribe__Events__Organizer::POSTTYPE, array( 'organizer', 'organizers' ) );

		// Entry for Venues CPT
		$this->register( Tribe__Events__Venue::POSTTYPE, array( 'venue', 'venues' ) );

		// Entry for Terms in Events Cat
		$this->register( Tribe__Events__Main::TAXONOMY, array( 'category', 'categories', 'cat', 'cats' ) );

		// Entry for Attachment
		$this->register( 'attachment', array( 'attachments', 'image', 'images' ) );
	}

	public function __sleep() {
		return array( 'items' );
	}

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

		return true;
	}

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
			// If it's a scalar and it's not one of the registred actions we skip it
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
			$this->items[ $slug ]->{ $action } = array_unique( array_filter( array_merge( $this->items[ $slug ]->{ $action }, (array) $ids ) ) );
		}

		return true;
	}

	public function merge( self $activity ) {
		$items = $activity->get();

		foreach ( $items as $slug => $data ) {
			$this->add( $slug, $data );
		}

		return $this;
	}

	/**
	 * Removes a tab from the queue items
	 *
	 * @param  string  $slug The Slug of the Tab
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
	 * Fetches the Instance of the Tab or all the tabs
	 *
	 * @param  string  $slug (optional) The Slug of the Tab
	 *
	 * @return null|array|object        If we couldn't find the tab it will be null, if the slug is null will return all tabs
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

	public function count( $slug, $action = null ) {
		$actions = $this->get( $slug );

		if ( empty( $actions ) ) {
			return 0;
		}

		// Sum all of the Actions
		if ( is_null( $action ) ) {
			return array_sum( array_map( 'count', (array) $actions ) );
		} elseif ( ! empty( $actions->{ $action } ) ) {
			return count( $actions->{ $action } );
		} else {
			return 0;
		}
	}

	/**
	 * Checks if a given Tab (slug) exits
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
		return empty( $this->items[ $slug ] ) ;
	}
}