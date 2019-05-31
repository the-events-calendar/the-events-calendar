<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Single Event
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since 4.4
 */
final class Tribe__Events__Customizer__Single_Event extends Tribe__Customizer__Section {
	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 *
	 * Note: This is the only required method for a Connector to work
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance() {
		return tribe( 'tec.customizer.single-event' );
	}

	/**
	 * Add the CSS rules template to the `tribe_events_pro_customizer_css_template`
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Customizer::instance();

		if ( $customizer->has_option( $this->ID, 'details_bg_color' ) ) {
			$template .= '
				.single-tribe_events .tribe-events-event-meta {
					background-color: <%= single_event.details_bg_color %>;
					color: <%= single_event.details_text_color %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'post_title_color' ) ) {
			$template .= '
				.tribe-events-single-event-title {
					color: <%= single_event.post_title_color %>;
				}
			';
		}

		return $template;
	}

	public function create_ghost_settings( $settings = array() ) {
		if ( ! empty( $settings['details_bg_color'] ) ) {
			$details_bg_color = new Tribe__Utils__Color( $settings['details_bg_color'] );

			if ( $details_bg_color->isDark() ) {
				$settings['details_text_color'] = '#f9f9f9';
			} else {
				$settings['details_text_color'] = '#333333';
			}
		}

		return $settings;
	}

	public function setup() {
		$this->defaults = array(
			'post_title_color' => '#333',
			'details_bg_color' => '#e5e5e5',
		);

		$this->arguments = array(
			'priority'    => 60,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Single Event', 'the-events-calendar' ),
			'description' => esc_html__( 'Options selected here will override what was selected in the "General Theme" and "Global Elements" sections', 'the-events-calendar' ),
		);
	}

	/**
	 * Create the Fields/Settings for this sections
	 *
	 * @param  WP_Customize_Section $section The WordPress section instance
	 * @param  WP_Customize_Manager $manager [description]
	 *
	 * @return void
	 */
	public function register_settings( WP_Customize_Section $section, WP_Customize_Manager $manager ) {
		$customizer = Tribe__Customizer::instance();

		$manager->add_setting(
			$customizer->get_setting_name( 'post_title_color', $section ),
			array(
				'default'              => $this->get_default( 'post_title_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'post_title_color', $section ),
				array(
					'label'   => esc_html__( 'Post Title Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'details_bg_color', $section ),
			array(
				'default'              => $this->get_default( 'details_bg_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'details_bg_color', $section ),
				array(
					'label'   => esc_html__( 'Details Background Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'post_title_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'details_bg_color', $section ) );
	}
}
