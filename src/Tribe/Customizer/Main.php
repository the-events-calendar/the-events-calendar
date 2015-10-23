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
	 * The Panel ID
	 *
	 * @since 4.0
	 * @access public
	 * @var string
	 */
	public $ID;

	/**
	 * Array of Sections of our Panel
	 *
	 * @since 4.0
	 * @access private
	 * @var array
	 */
	private $sections;

	/**
	 * Array of Sections Classes, for non-panel pages
	 *
	 * @since 4.0
	 * @access private
	 * @var array
	 */
	private $sections_class = array();

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
		// The Panel ID
		$this->ID = apply_filters( 'tribe_events_pro_customizer_panel_id', 'tribe_events_pro_customizer', $this );

		// Initialize the Sections
		$this->sections_class[] = Tribe__Events__Pro__Customizer__Section_Day_List_View::instance();
		$this->sections_class[] = Tribe__Events__Pro__Customizer__Section_Month_Week_View::instance();
		$this->sections_class[] = Tribe__Events__Pro__Customizer__Section_Photo_View::instance();
		$this->sections_class[] = Tribe__Events__Pro__Customizer__Section_General_Theme::instance();
		$this->sections_class[] = Tribe__Events__Pro__Customizer__Section_Global_Elements::instance();
		$this->sections_class[] = Tribe__Events__Pro__Customizer__Section_Single_Event::instance();

		$this->sections_class = apply_filters( 'tribe_events_pro_customizer_sections_class', $this->sections_class, $this );

		// Hook the Registering methods
		add_action( 'customize_register', array( $this, 'register' ), 15 );

		add_action( 'wp_print_footer_scripts', array( $this, 'print_css_template' ), 15 );

	}

	/**
	 * A method to easily search on an array
	 *
	 * @param  array $variable  Varaible to be searched
	 * @param  array  $indexes  The index that the method will try to retrieve
	 * @param  mixed $default   If the variable doesn't exist, what is the default
	 *
	 * @return mixed            Return the variable based on the index
	 */
	public static function search_var( $variable = null, $indexes = array(), $default = null ) {
		if ( is_object( $variable ) ) {
			$variable = (array) $variable;
		}

		if ( ! is_array( $variable ) ) {
			return $variable;
		}

		foreach ( (array) $indexes as $index ) {
			if ( ! is_array( $variable ) || ! isset( $variable[ $index ] ) ) {
				$variable = $default;
				break;
			}

			$variable = $variable[ $index ];
		}

		return $variable;
	}

	/**
	 * Get an option from the database, using index search you can retrieve the full panel, a section or even a setting
	 *
	 * @param  array $search   Index search, array( 'section_name', 'setting_name' )
	 * @param  mixed $default  The default, if the requested variable doesn't exits
	 * @return mixed           The requested option or the default
	 */
	public function get_option( $search = null, $default = null ) {
		$sections = get_option( $this->ID, $default );
		foreach ( $this->sections_class as $section ) {
			/**
			 * Allow filtering the defaults for each settings to be filtered before the Ghost options to be set
			 *
			 * @var array
			 */
			$defaults[ $section->ID ] = apply_filters( 'tribe_events_pro_customizer_section_' . $section->ID . '_defaults', array() );
			$settings = isset( $sections[ $section->ID ] ) ? $sections[ $section->ID ] : array();
			$sections[ $section->ID ] = wp_parse_args( $settings, $defaults[ $section->ID ] );
		}

		/**
		 * Allows Ghost Options to be inserted
		 * @var array
		 * @var array
		 */
		$sections = apply_filters( 'tribe_events_pro_customizer_pre_get_option', $sections, $search );

		// Search on the Array
		if ( ! is_null( $search ) ) {
			$option = self::search_var( $sections, $search, $default );
		} else {
			$option = $sections;
		}

		/**
		 * Apply Filters After finding the variable
		 * @var mixed
		 * @var array
		 * @var array
		 */
		$option = apply_filters( 'tribe_events_pro_customizer_get_option', $option, $search, $sections );

		return $option;
	}

	/**
	 * Check if the option exists, this method is used allow only sections that were saved to be applied.
	 *
	 * @param strings Using the following structure: self::has_option( 'section_name', 'setting_name' );
	 *
	 * @return boolean Wheter the option exists in the database
	 */
	public function has_option() {
		$search = func_get_args();
		$option = self::get_option();
		$real_option = get_option( $this->ID, array() );

		// Get section and Settign based on keys
		$section = reset( $search );
		$setting = end( $search );

		if ( empty( $real_option ) || empty( $real_option[ $section ] ) ) {
			return false;
		}

		// Search on the Array
		if ( ! is_null( $search ) ) {
			$option = self::search_var( $option, $search, null );
		}

		return ! empty( $option );
	}

	/**
	 * Print the CSS for the customizer on `wp_print_footer_scripts`
	 *
	 * @return void
	 */
	public function print_css_template() {
		/**
		 * Use this filter to add more CSS, using Underscore Template style
		 *
		 * @link  http://underscorejs.org/#template
		 *
		 * @var string
		 */
		$css_template = apply_filters( 'tribe_events_pro_customizer_css_template', '' );

		// All sections should use this action to print their template
		echo '<script type="text/css" id="tmpl-tribe_events_pro_customizer_css">';
		echo $css_template;
		echo '</script>';

		// Place where the template will be rendered to
		echo '<style type="text/css" id="tribe_events_pro_customizer_css">';
		echo $this->parse_css_template( $css_template );
		echo '</style>';
	}

	/**
	 * Replaces the Settings using the Underscore templating strings
	 *
	 * @param  string $template The template variable, that we will look to replace the variables
	 * @return string           A Valid css after replacing the variables
	 */
	private function parse_css_template( $template ) {
		$css = $template;
		$sections = $this->get_option();

		$search = array();
		$replace = array();
		foreach ( $sections as $section => $settings ) {
			foreach ( $settings as $setting => $value ) {
				$index = array( $section, $setting );

				// Add search based on Underscore template
				$search[] = '<%= ' . implode( '.', $index ) . ' %>';

				// Get the Replace value
				$replace[] = $value;
			}
		}

		// Finally Str replace
		return str_replace( $search, $replace, $css );
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

		/**
		 * Allow users to filter the Panel
		 *
		 * @var WP_Customize_Panel
		 * @var Tribe__Events__Pro__Customizer__Main
		 */
		$this->panel = apply_filters( 'tribe_events_pro_customizer_panel', $this->register_panel(), $this );

		/**
		 * Filter the Sections within our Panel before they are added to the Cutomize Manager
		 *
		 * @since 4.0
		 *
		 * @var array
		 * @var Tribe__Events__Pro__Customizer__Main
		 */
		$this->sections = apply_filters( 'tribe_events_pro_customizer_pre_sections', $this->sections, $this );

		foreach ( $this->sections as $id => $section ) {
			$this->sections[ $id ] = $this->register_section( $id, $section );

			/**
			 * Allows people to Register and de-register the method to register more Fields
			 *
			 * @since 4.0
			 *
			 * @var array
			 * @var WP_Customize_Manager
			 */
			do_action( 'tribe_events_pro_customizer_register_' . $id . '_settings', $this->sections[ $id ], $this->manager );
		}

		/**
		 * Filter the Sections within our Panel, now using the actual WP_Customize_Section
		 *
		 * @since 4.0
		 *
		 * @var array
		 * @var Tribe__Events__Pro__Customizer__Main
		 */
		$this->sections = apply_filters( 'tribe_events_pro_customizer_sections', $this->sections, $this );
	}

	/**
	 * Register the base Panel for Events Calendar Sections to be attached to
	 *
	 * @since 4.0
	 *
	 * @return WP_Customize_Panel
	 */
	private function register_panel() {
		$panel = $this->manager->get_panel( $this->ID );

		// If the Panel already exists we leave returning it's instance
		if ( ! empty( $panel ) ) {
			return $panel;
		}

		$panel_args = apply_filters( 'tribe_events_pro_customizer_panel_args', array(
			'title' => esc_html__( 'The Events Calendar', 'tribe-events-calendar-pro' ),
			'description' => esc_html__( 'Use the following panel of your customizer to change the styling of your Calendar and Event pages.', 'tribe-events-calendar-pro' ),

			// After `static_front_page`
			'priority' => 125,
		), $this->ID, $this );

		// Actually Register the Panel
		$this->manager->add_panel( $this->ID, $panel_args );

		// Return the Panel instance
		return $this->manager->get_panel( $this->ID );
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
		$section_id = apply_filters( 'tribe_events_pro_customizer_section_id', $id, $this );
		$section = $this->manager->get_section( $section_id );

		// If the Panel already exists we leave returning it's instance
		if ( ! empty( $section ) ) {
			return $section;
		}

		/**
		 * Filter the Section arguments, so that developers can filter arguments based on $section_id
		 * @var array
		 */
		$section_args = apply_filters( 'tribe_events_pro_customizer_section_args', $args, $section_id, $this );

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
		if ( $section instanceof WP_Customize_Section ) {
			$name .= '[' . $section->id . ']';
		} elseif ( is_string( $section ) ) {
			$name .= '[' . $section . ']';
		}

		// Set the actual setting slug
		$name .= '[' . esc_attr( $slug ) . ']';

		return $name;
	}

}
