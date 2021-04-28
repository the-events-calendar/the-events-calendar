<?php
/**
 * The Events Calendar Customizer Section Class
 * Month View
 *
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Customizer\Section;

/**
 * Month View
 *
 * @since TBD
 */
class Month_View extends \Tribe__Customizer__Section {

	public function setup() {
		parent::setup();

		$this->defaults = [
			'grid_lines_color'             => '#e4e4e4',
			'grid_background_color_choice' => 'transparent',
			'days_of_week_color'           => '#5d5d5d',
			'date_marker_color'            => '#141827',
			'multiday_background_color'    => '#334aff',
		];

		$this->arguments = [
			'priority'    => 65,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Month View', 'the-events-calendar' ),
			'description' => esc_html__( 'Options selected here will override what was selected in the "Global Elements" section.', 'the-events-calendar' ),
		];
	}

	public function setup_content_headings() {
		$this->content_headings = [
			'grid' => [
				'priority' => 0,
				'label'    => esc_html_x(
					'Grid',
					'The header for the grid color control section.',
					'the-events-calendar'
				),
			],
			'date_day' => [
				'priority' => 10,
				'label'    => esc_html_x(
					'Date and Day',
					'The header for the date and day color control section.',
					'the-events-calendar'
				),
			]
		];
	}

	public function setup_content_settings() {
		$this->content_settings = [
			'grid_lines_color'         => [
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'grid_background_color_choice'    => [
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			/*
			'grid_background_color'    => [
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			*/
			'days_of_week_color'       => [
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'date_marker_color'        => [
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'multiday_event_bar_color' => [
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		];
	}

	public function setup_content_controls() {
		$this->content_controls = [
			'grid_lines_color'         => [
				'control_type' => 'color',
				'label'        => esc_html_x(
					'Grid lines color',
					'The grid lines color setting label.',
					'the-events-calendar'
				),
				'priority'     => 3,
			],
			'grid_background_color_choice'    => [
				'priority'     => 6,
				'control_type' => 'default',
				'type'         => 'radio',
				'label'        => esc_html_x(
					'Grid background color',
					'The grid background color setting label.',
					'the-events-calendar'
				),
				'description'  => esc_html_x(
					'The Month View grid background',
					'The grid background color setting description.',
					'the-events-calendar'
				),
				'choices'      => [
					'transparent' => esc_html_x(
						'Transparent (allows Events Background Color to show though) ',
						'Label for option to leave transparent.',
						'the-events-calendar'
					),
					'custom'      => esc_html_x(
						'Select Color',
						'Label for option to set a color.',
						'the-events-calendar'
					),
				],
			],
			'grid_background_color'    => [
				'control_type'    => 'color',
				'priority'        => 7,
				'active_callback' => function ( $control ) {
					return 'custom' == $control->manager->get_setting( tribe( 'customizer' )->get_setting_name( 'grid_background_color_choice', tribe( 'customize' )->get_section( $control->section ) ) )->value();
				},

			],
			'days_of_week_color'       => [
				'priority'     => 13,
				'control_type' => 'color',
				'label'        => esc_html_x(
					'Days of the Week color',
					'The days of the week text color setting label.',
					'the-events-calendar'
				),
				'description'  => esc_html_x(
					'The Month View grid background',
					'The days of the week text color setting description.',
					'the-events-calendar'
				),
			],
			'date_marker_color'        => [
				'priority'     => 16,
				'control_type' => 'color',
				'label'        => esc_html_x(
					'Date Marker color',
					'The date marker text color setting label.',
					'the-events-calendar'
				),
				'description'  => esc_html_x(
					'The Month View grid lines',
					'The date marker text color setting description.',
					'the-events-calendar'
				),
			],
			'multiday_event_bar_color' => [
				'priority'     => 19,
				'control_type' => 'color',
				'label'        => esc_html_x(
					'Multiday and All-day Event bar color',
					'The multiday event bar color setting label.',
					'the-events-calendar'
				),
				'description'  => esc_html_x(
					'This overrides the Accent Color from Global Settings.
					This only applies to current and future events.',
					'The multiday event bar color setting description.',
					'the-events-calendar'
				),
			],
		];
	}
}
