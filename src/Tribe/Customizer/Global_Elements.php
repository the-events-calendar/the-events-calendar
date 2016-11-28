<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Global Elements
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since 4.4
 */
final class Tribe__Events__Customizer__Global_Elements extends Tribe__Customizer__Section {
	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 *
	 * Note: This is the only required method for a Connector to work
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance() {
		return tribe( 'tec.customizer.global-elements' );
	}

	/**
	 * Grab the CSS rules template
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Customizer::instance();

		if ( $customizer->has_option( $this->ID, 'link_color' ) ) {
			$template .= '
				#tribe-events-content a,
				.tribe-events-event-meta a {
					color: <%= global_elements.link_color %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'filterbar_color' ) ) {
			$template .= '
				#tribe-bar-form {
					background-color: <%= global_elements.filterbar_color %>;
				}

				#tribe-bar-views .tribe-bar-views-inner {
					background-color: <%= global_elements.filterbar_color_darker %>;
				}

				#tribe-bar-collapse-toggle {
					background-color: transparent;
				}

				#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a {
					background-color: <%= global_elements.filterbar_color_darker %>;
				}

				#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option.tribe-bar-active a:hover {
					background-color: transparent;
				}

				#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a:hover {
					background-color: <%= global_elements.filterbar_color %>;
				}

				#tribe-bar-form .tribe-bar-submit input[type=submit] {
					background-color: <%= global_elements.filterbar_color_darkest %>;
				}

				#tribe-bar-form input[type="text"] {
					border-bottom-color: <%= global_elements.filterbar_color_darkest %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'button_color' ) ) {
			$template .= '
				#tribe_events_filters_wrapper input[type=submit],
				.tribe-events-button,
				#tribe-events .tribe-events-button,
				.tribe-events-button.tribe-inactive,
				#tribe-events .tribe-events-button:hover,
				.tribe-events-button:hover,
				.tribe-events-button.tribe-active:hover {
					background-color: <%= global_elements.button_color %>;
				}
			';
		}

		return $template;
	}

	public function create_ghost_settings( $settings = array() ) {
		if ( ! empty( $settings['filterbar_color'] ) ) {
			$settings['filterbar_color_darker'] = new Tribe__Utils__Color( $settings['filterbar_color'] );
			$settings['filterbar_color_darker'] = '#' . $settings['filterbar_color_darker']->darken();

			$settings['filterbar_color_darkest'] = new Tribe__Utils__Color( $settings['filterbar_color'] );
			$settings['filterbar_color_darkest'] = '#' . $settings['filterbar_color_darkest']->darken( 30 );
		}

		return $settings;
	}

	public function setup() {
		$this->arguments = array(
			'priority'    => 20,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Global Elements', 'the-events-calendar' ),
			'description' => esc_html__( 'Options selected here will override what was selected in the "General Theme" section', 'the-events-calendar' ),
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
			$customizer->get_setting_name( 'link_color', $section ),
			array(
				'default'              => $this->get_default( 'link_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'link_color', $section ),
				array(
					'label'   => esc_html__( 'Link Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'filterbar_color', $section ),
			array(
				'default'              => $this->get_default( 'filterbar_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'filterbar_color', $section ),
				array(
					'label'   => esc_html__( 'Filter Bar Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'button_color', $section ),
			array(
				'default'              => $this->get_default( 'button_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'button_color', $section ),
				array(
					'label'   => esc_html__( 'Button Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'map_pin', $section ),
			array(
				'default'              => $this->get_default( 'map_pin' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'esc_url_raw',
			)
		);

		$manager->add_control(
			new WP_Customize_Image_Control(
				$manager,
				$customizer->get_setting_name( 'map_pin', $section ),
				array(
					'default'    => $this->get_default( 'button_color' ),
					'label'      => esc_html__( 'Map Pin', 'the-events-calendar' ),
					'section'    => $section->id,
				)
			)
		);

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'link_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'filterbar_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'button_color', $section ) );

		// To add Live Edit Pins will require some JS refactor to be able to work
		// $customizer->add_setting_name( $customizer->get_setting_name( 'map_pin', $section ) );

	}
}
