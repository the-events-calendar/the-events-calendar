<?php
/**
 * The Events Calendar Customizer Section Class
 * Events Bar
 *
 * @since 5.8.0
 */

namespace Tribe\Events\Views\V2\Customizer\Section;

/**
 * Month View
 *
 * @since 5.8.0
 */
class Events_Bar extends \Tribe__Customizer__Section {

	/**
	 * ID of the section.
	 * Namespaced to avoid collisions.
	 *
	 * @since 5.8.0
	 *
	 * @access public
	 * @var string
	 */
	public $ID = 'tec_events_bar';

	/**
	 * Allows section CSS to be loaded in order for overrides.
	 *
	 * @var integer
	 */
	public $queue_priority = 15;

	/**
	 * This method will be executed when the Class is Initialized.
	 *
	 * @since 5.8.0
	 */
	public function setup() {
		parent::setup();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_defaults() {
		return [
			'events_bar_background_color_choice'    => 'default',
			'events_bar_background_color'           => '#FFFFFF',
			'events_bar_border_color_choice'        => 'default',
			'events_bar_border_color'               => '#e4e4e4',
			'events_bar_icon_color_choice'          => 'default',
			'events_bar_icon_color'                 => '#5d5d5d',
			'events_bar_text_color'                 => '#141827',
			'find_events_button_color_choice'       => 'default',
			'find_events_button_color'              => '#334aff',
			'find_events_button_text_color'         => '#FFFFFF',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_arguments() {
		return [
			'priority'	=> 63,
			'capability'  => 'edit_theme_options',
			'title'	   => esc_html__( 'Events Bar', 'the-events-calendar' ),
			'description' => _x(
				'These settings control the search and options bar that appears above calendar views.',
				'Note about what these settings control.',
				'the-events-calendar'
			),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_settings() {
		return [
			'events_bar_background_color_choice'        => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'events_bar_background_color'               => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'events_bar_border_color_choice'        => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'events_bar_border_color'               => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'events_bar_icon_color_choice'          => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'events_bar_icon_color'                 => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'events_bar_text_color'                 => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'find_events_button_color_choice'       => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'find_events_button_color'              => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'find_events_button_text_color'         => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_headings() {
		return [
			'events_bar_font_colors' => [
				'priority'	 => 0,
				'type'		 => 'heading',
				'label'		=> esc_html_x(
					'Set Font Colors',
					'The header for the font color control section.',
					'the-events-calendar'
				),
			],
			'events_bar_view_separator-10' => [
				'priority'	 => 10,
				'type'		 => 'separator',
			],
			'events_bar_appearance' => [
				'priority'	 => 11, // Should come just after above separator.
				'type'		 => 'heading',
				'label'		=> esc_html_x(
					'Adjust Appearance',
					'The header for the color control section.',
					'the-events-calendar'
				),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_controls() {
		$customizer = tribe( 'customizer' );
		return [
			'events_bar_text_color'                 => [
				'priority'    => 3,
				'type'        => 'color',
				'label'       => esc_html_x(
					'Text Color',
					'The events bar text color setting label.',
					'the-events-calendar'
				),
			],
			'find_events_button_text_color'         => [
				'priority'    => 5,
				'type'        => 'color',
				'transport'   => 'postMessage',
				'label'       => esc_html_x(
					'Find Events Button Text',
					'The "Find Events" button text color setting label.',
					'the-events-calendar'
				),
			],
			'events_bar_icon_color_choice'          => [
				'priority'    => 15,
				'type'        => 'radio',
				'label'       => esc_html_x(
					'Icons',
					'The Events Bar icon color setting label.',
					'the-events-calendar'
				),
				'choices'     => [
					'default' => esc_html_x(
						'Default',
						'Label for the default option.',
						'the-events-calendar'
					),
					'accent'      => sprintf(
						/* translators: 1: Customizer url. */
						_x(
							'Use the <a href="%1$s">Accent Color</a>',
							'Label for option to use the accent color. Links to the accent color setting.',
							'the-events-calendar'
						),
						$customizer->get_setting_url(
							'global_elements',
							'accent_color'
						)
					),
					'custom'	  => esc_html_x(
						'Custom',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				],
			],
			'events_bar_icon_color'                 => [
				'priority'    => 16, // Immediately after events_bar_icon_color_choice.
				'type'        => 'color',
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'events_bar_icon_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return 'custom' === $value;
				},
			],
			'find_events_button_color_choice'       => [
				'priority'    => 20,
				'type'        => 'radio',
				'label'       => esc_html_x(
					'Find Events Button Color',
					'The "Find Events" button color setting label.',
					'the-events-calendar'
				),
				'choices'     => [
					'default' => sprintf(
						/* translators: 1: Customizer url. */
						_x(
							'Use the <a href="%1$s">Accent Color</a>',
							'Label for option to use the accent color (default). Links to the accent color setting.',
							'the-events-calendar'
						),
						$customizer->get_setting_url(
							'global_elements',
							'accent_color'
						)
					),
					'custom'	  => esc_html_x(
						'Custom',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				],
			],
			'find_events_button_color'              => [
				'priority'    => 21, // Immediately after find_events_button_color_choice.
				'type'        => 'color',
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'find_events_button_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return $this->defaults['find_events_button_color_choice'] !== $value;
				},
			],
			'events_bar_background_color_choice'    => [
				'priority'    => 25,
				'type'        => 'radio',
				'label'       => esc_html_x(
					'Background Color',
					'The Events Bar background color setting label.',
					'the-events-calendar'
				),
				'choices'     => [
					'default' => esc_html_x(
						'Default',
						'Label for the default option.',
						'the-events-calendar'
					),
					'global_background'      => sprintf(
						/* translators: 1: Customizer url. */
						_x(
							'Use the Calendar <a href="%1$s">Background Color</a>',
							'Label for option to use the events background color. Links to the background color setting.',
							'the-events-calendar'
						),
						$customizer->get_setting_url(
							'global_elements',
							'background_color_choice'
						)
					),
					'custom'	  => esc_html_x(
						'Custom',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				]
			],
			'events_bar_background_color'           => [
				'priority'    => 26, // Immediately after events_bar_background_color_choice.
				'type'        => 'color',
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'events_bar_background_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return 'custom' === $value;
				},
			],
			'events_bar_border_color_choice'        => [
				'priority'    => 30,
				'type'        => 'radio',
				'label'       => esc_html_x(
					'Border Color',
					'The Events Bar border color setting label.',
					'the-events-calendar'
				),
				'choices'     => [
					'default' => esc_html_x(
						'Default',
						'Label for the default option.',
						'the-events-calendar'
					),
					'custom'	  => esc_html_x(
						'Custom',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				]
			],
			'events_bar_border_color'               => [
				'priority'    => 31, // Immediately after events_bar_border_color_choice.
				'type'        => 'color',
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'events_bar_border_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return $this->defaults['events_bar_border_color_choice'] !== $value;
				},
			],
		];
	}

	/**
	 * Grab the CSS rules template
	 *
	 * @param string $css_template The Customizer CSS string/template.
	 *
	 * @return string The Customizer CSS string/template, with v2 Month View styles added.
	 */
	public function get_css_template( $css_template ) {
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return $css_template;
		}

		// These allow us to continue to _not_ target the shortcode.
		$apply_to_shortcode = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );
		$tribe_events = $apply_to_shortcode ? '.tribe-events' : '.tribe-events:not( .tribe-events-view--shortcode )';
		$tribe_common = $apply_to_shortcode ? '.tribe-common' : '.tribe-common:not( .tribe-events-view--shortcode )';

		if ( $this->should_include_setting_css( 'events_bar_text_color' ) ) {
			$text_color_obj     = new \Tribe__Utils__Color( $this->get_option( 'events_bar_text_color' ) );
			$text_color         = $text_color_obj->getRgb();
			$text_color_rgb     = $text_color['R'] . ',' . $text_color['G'] . ',' . $text_color['B'];
			$text_color_hover   = 'rgba(' . $text_color_rgb . ',0.12)';

			// Text color.
			$css_template .= "
				.tribe-common--breakpoint-medium{$tribe_common} .tribe-events-header__events-bar .tribe-common-form-control-text__input {
					color: <%= tec_events_bar.events_bar_text_color %>;
				}
			";

			$css_template .= "
				.tribe-common--breakpoint-medium{$tribe_common} .tribe-events-header__events-bar .tribe-common-form-control-text__input::placeholder {
					color: <%= tec_events_bar.events_bar_text_color %>;
					opacity: .6;
				}
			";

			$css_template .= "
				{$tribe_events} .tribe-events-c-view-selector__list-item-text {
					color: <%= tec_events_bar.events_bar_text_color %>;
				}
			";

			$css_template .= "
				{$tribe_events} .tribe-events-c-view-selector__list-item:hover,
				{$tribe_events} .tribe-events-c-view-selector__list-item:focus {
					background-color: $text_color_hover;
				}


				{$tribe_events} .tribe-events-c-view-selector__list-item:focus-within,
				{$tribe_events} .tribe-events-c-view-selector__list-item-link:hover,
				{$tribe_events} .tribe-events-c-view-selector__list-item-link:focus {
					background-color: transparent;
				}
			";
		}

		if ( $this->should_include_setting_css( 'find_events_button_text_color' ) ) {
			$button_color_obj    = new \Tribe__Utils__Color( $this->get_option( 'find_events_button_text_color' ) );
			$button_color        = $button_color_obj->getRgb();
			$button_color_rgb    = $button_color['R'] . ',' . $button_color['G'] . ',' . $button_color['B'];
			$button_color_hover  = 'rgba(' . $button_color_rgb . ',0.5)';
			$button_color_active = 'rgba(' . $button_color_rgb . ',0.6)';

			$css_template .= "
				{$tribe_common} .tribe-events-c-search__button {
					color: rgb({$button_color_rgb});
				}

				{$tribe_common} .tribe-events-c-search__button:active {
					color: {$button_color_active};
				}

				{$tribe_common} .tribe-events-c-search__button:hover,
				{$tribe_common} .tribe-events-c-search__button:focus {
					color: {$button_color_hover};
				}

				.tribe-theme-twentytwentyone {$tribe_common} .tribe-common-c-btn.tribe-events-c-search__button:not(:hover):not(:active) {
					color: rgb({$button_color_rgb});
				}

				.tribe-theme-twentytwentyone {$tribe_common} .tribe-common-c-btn.tribe-events-c-search__button:active {
					color: {$button_color_active};
				}

				.tribe-theme-twentytwentyone {$tribe_common} .tribe-common-c-btn.tribe-events-c-search__button:hover,
				.tribe-theme-twentytwentyone {$tribe_common} .tribe-common-c-btn.tribe-events-c-search__button:focus {
					color: {$button_color_hover};
				}
			";
		}

		if ( $this->should_include_setting_css( 'events_bar_icon_color_choice' ) ) {
			if ( 'custom' === $this->get_option( 'events_bar_icon_color_choice' ) ) {
				$icon_color_obj = new \Tribe__Utils__Color( $this->get_option( 'events_bar_icon_color' ) );
			} elseif (
				'accent' === $this->get_option( 'events_bar_icon_color_choice' )
				&& $this->should_include_setting_css( 'accent_color', 'global_elements' )
			) {
				$option = tribe( 'customizer' )->get_option( [ 'global_elements', 'accent_color' ], false );
				$icon_color_obj = new \Tribe__Utils__Color( $option );
			}

			if ( ! empty( $icon_color_obj ) ) {
				$icon_color_arr     = $icon_color_obj->getRgb();
				$icon_color_hex     = '#' . $icon_color_obj->getHex();
				$icon_color_rgb     = $icon_color_arr['R'] . ',' . $icon_color_arr['G'] . ',' . $icon_color_arr['B'];
				$icon_color_focus   = 'rgba(' . $icon_color_rgb . ',0.75)';


				$css_template .= "
					{$tribe_events} .tribe-events-c-search__input-control-icon-svg path,
					{$tribe_events} .tribe-events-c-events-bar__search-button-icon-svg path,
					{$tribe_events} .tribe-events-c-view-selector__button-icon-svg path,
					{$tribe_events} .tribe-events-c-view-selector__list-item-icon-svg:not(.tribe-common-c-svgicon__svg-stroke) path {
						fill: {$icon_color_focus};
					}

					{$tribe_events} .tribe-events-c-search__input-control--keyword .tribe-events-c-search__input:focus ~ .tribe-events-c-search__input-control-icon-svg path,
					{$tribe_events} .tribe-events-c-search__input-control--location .tribe-events-c-search__input:focus ~ .tribe-events-c-search__input-control-icon-svg path {
						fill: {$icon_color_hex};
					}

					{$tribe_events} .tribe-events-c-events-bar .tribe-events-c-view-selector__button::before {
						background-color: {$icon_color_hex};
					}

					{$tribe_events} .tribe-events-c-events-bar .tribe-events-c-view-selector__list-item-icon-svg.tribe-common-c-svgicon__svg-stroke path {
						stroke: {$icon_color_hex};
					}
				";
			}
		}

		if ( $this->should_include_setting_css( 'find_events_button_color_choice' ) ) {
			$button_text_color_obj    = new \Tribe__Utils__Color( $this->get_option( 'find_events_button_color' ) );
			$button_text_color        = $button_text_color_obj->getRgb();
			$button_text_color_rgb    = $button_text_color['R'] . ',' . $button_text_color['G'] . ',' . $button_text_color['B'];
			$button_text_color_hover  = 'rgba(' . $button_text_color_rgb . ',0.8)';
			$button_text_color_active = 'rgba(' . $button_text_color_rgb . ',0.9)';

			$css_template .= "
				{$tribe_common} .tribe-events-c-search__button {
					background-color: <%= tec_events_bar.find_events_button_color %>;
				}

				{$tribe_common} .tribe-events-c-search__button:active {
					background-color: {$button_text_color_active};
				}

				{$tribe_common} .tribe-events-c-search__button:focus,
				{$tribe_common} .tribe-events-c-search__button:hover {
					background-color: {$button_text_color_hover};
				}

				.tribe-theme-twentytwenty {$tribe_common}:not(.tribe-events-view--shortcode) .tribe-common-c-btn.tribe-events-c-search__button {
					background-color: <%= tec_events_bar.find_events_button_color %>;
				}

				.tribe-theme-twentytwenty {$tribe_common}:not(.tribe-events-view--shortcode) .tribe-common-c-btn.tribe-events-c-search__button:active {
					background-color: {$button_text_color_active};
				}

				.tribe-theme-twentytwenty {$tribe_common}:not(.tribe-events-view--shortcode) .tribe-common-c-btn.tribe-events-c-search__button:hover,
				.tribe-theme-twentytwenty {$tribe_common}:not(.tribe-events-view--shortcode) .tribe-common-c-btn.tribe-events-c-search__button:focus {
					background-color: {$button_text_color_hover};
				}


				.tribe-theme-twentytwentyone {$tribe_common} .tribe-common-c-btn.tribe-events-c-search__button:not(:hover):not(:active) {
					background-color: <%= tec_events_bar.find_events_button_color %>;
				}

				.tribe-theme-twentytwentyone {$tribe_common} .tribe-common-c-btn.tribe-events-c-search__button:not(:hover):active {
					background-color: {$button_text_color_active};
				}

				.tribe-theme-twentytwentyone {$tribe_common} .tribe-common-c-btn.tribe-events-c-search__button:focus,
				.tribe-theme-twentytwentyone {$tribe_common} .tribe-common-c-btn.tribe-events-c-search__button:hover {
					background-color: {$button_text_color_hover};
				}
			";
		}

		if ( $this->should_include_setting_css( 'events_bar_border_color_choice' ) ) {
			$css_template .= "
				.tribe-common--breakpoint-medium{$tribe_events} .tribe-events-header .tribe-events-header__events-bar,
				.tribe-common--breakpoint-medium{$tribe_events} .tribe-events-c-search__input-control {
					border-color: <%= tec_events_bar.events_bar_border_color %>;
				}
			";
		}

		if ( $this->should_include_setting_css( 'events_bar_background_color_choice' ) ) {
			if ( 'custom' == $this->get_option( 'events_bar_background_color_choice' ) ) {
				$background_color = "tec_events_bar.events_bar_background_color";
			} elseif (
				'global_background' == $this->get_option( 'events_bar_background_color_choice' )
				&& $this->should_include_setting_css( 'background_color_choice', 'global_elements' )
			) {
				$background_color = "global_elements.background_color";
			}

			if ( ! empty( $background_color ) ) {
				$css_template .= "
					.tribe-common--breakpoint-medium{$tribe_events} .tribe-events-header__events-bar,
					.tribe-common--breakpoint-medium{$tribe_events} .tribe-events-header__events-bar .tribe-common-form-control-text__input,
					.tribe-common--breakpoint-medium{$tribe_events} .tribe-events-header__events-bar .tribe-events-c-events-bar__search-container {
						background-color: <%= {$background_color} %>;
					}

					{$tribe_events} .tribe-events-c-view-selector__content {
						background-color: <%= {$background_color} %>;
					}
				";
			}
		}

		return $css_template;
	}
}
