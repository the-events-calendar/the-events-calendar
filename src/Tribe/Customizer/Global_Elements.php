<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Tribe\Customizer\Controls\Heading;
use Tribe\Customizer\Controls\Separator;
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
		// Sanity check.
		if ( tribe_events_views_v2_is_enabled() ) {
			return $template;
		}

		$customizer = tribe( 'customizer' );

		/**
		 * Allows filtering the CSS template with full knowledge of the Global Elements section and the current Customizer instance.
		 *
		 * @since 5.3.1
		 *
		 * @param string                     $template   The CSS template, as produced by the Global Elements.
		 * @param Tribe__Customizer__Section $this       The Global Elements section.
		 * @param Tribe__Customizer          $customizer The current Customizer instance.
		 */
		$template = apply_filters( 'tribe_customizer_global_elements_css_template', $template, $this, $customizer );

		if ( tribe_events_views_v2_is_enabled() ) {
			return $template;
		}

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

	public function create_ghost_settings( $settings = [] ) {
		if ( ! empty( $settings['filterbar_color'] ) ) {
			$settings['filterbar_color_darker'] = new Tribe__Utils__Color( $settings['filterbar_color'] );
			$settings['filterbar_color_darker'] = '#' . $settings['filterbar_color_darker']->darken();

			$settings['filterbar_color_darkest'] = new Tribe__Utils__Color( $settings['filterbar_color'] );
			$settings['filterbar_color_darkest'] = '#' . $settings['filterbar_color_darkest']->darken( 30 );
		}

		return $settings;
	}

	public function setup() {
		$views_v2_is_enabled = tribe_events_views_v2_is_enabled();
		$title               = $views_v2_is_enabled ? esc_html__( 'General', 'the-events-calendar' ) : esc_html__( 'Global Elements', 'the-events-calendar' );
		$description         = $views_v2_is_enabled ? '' : esc_html__( 'Options selected here will override what was selected in the "General Theme" section.', 'the-events-calendar' );

		$this->defaults = [
			'link_color'              => '#141827',
			'event_title_color'       => '#141827',
			'event_date_time_color'   => '#141827',
			'background_color_choice' => 'transparent',
		];

		$this->arguments = [
			'priority'    => 20,
			'capability'  => 'edit_theme_options',
			'title'       => $title,
			'description' => $description,
		];
	}

	/**
	 * Create the Fields/Settings for this sections
	 *
	 * @param  WP_Customize_Section $section The WordPress section instance
	 * @param  WP_Customize_Manager $manager WP_Customize_Manager instance.
	 *
	 * @return void
	 */
	public function register_settings( WP_Customize_Section $section, WP_Customize_Manager $manager ) {
		$customizer = tribe( 'customizer' );

		// Add an heading that is a Control only in name: it does not, actually, control or save any setting.
		$manager->add_control(
			new Heading(
				$manager,
				$customizer->get_setting_name( 'font_color_heading', $section ),
				[
					'label'    => esc_html__( 'Set Font Colors', 'the-events-calendar' ),
					'section'  => $section->id,
					'priority' => 0,
				]
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'link_color', $section ),
			[
				'default'              => $this->get_default( 'link_color' ),
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'link_color', $section ),
				[
					'label'       => esc_html__( 'Links', 'the-events-calendar' ),
					'description' => esc_html__( 'For displayed URLs', 'the-events-calendar' ),
					'section'     => $section->id,
					'priority'    => 8,
				]
			)
		);

		$customizer->add_setting_name( $customizer->get_setting_name( 'link_color', $section ) );

		$manager->add_control(
			new Separator(
				$manager,
				$customizer->get_setting_name( 'adjust_appearance_separator', $section ),
				[
					'label'    => esc_html__( 'Adjust Appearance', 'the-events-calendar' ),
					'section'  => $section->id,
					'priority' => 9,
				]
			)
		);

		// Add an heading that is a Control only in name: it does not, actually, control or save any setting.
		$manager->add_control(
			new Heading(
				$manager,
				$customizer->get_setting_name( 'adjust_appearance_heading', $section ),
				[
					'label'    => esc_html__( 'Adjust Appearance', 'the-events-calendar' ),
					'section'  => $section->id,
					'priority' => 10,
				]
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'accent_color', $section ),
				[
					'label'   => esc_html__( 'Accent Color', 'the-events-calendar' ),
					'section' => $section->id,
					'priority' => 15,
				]
			)
		);

		$customizer->add_setting_name( $customizer->get_setting_name( 'accent_color', $section ) );

		// Custom Map Pins are not supported with basic embeds.
		if ( ! tribe_is_using_basic_gmaps_api() ) {

			$manager->add_setting(
				$customizer->get_setting_name( 'map_pin', $section ),
				[
					'default'           => $this->get_default( 'map_pin' ),
					'type'              => 'option',
					'sanitize_callback' => 'esc_url_raw',
				]
			);

			$manager->add_control(
				new WP_Customize_Image_Control(
					$manager,
					$customizer->get_setting_name( 'map_pin', $section ),
					[
						'default' => $this->get_default( 'button_color' ),
						'label'   => esc_html__( 'Map Pin', 'the-events-calendar' ),
						'section' => $section->id,
						'priority' => 20,
					]
				)
			);
		}

		// Old stuff for backwards compatibility.
		if ( tribe_events_views_v2_is_enabled() ) {
			return;
		}

		$manager->add_setting(
			$customizer->get_setting_name( 'filterbar_color', $section ),
			[
				'default' => $this->get_default( 'filterbar_color' ),
				'type'    => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'filterbar_color', $section ),
				[
					'label'    => esc_html__( 'Filter Bar Color', 'the-events-calendar' ),
					'section'  => $section->id,
					'priority' => 20,
				]
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'button_color', $section ),
			[
				'default' => $this->get_default( 'button_color' ),
				'type'    => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'button_color', $section ),
				[
					'label'   => esc_html__( 'Button Color', 'the-events-calendar' ),
					'section' => $section->id,
					'priority' => 20,
				]
			)
		);

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'filterbar_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'button_color', $section ) );
	}
}
