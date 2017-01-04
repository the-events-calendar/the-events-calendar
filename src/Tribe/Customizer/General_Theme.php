<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * General Theme
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since 4.4
 */
final class Tribe__Events__Customizer__General_Theme extends Tribe__Customizer__Section {
	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 *
	 * Note: This is the only required method for a Connector to work
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance() {
		return tribe( 'tec.customizer.general-theme' );
	}

	/**
	 * Grab the CSS rules template
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Customizer::instance();

		if ( $customizer->has_option( $this->ID, 'accent_color' ) ) {
			$template .= '
				.tribe-events-calendar td.tribe-events-present div[id*="tribe-events-daynum-"],
				#tribe_events_filters_wrapper input[type=submit],
				.tribe-events-button,
				#tribe-events .tribe-events-button,
				.tribe-events-button.tribe-inactive,
				#tribe-events .tribe-events-button:hover,
				.tribe-events-button:hover,
				.tribe-events-button.tribe-active:hover {
					background-color: <%= general_theme.accent_color %>;
				}

				#tribe-events-content .tribe-events-tooltip h4,
				#tribe_events_filters_wrapper .tribe_events_slider_val,
				.single-tribe_events a.tribe-events-ical,
				.single-tribe_events a.tribe-events-gcal {
					color: <%= general_theme.accent_color %>;
				}

				.tribe-grid-allday .tribe-events-week-allday-single,
				.tribe-grid-body .tribe-events-week-hourly-single,
				.tribe-grid-allday .tribe-events-week-allday-single:hover,
				.tribe-grid-body .tribe-events-week-hourly-single:hover {
					background-color: <%= general_theme.accent_color %>;
					border-color: rgba(0, 0, 0, 0.3);
				}
			';
		}

		if ( $scheme = $customizer->get_option( array( $this->ID, 'featured_color_scheme' ) ) ) {
			$scheme = $this->sanitize_featured_color_choice( $scheme );
			$schemes = $this->get_featured_color_schemes();

			$is_light  = false;
			$is_custom = false;

			if ( 'custom' === $scheme ) {
				$background_color = $customizer->get_option( array( $this->ID, 'featured_color_scheme_custom' ) );
				$scheme           = 'default';
				$is_custom        = true;
			} else {
				$background_color = $schemes[ $scheme ]['colors'][0];
			}

			if ( ! $background_color ) {
				$background_color = $schemes['default']['colors'][0];
			}

			$background_color_obj = new Tribe__Utils__Color( $background_color );
			$background_rgb = $background_color_obj->getRgb();

			if ( $is_custom ) {
				$is_light = $background_color_obj->isLight();
			}

			$template .= '
				.tribe-events-list .tribe-events-loop .tribe-event-featured,
				.tribe-events-list #tribe-events-day.tribe-events-loop .tribe-event-featured,
				.type-tribe_events.tribe-events-photo-event.tribe-event-featured .tribe-events-photo-event-wrap,
				.type-tribe_events.tribe-events-photo-event.tribe-event-featured .tribe-events-photo-event-wrap:hover {
					background-color: ' . $background_color . ';
				}

				#tribe-events-content table.tribe-events-calendar .type-tribe_events.tribe-event-featured {
					background-color: ' . $background_color . ';
				}

				.tribe-events-list-widget .tribe-event-featured,
				.tribe-events-venue-widget .tribe-event-featured,
				.tribe-mini-calendar-list-wrapper .tribe-event-featured,
				.tribe-events-adv-list-widget .tribe-event-featured .tribe-mini-calendar-event {
					background-color: ' . $background_color . ';
				}

				.tribe-grid-body .tribe-event-featured.tribe-events-week-hourly-single {
					background-color: rgba(' . "{$background_rgb['R']},{$background_rgb['G']},{$background_rgb['B']}, .7 )" . ';
					border-color: ' . $background_color . ';
				}

				.tribe-grid-body .tribe-event-featured.tribe-events-week-hourly-single:hover {
					background-color: ' . $background_color . ';
				}
			';

			if ( $is_light ) {
				$template .= '
					.tribe-events-list .tribe-events-loop .tribe-event-featured .tribe-events-event-cost span,
					#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured [class*="-event-title"] a,
					#tribe-events-content table.tribe-events-calendar .type-tribe_events.tribe-event-featured [class*="-event-title"] a,
					.events-archive.events-gridview #tribe-events-content table .type-tribe_events.tribe-event-featured .tribe-events-month-event-title a,
					.tribe-grid-body .tribe-event-featured.tribe-events-week-hourly-single a,
					.tribe-mini-calendar-list-wrapper .tribe-event-featured .tribe-events-title a,
					.tribe-mini-calendar-list-wrapper .tribe-event-featured .tribe-mini-calendar-event .tribe-events-title a,
					.tribe-events-list-widget .tribe-event-featured .tribe-event-title a,
					.tribe-events-list-widget .tribe-event-featured .tribe-mini-calendar-event .tribe-event-title a,
					.tribe-events-adv-list-widget .tribe-event-featured .tribe-events-title a,
					.tribe-events-adv-list-widget .tribe-event-featured .tribe-mini-calendar-event .tribe-events-title a {
						color: #000;
					}

					#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured [class*="-event-title"] a:hover,
					#tribe-events-content table.tribe-events-calendar .type-tribe_events.tribe-event-featured [class*="-event-title"] a:hover,
					.events-archive.events-gridview #tribe-events-content table .type-tribe_events.tribe-event-featured .tribe-events-month-event-title a:hover,
					.tribe-grid-body .tribe-event-featured.tribe-events-week-hourly-single a:hover,
					.tribe-mini-calendar-list-wrapper .tribe-event-featured .tribe-events-title a:hover,
					.tribe-mini-calendar-list-wrapper .tribe-event-featured .tribe-mini-calendar-event .tribe-events-title a:hover,
					.tribe-events-adv-list-widget .tribe-event-featured .tribe-events-title a:hover,
					.tribe-events-adv-list-widget .tribe-event-featured .tribe-mini-calendar-event .tribe-events-title a:hover {
						color: rgba( 0, 0, 0, .7 );
					}

					.tribe-events-list .tribe-events-loop .tribe-event-featured,
					.tribe-events-list .tribe-events-loop .tribe-event-featured .entry-summary,
					.tribe-events-list .tribe-events-loop .tribe-event-featured .tribe-events-content,
					.tribe-events-list #tribe-events-day.tribe-events-loop .tribe-event-featured,
					.tribe-events-list #tribe-events-day.tribe-events-loop .tribe-event-featured .entry-summary,
					.tribe-events-list-widget .tribe-event-featured,
					.tribe-events-list-widget .tribe-event-featured .tribe-event-duration,
					.tribe-mini-calendar-list-wrapper .tribe-event-featured,
					.tribe-events-adv-list-widget .tribe-event-featured {
						color: rgba( 0, 0, 0, .9 );
					}

					.tribe-event-featured .event-is-recurring,
					.tribe-events-venue-widget .tribe-event-featured,
					#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured a,
					.tribe-events-list-widget .tribe-event-featured a,
					.tribe-events-list-widget .tribe-event-featured .tribe-event-title a,
					.tribe-events-venue-widget .tribe-event-featured a,
					.tribe-events-venue-widget .tribe-event-featured .tribe-event-title a,
					.tribe-events-adv-list-widget .tribe-event-featured .tribe-events-duration,
					.tribe-mini-calendar-list-wrapper .tribe-event-featured .tribe-events-duration,
					.tribe-events-list .tribe-events-loop .tribe-event-featured .tribe-events-event-meta {
						color: rgba( 0, 0, 0, .7 );
					}

					#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured a:active,
					#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured a:hover,
					.tribe-events-list-widget .tribe-event-featured a:active,
					.tribe-events-list-widget .tribe-event-featured a:hover,
					.tribe-events-venue-widget .tribe-event-featured a:active,
					.tribe-events-venue-widget .tribe-event-featured a:hover {
						color: #000;
					}
				';
			}
		}

		return $template;
	}

	public function setup() {
		$this->defaults = array(
			'base_color_scheme' => 'light',
			'featured_color_scheme' => 'default',
		);

		$this->arguments = array(
			'priority'    => 10,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'General Theme', 'the-events-calendar' ),
			'description' => esc_html__( 'Global configurations for the styling of The Events Calendar', 'the-events-calendar' ),
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
			$customizer->get_setting_name( 'accent_color', $section ),
			array(
				'default'              => $this->get_default( 'accent_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'accent_color', $section ),
				array(
					'label'   => esc_html__( 'Accent Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'featured_color_scheme', $section ),
			array(
				'default'              => $this->get_default( 'featured_color_scheme' ),
				'sanitize_callback'    => array( $this, 'sanitize_featured_color_choice' ),
				'type'                 => 'option',
			)
		);

		$manager->add_control(
			new WP_Customize_Control(
				$manager,
				$customizer->get_setting_name( 'featured_color_scheme', $section ),
				array(
					'label'    => __( 'Featured Events Highlight Color', 'the-events-calendar' ),
					'section'  => $section->id,
					'type'     => 'select',
					'choices'  => $this->get_featured_color_choices(),
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'featured_color_scheme_custom', $section ),
			array(
				'default'              => $this->get_default( 'featured_color_scheme_custom' ),
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'type'                 => 'option',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'featured_color_scheme_custom', $section ),
				array(
					'description' => __( 'If the Featured Events highlight color is set to Custom, the following color will be used:', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'accent_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'featured_color_scheme', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'featured_color_scheme_custom', $section ) );
	}

	/**
	 * Get color schemes for featured events
	 *
	 * @return array
	 */
	public function get_featured_color_schemes() {
		$schemes = array(
			'blue-steel' => array(
				'label' => __( 'Blue Steel', 'the-events-calendar' ),
				'colors' => array(
					'#2b474f',
				),
			),
			'deep-sea' => array(
				'label' => __( 'Deep Sea', 'the-events-calendar' ),
				'colors' => array(
					'#157f9d',
				),
			),
			'default' => array(
				'label' => __( 'Default', 'the-events-calendar' ),
				'colors' => array(
					'#0ea0d7',
				),
			),
			'evergreen' => array(
				'label' => __( 'Evergreen', 'the-events-calendar' ),
				'colors' => array(
					'#416d53',
				),
			),
			'lagoon' => array(
				'label' => __( 'Lagoon', 'the-events-calendar' ),
				'colors' => array(
					'#1ca8c7',
				),
			),
			'malacite' => array(
				'label' => __( 'Malacite', 'the-events-calendar' ),
				'colors' => array(
					'#078e87',
				),
			),
			'skyfall' => array(
				'label' => __( 'Skyfall', 'the-events-calendar' ),
				'colors' => array(
					'#2f3750',
				),
			),
			'sunshine' => array(
				'label' => __( 'Sunshine', 'the-events-calendar' ),
				'colors' => array(
					'#f4af49',
				),
			),
			'custom' => array(
				'label' => __( 'Custom', 'the-events-calendar' ),
				'colors' => array(),
			),
		);

		/**
		 * Filter the color schemes for featured events
		 *
		 * @param array $schemes Available color schemes for featured events
		 */
		return apply_filters( 'tribe_events_customizer_featured_color_schemes', $schemes );
	}

	/**
	 * Gets featured color choices as key/value pairs
	 *
	 * @return array
	 */
	public function get_featured_color_choices() {
		$schemes = $this->get_featured_color_schemes();

		$choices = array();

		foreach ( $schemes as $scheme => $data ) {
			// add a divider before the "Custom" choice
			if ( 'custom' === $scheme ) {
				$choices[ 'divider' ] = '---';
			}

			$choices[ $scheme ] = $data['label'];
		}

		return $choices;
	}

	/**
	 * Sanitizes the featured color choices
	 *
	 * @param string $choice Color choice
	 *
	 * @return string
	 */
	public function sanitize_featured_color_choice( $choice ) {
		$schemes = $this->get_featured_color_schemes();

		if ( ! isset( $schemes[ $choice ] ) ) {
			$choice = 'default';
		}

		return $choice;
	}
}
