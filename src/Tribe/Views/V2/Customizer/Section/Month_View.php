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
class Month_View extends \Tribe__Customizer__Section {

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
	 * Allows sections to be loaded in order for overrides.
	 *
	 * @var integer
	 */
	public $queue_priority = 20;

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
		$this->defaults = [
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
		$this->arguments = [
			'priority'	=> 65,
			'capability'  => 'edit_theme_options',
			'title'	   => esc_html__( 'Month View', 'the-events-calendar' ),
			'description' => esc_html__(
				'Options selected here will override what was selected in the ' . $this->get_general_settings_link() . ' section.',
				'the-events-calendar'
			),
			/* translators: 1: Customizer link (HTML). */
			'description' => sprintf(
				_x(
					'Options selected here will override what was selected in the %1$s section.',
					'Note about overriding general settings. Contains link HTML.',
					'the-events-calendar'
				),
				$this->get_general_settings_link()
			)
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_headings() {
		$this->content_headings = [
			'date_day' => [
				'priority'	 => 0,
				'type'		 => 'heading',
				'label'		=> esc_html_x(
					'Set Font Colors',
					'The header for the date and day color control section.',
					'the-events-calendar'
				),
			],
			'month_view_separator-10' => [
				'priority'	 => 10,
				'type'		 => 'separator',
			],
			'grid' => [
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
		$this->content_settings = [
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
		$this->content_controls = [
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
					'transparent' => sprintf(
						_x(
							'Transparent.',
							'Label for option to leave transparent (default).',
							'the-events-calendar'
						)
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
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'grid_background_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return $this->defaults['grid_background_color_choice'] !== $value;
				},

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
					'Preview display when hovering on an event title.',
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
						/* translators: 1: Customizer link (HTML). */
						_x(
							'Use the %1$s.',
							'Label for option to use the event background color.',
							'the-events-calendar'
						),
						$this->get_events_background_link()
					)
				],
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'grid_background_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return $this->defaults['grid_background_color_choice'] === $value;
				},

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
					'For all-day and multi-day events.',
					'The multiday event bar color setting description.',
					'the-events-calendar'
				),
				'choices'     => [
					'default' => _x(
						'Use the ' . $this->get_accent_color_link() . '',
						'Label for option to use the accent color.',
						'the-events-calendar'
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
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'multiday_event_bar_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return $this->defaults['multiday_event_bar_color_choice'] !== $value;
				},
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
					'Bottom border highlight when hovering on a day.',
					'The grid hover color setting description.',
					'the-events-calendar'
				),
			],
		];
	}

	/**
	 * Gets the link to the a setting in the TEC Customizer Global Elements.
	 *
	 * @since 5.7.0
	 *
	 * @todo (Stephen): Maybe move this to common? Generalize more or create on for each section?
	 *
	 * @param string $setting    The sting setting "slug" to link to.
	 * @param string $label_text The translated label text for the link.
	 *
	 * @return string The HTML link element.
	 */
	public function get_global_element_link( $setting, $label_text ) {
		if ( empty( $setting ) ) {
			// Default to first item if not set.
			$setting = 'event_title_color';
		}
		$control                     = tribe( 'customizer' )->get_setting_name( $setting, 'global_elements' );
		$query['autofocus[control]'] = 'tribe_customizer' . $control;
		$control_url                 = add_query_arg( $query, admin_url( 'customize.php' ) );

		return sprintf(
			/* translators: 1: Customizer URL, 2: Passed (already translated) link text. */
			'<a href="%1$s">%2$s</a>',
			esc_url( $control_url ),
			esc_html( $label_text )
		);
	}

	/**
	 * Gets the link to the event background color setting in Customizer.
	 *
	 * @since 5.7.0
	 *
	 * @return string The HTML link element.
	 */
	public function get_general_settings_link() {
		$label_text = _x(
			'General',
			'Text used for links to the General settings section.',
			'the-events-calendar'
		);
		return $this->get_global_element_link( '', $label_text );
	}

	/**
	 * Gets the link to the event background color setting in Customizer.
	 *
	 * @since 5.7.0
	 *
	 * @return string The HTML link element.
	 */
	public function get_events_background_link() {
		$label_text = _x(
			'General Background Color',
			'Text used for links to the Event Background Color setting.',
			'the-events-calendar'
		);
		return $this->get_global_element_link( 'background_color_choice', $label_text );
	}

	/**
	 * Gets the link to the accent color setting in Customizer.
	 *
	 * @since 5.7.0
	 *
	 * @return string The HTML link element.
	 */
	public function get_accent_color_link() {
		$label_text = _x(
			'Accent Color',
			'Text used for links to the Accent Color setting.',
			'the-events-calendar'
		);
		return $this->get_global_element_link( 'accent_color', $label_text );
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

		// These allow us to continue to _not_ target the shortcode.
		$apply_to_shortcode = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );
		$tribe_events = $apply_to_shortcode ? '.tribe-events' : '.tribe-events:not( .tribe-events-view--shortcode )';

		if ( $this->should_include_setting_css( 'grid_lines_color' ) ) {
			$template .= "
				$tribe_events.tribe-common--breakpoint-medium .tribe-events-calendar-month__body,
				$tribe_events.tribe-common--breakpoint-medium .tribe-events-calendar-month__day,
				$tribe_events.tribe-common--breakpoint-medium .tribe-events-calendar-month__week {
					border-color: <%= month_view.grid_lines_color %>;
				}
			";
		}

		if ( $this->should_include_setting_css( 'grid_hover_color' ) ) {
			$template .= "
				$tribe_events.tribe-common--breakpoint-medium .tribe-events-calendar-month__day:hover::after {
					background-color: <%= month_view.grid_hover_color %>;
				}
			";
		}

		if ( $this->should_include_setting_css( 'grid_background_color_choice' ) ) {
			if ( $this->should_include_setting_css( 'grid_background_color' ) ) {
				$template .="
					$tribe_events .tribe-events-calendar-month__body {
						background-color: <%= month_view.grid_background_color %>;
					}
				";
			}
		} else {
			if (
				$this->should_include_setting_css( 'tooltip_background_color' )
				&& $this->should_include_setting_css( 'background_color_choice', 'global_elements' )
			) {
				$template .="
					.tooltipster-base.tribe-events-tooltip-theme,
					.tooltipster-base.tribe-events-tooltip-theme--hover {
						background-color: <%= global_elements.background_color %>;
					}
				";
			}
		}

		if ( $this->should_include_setting_css( 'days_of_week_color' )  ) {
			$template .="
				$tribe_events .tribe-events-calendar-month__header-column-title {
					color: <%= month_view.days_of_week_color %>;
				}
			";
		}

		if ( $this->should_include_setting_css( 'date_marker_color' )  ) {
			$template .="
				.tribe-events-calendar-month__day-date.tribe-common-h4,
				$tribe_events .tribe-events-calendar-month__day-date-link,
				$tribe_events .tribe-events-calendar-month__day:not(.tribe-events-calendar-month__day--current) .tribe-events-calendar-month__day-date-daynum {
					color: <%= month_view.date_marker_color %>;
				}
			";
		}

		if ( $this->should_include_setting_css( 'multiday_event_bar_color_choice' ) ) {
			if ( $this->should_include_setting_css( 'multiday_event_bar_color' ) ) {
				$bar_color_rgb   = $this->to_rgb( $this->get_option( 'multiday_event_bar_color' ) );
				$bar_color       = 'rgba(' . $bar_color_rgb . ',0.24)';
				$bar_color_hover = 'rgba(' . $bar_color_rgb . ',0.34)';

				$template .="
					$tribe_events .tribe-events-calendar-month__multiday-event:not(.tribe-events-calendar-month__multiday-event--past) .tribe-events-calendar-month__multiday-event-bar-inner {
						background-color: $bar_color;
					}

					$tribe_events tribe-events-calendar-month__multiday-event:not(.tribe-events-calendar-month__multiday-event--past) .tribe-events-calendar-month__multiday-event-bar-inner--hover,
					$tribe_events tribe-events-calendar-month__multiday-event:not(.tribe-events-calendar-month__multiday-event--past) .tribe-events-calendar-month__multiday-event-bar-inner--focus {
						background-color: $bar_color_hover;
					}
				";


			}
		}
		return $template;
	}
}
