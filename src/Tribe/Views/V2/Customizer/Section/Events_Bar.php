<?php
/**
 * The Events Calendar Customizer Section Class
 * Events Bar
 *
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Customizer\Section;

/**
 * Month View
 *
 * @since TBD
 */
class Events_Bar extends \Tribe__Customizer__Section {

	/**
	 * ID of the section.
	 * Namespaced to avoid collisions.
	 *
	 * @since TBD
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
	 * @since TBD
	 */
	public function setup() {
		parent::setup();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_defaults() {
		return [
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
			'priority'	=> 10,
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
					return $this->defaults['events_bar_icon_color_choice'] !== $value;
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
						'Default.',
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
	 * @param string $template The Customizer CSS string/template.
	 *
	 * @return string The Customizer CSS string/template, with v2 Month View styles added.
	 */
	public function get_css_template( $template ) {
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return $template;
		}

		if ( $this->should_include_setting_css( 'events_bar_text_color' ) ) {
			// Text color.
			$template .= "
				.tribe-common--breakpoint-medium.tribe-common .tribe-events-header__events-bar .tribe-common-form-control-text__input {
					color: <%= tec_events_bar.events_bar_text_color %>;
			}";

			$template .= "
				.tribe-common--breakpoint-medium.tribe-common .tribe-events-header__events-bar .tribe-common-form-control-text__input::placeholder {
					color: <%= tec_events_bar.events_bar_text_color %>;
					opacity: .6;
				}
			";
		}

		if ( $this->should_include_setting_css( 'find_events_button_text_color' ) ) {
			$template .= "
				#submit-bar-button {
					color: <%= tec_events_bar.find_events_button_text_color %>;
				}
			";
		}

		if ( $this->should_include_setting_css( 'events_bar_icon_color_choice' ) ) {
			if ( 'custom' === $this->get_option( 'events_bar_icon_color_choice' ) ) {
				$icon_color = 'tec_events_bar.events_bar_icon_color';
			} elseif (
				'accent' === $this->get_option( 'events_bar_icon_color_choice' )
				&& $this->should_include_setting_css( 'accent_color', 'global_elements' )
			) {
				$icon_color = 'global_elements.accent_color';
			}

			$template .= "
				.tribe-events .tribe-events-c-search__input-control-icon-svg path,
				.tribe-events .tribe-events-c-events-bar__search-button-icon-svg path,
				.tribe-events .tribe-events-c-view-selector__button-icon-svg path,
				.tribe-events .tribe-events-c-view-selector__list-item-icon-svg path {
					fill: <%= {$icon_color} %>
				}
			";
		}

		if ( $this->should_include_setting_css( 'find_events_button_color_choice' ) ) {
			$button_color_obj    = new \Tribe__Utils__Color( $this->get_option( 'find_events_button_color' ) );
			$button_color        = $button_color_obj->getRgb();
			$button_color_rgb    = $button_color['R'] . ',' . $button_color['G'] . ',' . $button_color['B'];
			$button_color_hover  = 'rgba(' . $button_color_rgb . ',0.8)';
			$button_color_active = 'rgba(' . $button_color_rgb . ',0.9)';

			$template .= "
				#submit-bar-button {
					background-color: <%= tec_events_bar.find_events_button_color %>;
				}

				#submit-bar-button:active {
					background-color: {$button_color_active};
				}

				#submit-bar-button:focus,
				#submit-bar-button:hover {
					background-color: {$button_color_hover};
				}
			";
		}

		if ( $this->should_include_setting_css( 'events_bar_border_color_choice' ) ) {
			$template .= "
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-header--has-event-search .tribe-events-header__events-bar,
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-c-search__input-control {
					border-color: <%= tec_events_bar.events_bar_border_color %>;
				}
			";
		}

		return $template;
	}
}
