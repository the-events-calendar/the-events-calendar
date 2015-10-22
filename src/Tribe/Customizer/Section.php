<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Abstract
 * Extend this when you are trying to create a new The Events Calendar Section
 * on the Customize from WordPress
 *
 * @package Events Pro
 * @subpackage Customizer
 * @since 4.0
 */
abstract class Tribe__Events__Pro__Customizer__Section {

	/**
	 * ID of the section
	 *
	 * @since 4.0
	 *
	 * @access public
	 * @var string
	 */
	public $ID;

	/**
	 * Default values for the settings on this class
	 *
	 * @since 4.0
	 *
	 * @access private
	 * @var array
	 */
	public $defaults = array();

	/**
	 * Information to setup the Section
	 *
	 * @since 4.0
	 *
	 * @access public
	 * @var array
	 */
	public $arguments = array(
		'priority'    => 10,
		'capability'  => 'edit_theme_options',
		'title'       => null,
		'description' => null,
	);

	/**
	 * Overwrite this method to create the Fields/Settings for this section
	 *
	 * @param  WP_Customize_Section $section The WordPress section instance
	 * @param  WP_Customize_Manager $manager The WordPress Customizer Manager
	 *
	 * @return void
	 */
	public function register_settings( WP_Customize_Section $section, WP_Customize_Manager $manager ) {
		$customizer = Tribe__Events__Pro__Customizer__Main::instance();
	}

	/**
	 * Overwrite this method to be able to implement the CSS template related to this section
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Events__Pro__Customizer__Main::instance();

		return $template;
	}

	/**
	 * Overwrite this method to be able to creaty dynamic settings
	 *
	 * @param  array  $settings The actual options on the database
	 * @return array
	 */
	public function create_ghost_settings( $settings = array() ) {
		return $settings;
	}

	/**
	 * This method will be executed when the Class in Initialized
	 * Overwrite this method to be able to setup the arguments of your section
	 *
	 * @return void
	 */
	abstract public function setup();

	/**
	 * Private variable holding the class Instance
	 *
	 * @since 4.0
	 *
	 * @access private
	 * @var Tribe__Events__Pro__Customizer__Section
	 */
	private static $instances;

	/**
	 * Method to return the Private instance of the Class
	 *
	 * @since 4.0
	 *
	 * @access public
	 * @return Tribe__Events__Pro__Customizer__Section
	 */
	public static function instance( $name ) {
		$slug = self::get_section_slug( $name );

		if ( ! isset( self::$instances[ $slug ] ) ) {
			self::$instances[ $slug ] = new $name;
		}

		return self::$instances[ $slug ];
	}

	/**
	 * Get the section slug based on the Class name
	 *
	 * @param  string $class_name The name of this Class
	 * @return the slug for this class
	 */
	final public static function get_section_slug( $class_name ) {
		$abstract_name = __CLASS__;
		$reflection = new ReflectionClass( $class_name );

		// Get the Slug without the Base name
		$slug = str_replace( $abstract_name . '_', '', $reflection->getName() );

		return strtolower( $slug );
	}

	/**
	 * Setup and Load hooks for this Section
	 *
	 * @since  4.0
	 *
	 * @see  self::instance()
	 * @access private
	 *
	 * @return void
	 */
	final private function __construct() {
		$slug = self::get_section_slug( get_class( $this ) );

		// Don't create a new one
		if ( isset( self::$instances[ $slug ] ) ){
			return;
		}

		if ( ! is_string( $this->ID ) ){
			$this->ID = $slug;
		}

		// Allow child classes to setup the
		$this->setup();

		// Hook the Register methods
		add_action( 'tribe_events_pro_customizer_register_' . $this->ID . '_settings', array( $this, 'register_settings' ), 10, 2 );
		add_filter( 'tribe_events_pro_customizer_pre_sections', array( $this, 'register' ), 10, 2 );

		// Append this section CSS template
		add_filter( 'tribe_events_pro_customizer_css_template', array( $this, 'get_css_template' ), 15 );
		add_filter( 'tribe_events_pro_customizer_section_' . $this->ID . '_defaults', array( $this, 'get_defaults' ), 10 );

		// Create the Ghost Options
		add_filter( 'tribe_events_pro_customizer_pre_get_option', array( $this, 'filter_settings' ), 10, 2 );
	}

	/**
	 * A way to apply filters when getting the Customizer options
	 * @return array
	 */
	public function get_defaults() {
		// Create Ghost Options
		return $this->create_ghost_settings( $this->defaults );
	}

	/**
	 * Get the Default Value requested
	 * @return mixed
	 */
	public function get_default( $key ) {
		$defaults = $this->get_defaults();

		if ( ! isset( $defaults[ $key ] ) ) {
			return null;
		}

		return $defaults[ $key ];
	}

	/**
	 * Hooks to the `tribe_events_pro_customizer_pre_get_option`, this applies
	 * the `$this->create_ghost_settings()` method to the settings on the correct section
	 *
	 * @param  array $settings  Values from the Database from Customizer actions
	 * @param  array $search    Indexed search @see Tribe__Events__Pro__Customizer__Main::search_var()
	 *
	 * @return array
	 */
	public function filter_settings( $settings, $search ) {
		// Only Apply if getting the full options or Section
		if ( is_array( $search ) && count( $search ) > 1 ) {
			return $settings;
		}

		if ( count( $search ) === 1 ) {
			$settings = $this->create_ghost_settings( $settings );
		} else {
			$settings[ $this->ID ] = $this->create_ghost_settings( $settings[ $this->ID ] );
		}

		return $settings;
	}

	/**
	 * Register this Section
	 *
	 * @param  array  $sections   Array of Sections
	 * @param  Tribe__Events__Pro__Customizer__Main $customizer Our internal Cutomizer Class Instance
	 *
	 * @return array  Return the modified version of the Section array
	 */
	public function register( $sections, Tribe__Events__Pro__Customizer__Main $customizer ) {
		$sections[ $this->ID ] = $this->arguments;

		return $sections;
	}
}