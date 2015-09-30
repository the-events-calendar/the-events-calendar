<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Widgets
 *
 * @package Events Pro
 * @subpackage Customizer
 * @since 4.0
 */
final class Tribe__Events__Pro__Customizer__Section_Widget {

	/**
	 * Private variable holding the class Instance
	 *
	 * @since 4.0
	 *
	 * @access private
	 * @var Tribe__Events__Pro__Customizer__Section_Widget
	 */
	private static $instance;

	/**
	 * Method to return the Private instance of the Class
	 *
	 * @since 4.0
	 *
	 * @access public
	 * @return Tribe__Events__Pro__Customizer__Section_Widget
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
	public $ID = 'widget';

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
		add_action( 'tribe_events_customizer_register_' . $this->ID . '_settings', array( &$this, 'settings' ), 10, 2 );
		add_filter( 'tribe_events_customizer_pre_sections', array( &$this, 'register' ), 10, 2 );
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
			'priority'    => 70,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Widgets', 'tribe-events-calendar-pro' ),
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
			$customizer->get_setting_name( 'widget_color_scheme', $section ),
			array(
				'default' => 'light',
			)
		);

		$manager->add_control(
			$customizer->get_setting_name( 'widget_color_scheme', $section ),
			array(
				'type' => 'select',
				'label' => esc_html__( 'Widget Color Scheme', 'tribe-events-calendar-pro' ),
				'section' => $section->id,
				'choices' => array(
					'light' => 'Light',
					'dark' => 'Dark',
				),
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'calendar_header_color', $section ),
			array(
				'default'              => '#999',
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'calendar_header_color', $section ),
				array(
					'label'   => __( 'Calendar Header Color' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'calendar_datebar_color', $section ),
			array(
				'default'              => '#e0e0e0',
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'calendar_datebar_color', $section ),
				array(
					'label'   => __( 'Calendar Date Bar Color' ),
					'section' => $section->id,
				)
			)
		);
	}

}