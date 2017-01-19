<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Tabs extends Tribe__Tabbed_View  {


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
		if ( false == tribe_get_option( 'tribe_aggregator_disable', false ) ) {
			$this->register( 'Tribe__Events__Aggregator__Tabs__Scheduled' );
		}
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

		$slug = $this->get_requested_slug( $slug );

		// Fetch the Active Tab
		$tab = $this->get_active();

		// Compare
		return $slug === $tab->get_slug();
	}

	/**
	 * @return mixed|void
	 */
	public function get_default_tab() {
		/**
		 * Allow Developers to change the default tab
		 *
		 * @param string $slug
		 */
		$default = apply_filters( 'tribe_aggregator_default_tab', 'new' );

		return $default;
	}

	/**
	 * Returns the main admin settings URL.
	 *
	 * @param array|string $args     Query String or Array with the arguments
	 * @param boolean      $relative Return a relative URL or absolute
	 *
	 * @return string
	 */
	public function get_url( $args, $relative ) {
		return Tribe__Events__Aggregator__Page::instance()->get_url( $args, $relative );
	}
}
