<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Global Elements
 *
 * @package Events Pro
 * @subpackage Customizer
 * @since 4.0
 */
final class Tribe__Events__Pro__Customizer__Section_Global_Elements extends Tribe__Events__Pro__Customizer__Section {
	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 *
	 * Note: This is the only required method for a Connector to work
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance( $name = null ) {
		return parent::instance( __CLASS__ );
	}

	/**
	 * Grab the CSS rules template
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Events__Pro__Customizer__Main::instance();

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
			$settings['filterbar_color_darker'] = new Tribe__Events__Pro__Customizer__Color( $settings['filterbar_color'] );
			$settings['filterbar_color_darker'] = '#' . $settings['filterbar_color_darker']->darken();

			$settings['filterbar_color_darkest'] = new Tribe__Events__Pro__Customizer__Color( $settings['filterbar_color'] );
			$settings['filterbar_color_darkest'] = '#' . $settings['filterbar_color_darkest']->darken( 30 );
		}

		return $settings;
	}

	public function setup() {
		$this->defaults = array(
			'link_color' => '#21759b',
			'filterbar_color' => '#f5f5f5',
			'button_color' => '#21759b',
		);

		$this->arguments = array(
			'priority'    => 20,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Global Elements', 'tribe-events-calendar-pro' ),
			'description' => esc_html__( 'Options selected here will override what was selected in the "General Theme" section', 'tribe-events-calendar-pro' ),
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
		$customizer = Tribe__Events__Pro__Customizer__Main::instance();

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
					'label'   => esc_html__( 'Link Color', 'tribe-events-calendar-pro' ),
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
					'label'   => esc_html__( 'Filter Bar Color', 'tribe-events-calendar-pro' ),
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
					'label'   => esc_html__( 'Button Color', 'tribe-events-calendar-pro' ),
					'section' => $section->id,
				)
			)
		);

	}
}