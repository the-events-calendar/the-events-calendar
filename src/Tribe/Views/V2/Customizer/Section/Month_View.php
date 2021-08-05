<?php
/**
 * The Events Calendar Customizer Section Class
 * Month View
 *
 * @since 5.7.0
 */

namespace Tribe\Events\Views\V2\Customizer\Section;
/**
 * Month View
 *
 * @since 5.7.0
 */
final class Month_View extends \Tribe__Customizer__Section {

	/**
	 * ID of the section.
	 *
	 * @since 5.7.0
	 *
	 * @access public
	 * @var string
	 */
	public $ID = 'month_view';

	/**
	 * Allows section CSS to be loaded in order for overrides.
	 *
	 * @var integer
	 */
	public $queue_priority = 25;

	/**
	 * This method will be executed when the Class is Initialized.
	 *
	 * @since 5.7.0
	 */
	public function setup() {
		parent::setup();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_defaults() {
		return [
			'grid_lines_color'				  => '#e4e4e4',
			'grid_background_color_choice'	  => 'transparent',
			'grid_background_color'		      => '#FFFFFF',
			'grid_hover_color'                => '#141827',
			'days_of_week_color'			  => '#5d5d5d',
			'date_marker_color'			      => '#141827',
			'multiday_event_bar_color_choice' => 'default',
			'multiday_event_bar_color'	      => '#334aff',
			'tooltip_background_color'		  => 'default',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_arguments() {
		return [
			'priority'	=> 15,
			'capability'  => 'edit_theme_options',
			'title'	   => esc_html__( 'Month View', 'the-events-calendar' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_headings() {
		return [
			'month_view_font_colors' => [
				'priority'	 => 0,
				'type'		 => 'heading',
				'label'		=> esc_html_x(
					'Set Font Colors',
					'The header for the font color control section.',
					'the-events-calendar'
				),
			],
			'month_view_separator-10' => [
				'priority'	 => 10,
				'type'		 => 'separator',
			],
			'month_view_appearance' => [
				'priority'	 => 11,
				'type'		 => 'heading',
				'label'		=> esc_html_x(
					'Adjust Grid Colors',
					'The header for the calendar grid color control section.',
					'the-events-calendar'
				),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_settings() {
		return [
			'grid_lines_color'				=> [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'grid_hover_color'				=> [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'grid_background_color_choice'	=> [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'grid_background_color'		   => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'tooltip_background_color'		=> [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'days_of_week_color'			  => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'date_marker_color'			   => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'multiday_event_bar_color_choice' => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'multiday_event_bar_color'		=> [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_controls() {
		$customizer = tribe( 'customizer' );
		return [
			'days_of_week_color'              => [
				'priority'    => 3,
				'type'        => 'color',
				'label'       => esc_html_x(
					'Days of the Week',
					'The days of the week text color setting label.',
					'the-events-calendar'
				),
			],
			'date_marker_color'               => [
				'priority'    => 5,
				'type'        => 'color',
				'label'       => esc_html_x(
					'Date Marker',
					'The date marker text color setting label.',
					'the-events-calendar'
				),
			],
			'grid_background_color_choice'    => [
				'priority'    => 17,
				'type'        => 'radio',
				'label'       => esc_html_x(
					'Grid Background',
					'The grid background color setting label.',
					'the-events-calendar'
				),
				'choices'     => [
					'transparent' => esc_html_x(
						'Transparent',
						'Label for option to leave transparent (default).',
						'the-events-calendar'
					),
					'custom'	  => esc_html_x(
						'Custom',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				],
			],
			'grid_background_color'           => [
				'priority'        => 18, // This should come immediately after 'grid_background_color_choice'.
				'type'            => 'color',
			],
			'tooltip_background_color'        => [
				'priority'    => 18, // This should come immediately after 'grid_background_color_choice'.
				'type'        => 'radio',
				'label'       => esc_html_x(
					'Event Preview Background',
					'Label for tooltip background color setting.',
					'the-events-calendar'
				),
				'description' => esc_html_x(
					'Preview display when hovering on an event title',
					'The grid background color setting description.',
					'the-events-calendar'
				),
				'choices'     => [
					'default' => _x(
						'White',
						'Label for option to leave white (default).',
						'the-events-calendar'
					),
					'event'	  => sprintf(
						/* translators: 1: Customizer url. */
						_x(
							'Use the <a href="%1$s">General Background Color</a>',
							'Label for option to use the event background color. Links to the event background color setting.',
							'the-events-calendar'
						),
						$customizer->get_setting_url(
							'global_elements',
							'background_color_choice'
						)
					)
				],
			],
			'grid_lines_color'                => [
				'priority' => 13,
				'type'     => 'color',
				'label'    => esc_html_x(
					'Grid Lines Color',
					'The grid lines color setting label.',
					'the-events-calendar'
				),
			],
			'multiday_event_bar_color_choice' => [
				'priority'    => 7,
				'type'        => 'radio',
				'label'       => esc_html_x(
					'Event Span',
					'The multiday event bar color setting label.',
					'the-events-calendar'
				),
				'description' => esc_html_x(
					'For all-day and multi-day events',
					'The multiday event bar color setting description.',
					'the-events-calendar'
				),
				'choices'     => [
					'default' => sprintf(
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
					'custom'  => _x(
						'Custom',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				],
			],
			'multiday_event_bar_color'        => [
				'priority'        => 9,
				'type'    => 'color',
			],
			'grid_hover_color'              => [
				'priority'    => 15,
				'type'        => 'color',
				'label'       => esc_html_x(
					'Day Hover',
					'Day hover color setting label.',
					'the-events-calendar'
				),
				'description' => esc_html_x(
					'Bottom border highlight when hovering on a day',
					'The grid hover color setting description.',
					'the-events-calendar'
				),
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

		$new_styles = [];

		// It's all custom props now, baby...

		if ( $this->should_include_setting_css( 'grid_lines_color' ) ) {
			$grid_lines_color = $this->get_option( 'grid_lines_color' );
			$new_styles[] = "--tec-color-border-secondary-month-grid: {$grid_lines_color};";
		}

		if ( $this->should_include_setting_css( 'grid_hover_color' ) ) {
			$grid_hover_color = $this->get_option( 'grid_hover_color' );
			$new_styles[] = "--tec-color-border-active-month-grid-hover: {$grid_hover_color};";
		}

		if ( $this->should_include_setting_css( 'grid_background_color_choice' ) ) {
			$grid_background_color = $this->should_include_setting_css( 'grid_background_color' )
				? $this->get_option( 'grid_background_color' )
				: "#fff";

			$new_styles[] = "--tec-color-background-month-grid: {$grid_background_color};";
		} elseif (
			$this->should_include_setting_css( 'tooltip_background_color' )
			&& $this->should_include_setting_css( 'background_color_choice', 'global_elements' )
		) {
			$tooltip_background_color = tribe( 'customizer' )->get_option( [ 'global_elements', 'background_color' ] );
			$new_styles[] = "--tec-color-background-tooltip: {$tooltip_background_color};";
		}

		if ( $this->should_include_setting_css( 'days_of_week_color' )  ) {
			$days_of_week_color = $this->get_option( 'days_of_week_color' );
			$new_styles[] = "--tec-color-text-day-of-week-month: {$days_of_week_color};";
		}

		if ( $this->should_include_setting_css( 'date_marker_color' )  ) {
			$date_marker_color = $this->get_option( 'date_marker_color' );
			$new_styles[] = "--tec-color-day-marker-month: {$date_marker_color};";
			$new_styles[] = "--tec-color-day-marker-past-month: {$date_marker_color};";
		}

		if (
			$this->should_include_setting_css( 'multiday_event_bar_color_choice' )
			&& $this->should_include_setting_css( 'multiday_event_bar_color' )
		) {
			$bar_color_rgb = $this->get_rgb_color( 'multiday_event_bar_color' );
			$new_styles[] = "--tec-color-background-primary-multiday: rgba({$bar_color_rgb}, 0.24);";
			$new_styles[] = "--tec-color-background-primary-multiday-hover: rgba({$bar_color_rgb}, 0.34);";
			$new_styles[] = "--tec-color-background-primary-multiday-active: rgba({$bar_color_rgb}, 0.34);";
			$new_styles[] = "--tec-color-background-secondary-multiday: rgba({$bar_color_rgb}, 0.24);";
			$new_styles[] = "--tec-color-background-secondary-multiday-hover: rgba({$bar_color_rgb}, 0.34);";
		}

		if ( empty( $new_styles ) ) {
			return $css_template;
		}

		$new_css = sprintf(
			':root {
				/* Customizer-added Month View styles */
				%1$s
			}',
			implode( "\n", $new_styles )
		);

		return $css_template . $new_css;
	}

	/* Deprecated */

	/**
	 * Gets the link to the a setting in the TEC Customizer Global Elements.
	 *
	 * @since 5.7.0
	 * @deprecated 5.8.0
	 *
	 * @param string $setting    The sting setting "slug" to link to.
	 * @param string $label_text The translated label text for the link.
	 *
	 * @return string The HTML link element.
	 */
	public function get_global_element_link( $setting, $label_text ) {
		_deprecated_function( __METHOD__, '5.8.0', "tribe( 'customizer' )->get_setting_link" );
		if ( empty( $setting ) ) {
			// Default to first item if not set.
			$setting = 'event_title_color';
		}

		return tribe( 'customizer' )->get_setting_link( 'global_elements', $setting, $label_text );
	}

	/**
	 * Gets the link to the event background color setting in Customizer.
	 *
	 * @since 5.8.0
	 * @deprecated 5.8.0
	 *
	 * @return string The HTML link element.
	 */
	public function get_general_settings_link() {
		_deprecated_function( __METHOD__, '5.8.0', "tribe( 'customizer' )->get_section_link" );

		$label_text = _x(
			'General',
			'Text used for links to the General settings section.',
			'the-events-calendar'
		);

		return tribe( 'customizer' )->get_section_link( 'global_elements', $label_text );
	}

	/**
	 * Gets the link to the event background color setting in Customizer.
	 *
	 * @since 5.8.0
	 * @deprecated 5.8.0
	 *
	 * @return string The HTML link element.
	 */
	public function get_events_background_link() {
		_deprecated_function( __METHOD__, '5.8.0', "tribe( 'customizer' )->get_setting_link" );
		$label_text = _x(
			'General Background Color',
			'Text used for links to the Event Background Color setting.',
			'the-events-calendar'
		);

		return tribe( 'customizer' )->get_setting_link( 'global_elements', 'background_color_choice', $label_text );
	}

	/**
	 * Gets the link to the accent color setting in Customizer.
	 *
	 * @since 5.8.0
	 * @deprecated 5.8.0
	 *
	 * @return string The HTML link element.
	 */
	public function get_accent_color_link() {
		_deprecated_function( __METHOD__, '5.8.0', "tribe( 'customizer' )->get_setting_link" );

		$label_text = _x(
			'Accent Color',
			'Text used for links to the Accent Color setting.',
			'the-events-calendar'
		);

		return tribe( 'customizer' )->get_setting_link( 'global_elements', 'accent_color', $label_text );
	}
}
