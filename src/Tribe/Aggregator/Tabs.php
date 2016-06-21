<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Aggregator__Tabs {
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
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * A private method to prevent it to be created twice.
	 * It will add the methods and setup any dependecies
	 */
	private function __construct() {
		add_filter( 'admin_title', array( $this, 'filter_admin_title' ), 10, 2 );
		add_action( 'current_screen', array( $this, 'action_active_tab' ) );

		// Configure the Base Tabs
		$this->register( 'Tribe__Events__Aggregator__Tabs__New' );
		$this->register( 'Tribe__Events__Aggregator__Tabs__Scheduled' );
		$this->register( 'Tribe__Events__Aggregator__Tabs__Past' );
		$this->register( 'Tribe__Events__Aggregator__Tabs__Favorite' );

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

	public function action_active_tab( $screen ) {


	}

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

	private $items = array();

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

	public function is_active( $slug ) {
		if ( ! Tribe__Events__Aggregator__Page::instance()->is_screen() ) {
			return false;
		}

		// Fetch the Active Tab
		$tab = $this->get_active( $slug );

		// Compare
		return $slug === $tab->get_slug();
	}

	public function remove( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		unset( $this->items[ $slug ] );
		return true;
	}

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

	public function exists( $slug ) {
		return is_object( $this->get( $slug ) ) ? true : false;
	}


	public function _sort_by_priority( $a, $b ) {
		if ( $a->priority == $b->priority ) {
			return 0;
		}

		return ( $a->priority < $b->priority ) ? -1 : 1;
	}
}