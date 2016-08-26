<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Tabs {

	/**
	 * A list of all the tabs
	 * @var array
	 */
	private $items = array();

	/**
	 * Static Singleton Holder
	 *
	 * @var self|null
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
	 * A private method to prevent it to be created twice.
	 * It will add the methods and setup any dependencies
	 */
	private function __construct() {
		add_filter( 'admin_title', array( $this, 'filter_admin_title' ), 10, 2 );

		// Configure the Base Tabs
		$this->register( 'Tribe__Events__Aggregator__Tabs__New' );
		$this->register( 'Tribe__Events__Aggregator__Tabs__Scheduled' );
		$this->register( 'Tribe__Events__Aggregator__Tabs__History' );

		if ( ! empty( $_GET['id'] ) || Tribe__Main::instance()->doing_ajax() ) {
			$this->register( 'Tribe__Events__Aggregator__Tabs__Edit' );
		}
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

	/**
	 * Register a new tab on the Aggregator page
	 *
	 * @param  string|object   $tab  A list of
	 * @return object|boolean        The instance of the tab or false if we couldn't register
	 */
	public function register( $tab ) {
		// If Obj is a string, check if it's existing class, then get an instance of it
		if ( is_string( $tab ) && class_exists( $tab ) && method_exists( $tab, 'instance' ) ) {
			$tab = call_user_func_array( array( $tab, 'instance' ), array() );
		}

		// Makes sure that the tab you are registering is extended from the Abstract
		if ( ! is_object( $tab ) || ! in_array( 'Tribe__Events__Aggregator__Tabs__Abstract', class_parents( $tab ) ) ) {
			return false;
		}

		// Set the Tab Item on the array of Tabs
		$this->items[ $tab->get_slug() ] = $tab;

		// Return the tab
		return $tab;
	}

	/**
	 * Checks if a given Tab (slug) is active
	 *
	 * @param  string  $slug The Slug of the Tab
	 *
	 * @return boolean       Is this tab active?
	 */
	public function is_active( $slug = null ) {
		if ( ! Tribe__Events__Aggregator__Page::instance()->is_screen() ) {
			return false;
		}

		/**
		 * Allow Developers to change the default tab
		 * @param string $slug
		 */
		$default = apply_filters( 'tribe_aggregator_default_tab', 'new' );

		if ( is_null( $slug ) ) {
			// Set the slug
			$slug = ! empty( $_GET['tab'] ) && $this->exists( $_GET['tab'] ) ? $_GET['tab'] : $default;
		}

		// Fetch the Active Tab
		$tab = $this->get_active();

		// Compare
		return $slug === $tab->get_slug();
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
	public function get( $slug = null ) {
		// Sort Tabs by priority
		uasort( $this->items, array( $this, '_sort_by_priority' ) );

		if ( is_null( $slug ) ) {
			return $this->items;
		}

		// Prevent weird stuff here
		$slug = sanitize_title_with_dashes( $slug );

		if ( ! empty( $this->items[ $slug ] ) ) {
			return $this->items[ $slug ];
		}

		return null;
	}

	/**
	 * Checks if a given Tab (slug) exits
	 *
	 * @param  string  $slug The Slug of the Tab
	 *
	 * @return boolean
	 */
	public function exists( $slug ) {
		return is_object( $this->get( $slug ) ) ? true : false;
	}

	/**
	 * A method to sort tabs by priority
	 *
	 * @access private
	 *
	 * @param  object  $a First tab to compare
	 * @param  object  $b Second tab to compare
	 *
	 * @return int
	 */
	public function _sort_by_priority( $a, $b ) {
		if ( $a->priority == $b->priority ) {
			return 0;
		}

		return ( $a->priority < $b->priority ) ? -1 : 1;
	}
}
