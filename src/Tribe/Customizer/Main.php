<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Main class
 *
 * @package Events Pro
 * @subpackage Customizer
 * @since 4.0
 */
final class Tribe__Events__Pro__Customizer__Main {

	/**
	 * Private variable holding the class Instance
	 *
	 * @since 4.0
	 *
	 * @access private
	 * @var Tribe__Events__Pro__Customizer__Main
	 */
	private static $instance;

	/**
	 * Method to return the Private instance of the Class
	 *
	 * @since 4.0
	 *
	 * @access public
	 * @return Tribe__Events__Pro__Customizer__Main
	 */
	public static function instance() {
		// This also prevents double instancing the class
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * WP_Customize_Manager instance.
	 *
	 * @since 4.0
	 * @access public
	 * @var WP_Customize_Manager
	 */
	public $manager;

	/**
	 * Instance of Customize Panel
	 *
	 * @since 4.0
	 * @access public
	 * @var WP_Customize_Panel
	 */
	public $panel;

	/**
	 * Array of Sections of our Panel
	 *
	 * @since 4.0
	 * @access private
	 * @var array
	 */
	private $sections;

	/**
	 * Loads the Basic Settings for the Class to work
	 *
	 * @since  4.0
	 *
	 * @see  self::instance()
	 * @access private
	 *
	 * @return void
	 */
	private function __construct() {
		// Hook the Registering methods
		add_action( 'customize_register', array( &$this, 'register' ), 15 );

		// Initialize the Sections
		Tribe__Events__Pro__Customizer__Section_Day_List_View::instance();
		Tribe__Events__Pro__Customizer__Section_Month_Week_View::instance();
		Tribe__Events__Pro__Customizer__Section_Photo_View::instance();
		Tribe__Events__Pro__Customizer__Section_General_Theme::instance();
		Tribe__Events__Pro__Customizer__Section_Global_Elements::instance();
		Tribe__Events__Pro__Customizer__Section_Single_Event::instance();
		Tribe__Events__Pro__Customizer__Section_Widget::instance();
	}

	/**
	 * Method to start setting up the Customizer Section and Fields
	 *
	 * @since  4.0
	 *
	 * @param  WP_Customize_Manager $customizer WordPress Customizer variable
	 * @return void
	 */
	public function register( WP_Customize_Manager $customizer ) {
		// Set the Cutomizer on a class variable
		$this->manager = $customizer;

		// Register Panel
		$this->panel = apply_filters( 'tribe_events_customizer_panel', $this->register_panel(), $this );

		/**
		 * Filter the Sections within our Panel before they are added to the Cutomize Manager
		 *
		 * @since 4.0
		 *
		 * @var array
		 */
		$this->sections = apply_filters( 'tribe_events_customizer_pre_sections', $this->sections, $this );

		foreach ( $this->sections as $id => $section ) {
			$this->sections[ $id ] = $this->register_section( $id, $section );

			/**
			 * Allows people to Register and de-register the method to register more Fields
			 *
			 * @since 4.0
			 */
			do_action( 'tribe_events_customizer_register_' . $id . '_settings', $this->sections[ $id ], $this->manager );
		}

		/**
		 * Filter the Sections within our Panel, now using the actual WP_Customize_Section
		 *
		 * @since 4.0
		 *
		 * @var array
		 */
		$this->sections = apply_filters( 'tribe_events_customizer_sections', $this->sections, $this );
	}

	/**
	 * Register the base Panel for Events Calendar Sections to be attached to
	 *
	 * @since 4.0
	 *
	 * @return WP_Customize_Panel
	 */
	private function register_panel() {
		$panel_id = apply_filters( 'tribe_events_customizer_panel_id', 'tribe_events_customizer', $this );
		$panel = $this->manager->get_panel( $panel_id );

		// If the Panel already exists we leave returning it's instance
		if ( ! empty( $panel ) ){
			return $panel;
		}

		$panel_args = apply_filters( 'tribe_events_customizer_panel_args', array(
			'title' => esc_html__( 'The Events Calendar', 'tribe-events-calendar-pro' ),
			'description' => esc_html__( 'Use the following panel of your customizer to change the styling of your Calendar and Event pages.', 'tribe-events-calendar-pro' ),

			// After `static_front_page`
			'priority' => 125,
		), $panel_id, $this );

		// Actually Register the Panel
		$this->manager->add_panel( $panel_id, $panel_args );

		// Return the Panel instance
		return $this->manager->get_panel( $panel_id );
	}

	/**
	 * Use a "alias" method to register sections to allow users to filter args and the ID
	 *
	 * @since 4.0
	 *
	 * @param  string $id   The Unique section ID
	 * @param  array $args  Arguments to register the section
	 *
	 * @link https://codex.wordpress.org/Class_Reference/WP_Customize_Manager/add_section
	 *
	 * @return WP_Customize_Section
	 */
	public function register_section( $id, $args ) {
		$section_id = apply_filters( 'tribe_events_customizer_section_id', $id, $this );
		$section = $this->manager->get_section( $section_id );

		// If the Panel already exists we leave returning it's instance
		if ( ! empty( $section ) ){
			return $section;
		}

		/**
		 * Filter the Section arguments, so that developers can filter arguments based on $section_id
		 * @var array
		 */
		$section_args = apply_filters( 'tribe_events_customizer_section_args', $args, $section_id, $this );

		// Don't allow sections outside of our panel
		$section_args['panel'] = $this->panel->id;

		// Actually Register the Section
		$this->manager->add_section( $section_id, $section_args );

		// Return the Section instance
		return $this->manager->get_section( $section_id );
	}

	/**
	 * Build the Setting name using the HTML format for Arrays
	 *
	 * @since  4.0
	 *
	 * @param  string $slug    The actual Setting name
	 * @param  string|WP_Customize_Section $section [description]
	 *
	 * @return string          HTML name Attribute name o the setting
	 */
	public function get_setting_name( $slug, $section = null ) {
		$name = $this->panel->id;

		// If there is a section set append it
		if ( $section instanceof WP_Customize_Section ){
			$name .= '[' . $section->id . ']';
		} elseif ( is_string( $section ) ){
			$name .= '[' . $section . ']';
		}

		// Set the actual setting slug
		$name .= '[' . esc_attr( $slug ) . ']';

		return $name;
	}

}
