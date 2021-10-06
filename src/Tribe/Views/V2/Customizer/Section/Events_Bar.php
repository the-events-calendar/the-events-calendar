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
final class Events_Bar extends \Tribe__Customizer__Section {

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
			'events_bar_background_color_choice'        => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
				'transport'            => 'postMessage',
			],
			'events_bar_background_color'               => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'transport'            => 'postMessage',
			],
			'events_bar_border_color_choice'        => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
				'transport'            => 'postMessage',
			],
			'events_bar_border_color'               => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'transport'            => 'postMessage',
			],
			'events_bar_icon_color_choice'          => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
				'transport'            => 'postMessage',
			],
			'events_bar_icon_color'                 => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'transport'            => 'postMessage',
			],
			'events_bar_text_color'                 => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'transport'            => 'postMessage',
			],
			'find_events_button_color_choice'       => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
				'transport'            => 'postMessage',
			],
			'find_events_button_color'              => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'transport'            => 'postMessage',
			],
			'find_events_button_text_color'         => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'transport'            => 'postMessage',
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

		$new_styles   = [];

		// It's all custom props now, baby...
		if ( $this->should_include_setting_css( 'events_bar_text_color' ) ) {
			$text_color = $this->get_option( 'events_bar_text_color' );
			// Text color.

			if ( ! empty( $text_color ) ) {
				$new_styles[] = "--tec-color-text-events-bar-input: {$text_color};";
				$new_styles[] = "--tec-color-text-events-bar-input-placeholder: {$text_color};";
				$new_styles[] = "--tec-opacity-events-bar-input-placeholder: 0.6;";
				$new_styles[] = "--tec-color-text-view-selector-list-item: {$text_color};";
				$new_styles[] = "--tec-color-text-view-selector-list-item-hover: {$text_color};";
			}

			// Hover background follows text color
			$text_color_rgb = $this->get_rgb_color( 'events_bar_text_color' );
			if ( ! empty( $text_color_rgb ) ) {
				$new_styles[]   = "--tec-color-background-view-selector-list-item-hover: rgba({$text_color_rgb}, 0.12);";
			}
		}

		if ( $this->should_include_setting_css( 'find_events_button_text_color' ) ) {
			$button_text_color     = $this->get_option( 'find_events_button_text_color' );
			$button_text_color_rgb = $this->get_rgb_color( 'find_events_button_text_color' );
		}

		if ( ! empty( $button_text_color ) ) {
			$new_styles[] = "--tec-color-text-events-bar-submit-button: {$button_text_color};";
		}
		if ( ! empty( $button_text_color_rgb ) ) {
			$new_styles[] = "--tec-color-text-events-bar-submit-button-active: rgba({$button_text_color_rgb}, 0.5);";
			$new_styles[] = "--tec-color-text-events-bar-submit-button-hover: rgba({$button_text_color_rgb}, 0.6);";
		}

		if ( $this->should_include_setting_css( 'events_bar_icon_color_choice' ) ) {
			if ( 'custom' === $this->get_option( 'events_bar_icon_color_choice' ) ) {
				$icon_color = $this->get_option( 'events_bar_icon_color' );
			} elseif (
				'accent' === $this->get_option( 'events_bar_icon_color_choice' )
				&& $this->should_include_setting_css( 'accent_color', 'global_elements' )
			) {
				$icon_color = tribe( 'customizer' )->get_option( [ 'global_elements', 'accent_color' ] );
			}

			if ( ! empty( $icon_color ) ) {
				$new_styles[] = "--tec-color-icon-events-bar: {$icon_color};";
				$new_styles[] = "--tec-color-icon-events-bar-hover: {$icon_color};";
				$new_styles[] = "--tec-color-icon-events-bar-active: {$icon_color};";
			}
		}

		if ( $this->should_include_setting_css( 'find_events_button_color_choice' ) ) {
			$button_color     = $this->get_option( 'find_events_button_color' );
			$button_color_rgb = $this->get_rgb_color( 'find_events_button_color' );
		} elseif ( $this->should_include_setting_css( 'accent_color', 'global_elements' ) ) {
			$button_color     = tribe( 'customizer' )->get_option( [ 'global_elements', 'accent_color' ] );
			$button_color_rgb = $this->get_rgb_color( 'accent_color', 'global_elements' );
		}

		if ( ! empty( $button_color ) ) {
			$new_styles[] = "--tec-color-background-events-bar-submit-button: {$button_color};";
		}

		if ( ! empty( $button_color_rgb ) ) {
			$new_styles[] = "--tec-color-background-events-bar-submit-button-hover: rgba({$button_color_rgb}, 0.8);";
			$new_styles[] = "--tec-color-background-events-bar-submit-button-active: rgba({$button_color_rgb}, 0.9);";

		}

		if ( $this->should_include_setting_css( 'events_bar_background_color_choice' ) ) {
			if ( $this->should_include_setting_css( 'events_bar_background_color' ) ) {
				if ( 'custom' == $this->get_option( 'events_bar_background_color_choice' ) ) {
					$background_color = $this->get_option( 'events_bar_background_color' );
				} elseif (
					'global_background' == $this->get_option( 'events_bar_background_color_choice' )
					&& $this->should_include_setting_css( 'background_color', 'global_elements' )
				) {
					$background_color = tribe('customizer')->get_option( [ 'global_elements', 'background_color' ] );
				}
			}

			if ( ! empty( $background_color ) ) {
				$new_styles[] = "--tec-color-background-events-bar: {$background_color};";
				$new_styles[] = "--tec-color-background-events-bar-tabs: {$background_color};";
			}
		}

		if ( $this->should_include_setting_css( 'events_bar_border_color_choice' ) ) {
			$border_color = $this->get_option( 'events_bar_border_color' );

			if ( ! empty( $border_color ) ) {
				$new_styles[] = "--tec-color-border-events-bar: {$border_color};";
			}
		}

		if ( empty( $new_styles ) ) {
			return $css_template;
		}

		$new_css = sprintf(
			':root {
				/* Customizer-added Events Bar styles */
				%1$s
			}',
			implode( "\n", $new_styles )
		);

		return $css_template . $new_css;
	}
}
