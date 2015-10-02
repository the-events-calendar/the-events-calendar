<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Day List View
 *
 * @package Events Pro
 * @subpackage Customizer
 * @since 4.0
 */
final class Tribe__Events__Pro__Customizer__Section_Day_List_View {

	/**
	 * Private variable holding the class Instance
	 *
	 * @since 4.0
	 *
	 * @access private
	 * @var Tribe__Events__Pro__Customizer__Section_Day_List_View
	 */
	private static $instance;

	/**
	 * Method to return the Private instance of the Class
	 *
	 * @since 4.0
	 *
	 * @access public
	 * @return Tribe__Events__Pro__Customizer__Section_Day_List_View
	 */
	public static function instance() {
		// This also prevents double instancing the class
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * ID of the section
	 *
	 * @since 4.0
	 *
	 * @access public
	 * @var string
	 */
	public $ID = 'day_list_view';

	/**
	 * Loads the Hooks
	 *
	 * @since  4.0
	 *
	 * @see  self::instance()
	 * @access private
	 *
	 * @return void
	 */
	private function __construct() {
		// Hook the Register methods
		add_action( 'tribe_events_pro_customizer_register_' . $this->ID . '_settings', array( $this, 'settings' ), 10, 2 );
		add_filter( 'tribe_events_pro_customizer_pre_sections', array( $this, 'register' ), 10, 2 );

		// Append this section CSS template
		add_filter( 'tribe_events_pro_customizer_css_template', array( $this, 'get_css_template' ), 10 );
		add_filter( 'tribe_events_pro_customizer_section_' . $this->ID . '_defaults', array( $this, 'get_defaults' ), 10 );

		// Create the Ghost Options
		add_filter( 'tribe_events_pro_customizer_pre_get_option', array( $this, 'filter_settings' ), 10, 2 );
	}

	/**
	 * Grab the CSS rules template
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Events__Pro__Customizer__Main::instance();

		if ( $customizer->has_option( $this->ID, 'price_bg_color' ) ) {
			$template .= '
				.tribe-events-list .tribe-events-event-cost span {
					background-color: <%= day_list_view.price_bg_color %>;
					border-color: <%= day_list_view.price_border_color %>;
					color: <%= day_list_view.price_color %>;
				}
			';
		}

		return $template;
	}

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

	public function create_ghost_settings( $settings = array() ) {

		if ( ! empty( $settings['price_bg_color'] ) ) {
			$price_bg_color = new Tribe__Events__Pro__Customizer__Color( $settings['price_bg_color'] );

			$settings['price_border_color'] = '#' . $price_bg_color->darken( 15 );
			if ( $price_bg_color->isDark() ) {
				$settings['price_color'] = '#f9f9f9';
			} else {
				$settings['price_color'] = '#333333';
			}
		}

		return $settings;
	}

	/**
	 * A way to apply filters when getting the Customizer options
	 * @return array
	 */
	public function get_defaults() {
		$defaults = array(
			'price_bg_color' => '#eeeeee',
		);

		// Create Ghost Options
		$defaults = $this->create_ghost_settings( $defaults );

		return $defaults;
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
	 * Register this Section
	 *
	 * @param  array  $sections   Array of Sections
	 * @param  Tribe__Events__Pro__Customizer__Main $customizer Our internal Cutomizer Class Instance
	 *
	 * @return array  Return the modified version of the Section array
	 */
	public function register( $sections, $customizer ) {
		$sections[ $this->ID ] = array(
			'priority'    => 40,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Day/List View', 'tribe-events-calendar-pro' ),
			'description' => esc_html__( 'Options selected here will override what was selected in the "General Theme" and "Global Elements" sections', 'tribe-events-calendar-pro' ),
		);

		return $sections;
	}


	/**
	 * Create the Fields/Settings for this sections
	 *
	 * @param  WP_Customize_Section $section The WordPress section instance
	 * @param  WP_Customize_Manager $manager [description]
	 *
	 * @return void
	 */
	public function settings( WP_Customize_Section $section, WP_Customize_Manager $manager ) {
		$customizer = Tribe__Events__Pro__Customizer__Main::instance();

		$manager->add_setting(
			$customizer->get_setting_name( 'price_bg_color', $section ),
			array(
				'default'              => $this->get_default( 'price_bg_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'price_bg_color', $section ),
				array(
					'label'   => esc_html__( 'Price Background Color', 'tribe-events-calendar-pro' ),
					'section' => $section->id,
				)
			)
		);

	}


}