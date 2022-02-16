<?php
/**
 * Handles Views v2 Customizer settings.
 *
 * @since   5.3.1
 * @deprecated 5.9.0
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

use WP_Customize_Color_Control as Color_Control;
use WP_Customize_Control as Control;

/**
 * Class Customizer
 *
 * @since   5.3.1
 * @deprecated 5.9.0
 *
 * @package Tribe\Events\Views\V2
 */
class Customizer {
	/**
	 * Adds new settings/controls to the Global Elements section via the hook in common.
	 *
	 * @since 5.3.1
	 *
	 * @param \Tribe__Customizer__Section $section    The Global Elements Customizer section.
	 * @param WP_Customize_Manager        $manager    The settings manager.
	 * @param \Tribe__Customizer|null     $customizer The Customizer object.
	 */
	public function include_global_elements_settings( $section, $manager, $customizer = null ) {
		if ( null === $customizer ) {
			$customizer = tribe( 'customizer' );
		}

		// Event Title.
		$manager->add_setting(
			$customizer->get_setting_name( 'event_title_color', $section ),
			[
				'default'              => '#141827',
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new Color_Control(
				$manager,
				$customizer->get_setting_name( 'event_title_color', $section ),
				[
					'label'    => esc_html__( 'Event Title', 'the-events-calendar' ),
					'section'  => $section->id,
					'priority' => 8,
				]
			)
		);

		// Event Date & Time.
		$manager->add_setting(
			$customizer->get_setting_name( 'event_date_time_color', $section ),
			[
				'default'              => '#5d5d5d',
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new Color_Control(
				$manager,
				$customizer->get_setting_name( 'event_date_time_color', $section ),
				[
					'label'       => esc_html__( 'Event Date and Time', 'the-events-calendar' ),
					'description' => esc_html__( 'Main date and time display on views and single event pages', 'the-events-calendar' ),
					'section'     => $section->id,
					'priority'    => 8,
				]
			)
		);

		// Background Color.
		$manager->add_setting(
			$customizer->get_setting_name( 'background_color_choice', $section ),
			[
				'default'              => 'transparent',
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			]
		);

		$manager->add_control(
			new Control(
				$manager,
				$customizer->get_setting_name( 'background_color_choice', $section ),
				[
					'label'       => esc_html__( 'Background Color', 'the-events-calendar' ),
					'section'     => $section->id,
					'description' => esc_html__( 'All calendar and event pages', 'the-events-calendar' ),
					'type'        => 'radio',
					'priority'    => 12,
					'choices'     => [
						'transparent' => esc_html__( 'Transparent', 'the-events-calendar' ),
						'custom'      => esc_html__( 'Select Color', 'the-events-calendar' ),
					],
				]
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'background_color', $section ),
			[
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new Color_Control(
				$manager,
				$customizer->get_setting_name( 'background_color', $section ),
				[
					'section'         => $section->id,
					'priority'        => 12,
					'active_callback' => function ( $control ) use ( $customizer, $section ) {
						return 'custom' == $control->manager->get_setting( $customizer->get_setting_name( 'background_color_choice', $section ) )->value();
					},
				]
			)
		);

		// Accent Color.
		$manager->add_setting(
			$customizer->get_setting_name( 'accent_color', $section ),
			[
				'default'              => '#334aff',
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);
	}

	/**
	 * Adds new settings/controls to the Single Events section via the hook in common.
	 *
	 * @since 5.3.1
	 *
	 * @param \Tribe__Customizer__Section $section    The Single Events Customizer section.
	 * @param WP_Customize_Manager        $manager    The settings manager.
	 * @param \Tribe__Customizer|null     $customizer The Customizer object.
	 */
	public function include_single_event_settings( $section, $manager, $customizer = null ) {
		if ( null === $customizer ) {
			$customizer = tribe( 'customizer' );
		}

		// Remove the old setting/control to refactor.
		$manager->remove_setting( $customizer->get_setting_name( 'post_title_color', $section ) );
		$manager->remove_control( $customizer->get_setting_name( 'post_title_color', $section ) );

		// Register new control with option.
		$manager->add_setting(
			$customizer->get_setting_name( 'post_title_color_choice', $section ),
			[
				'default'              => 'general',
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			]
		);

		$manager->add_control(
			new Control(
				$manager,
				$customizer->get_setting_name( 'post_title_color_choice', $section ),
				[
					'label'       => esc_html__( 'Event Title', 'the-events-calendar' ),
					'section'     => $section->id,
					'type'        => 'radio',
					'priority'    => 5,
					'choices'     => [
						'general' => esc_html__( 'Use General', 'the-events-calendar' ),
						'custom'  => esc_html__( 'Custom', 'the-events-calendar' ),
					],
				]
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'post_title_color', $section ),
			[
				'default'              => tribe_events_views_v2_is_enabled() ? '#141827' : '#333',
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new Color_Control(
				$manager,
				$customizer->get_setting_name( 'post_title_color', $section ),
				[
					'label'   => esc_html__( 'Custom Color', 'the-events-calendar' ),
					'section' => $section->id,
					'priority'        => 6,
					'active_callback' => function ( $control ) use ( $customizer, $section ) {
						return 'custom' == $control->manager->get_setting( $customizer->get_setting_name( 'post_title_color_choice', $section ) )->value();
					},
				]
			)
		);
	}

	/**
	 * Filters the Global Elements section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 5.3.1
	 *
	 * @param string                      $css_template The CSS template, as produced by the Global Elements.
	 * @param \Tribe__Customizer__Section $section      The Global Elements section.
	 * @param \Tribe__Customizer|null     $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_global_elements_css_template( $css_template, $section, $customizer = null ) {
		if ( null === $customizer ) {
			$customizer = tribe( 'customizer' );
		}

		$settings = $customizer->get_option( [ $section->ID ] );
		// These allow us to continue to _not_ target the shortcode.
		$apply_to_shortcode = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );
		$tribe_events = $apply_to_shortcode ? '.tribe-events' : '.tribe-events:not( .tribe-events-view--shortcode )';
		$tribe_common = $apply_to_shortcode ? '.tribe-common' : '.tribe-common:not( .tribe-events-view--shortcode )';

		if ( $customizer->has_option( $section->ID, 'event_title_color' ) ) {
			// Event Title overrides.
			$css_template .= "
				.single-tribe_events .tribe-events-single-event-title,
				$tribe_events .tribe-events-calendar-list__event-title-link,
				$tribe_events .tribe-events-calendar-list__event-title-link:active,
				$tribe_events .tribe-events-calendar-list__event-title-link:visited,
				$tribe_events .tribe-events-calendar-list__event-title-link:hover,
				$tribe_events .tribe-events-calendar-list__event-title-link:focus,
				$tribe_events .tribe-events-calendar-month__calendar-event-title-link,
				$tribe_events .tribe-events-calendar-month__calendar-event-title-link:active,
				$tribe_events .tribe-events-calendar-month__calendar-event-title-link:visited,
				$tribe_events .tribe-events-calendar-month__calendar-event-title-link:hover,
				$tribe_events .tribe-events-calendar-month__calendar-event-title-link:focus,
				$tribe_events .tribe-events-calendar-month__multiday-event-bar-title,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:active,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:visited,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:hover,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:focus,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:active,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:visited,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:hover,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:focus,
				$tribe_events .tribe-events-calendar-day__event-title-link,
				$tribe_events .tribe-events-calendar-day__event-title-link:active,
				$tribe_events .tribe-events-calendar-day__event-title-link:visited,
				$tribe_events .tribe-events-calendar-day__event-title-link:hover,
				$tribe_events .tribe-events-calendar-day__event-title-link:focus,
				$tribe_events .tribe-events-calendar-latest-past__event-title-link,
				$tribe_events .tribe-events-calendar-latest-past__event-title-link:active,
				$tribe_events .tribe-events-calendar-latest-past__event-title-link:visited,
				$tribe_events .tribe-events-calendar-latest-past__event-title-link:hover,
				$tribe_events .tribe-events-calendar-latest-past__event-title-link:focus,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-list__event-title-link:hover,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-list__event-title-link:focus,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__calendar-event-title-link:hover,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__calendar-event-title-link:focus,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:hover,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:focus,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:hover,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:focus,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-day__event-title-link:hover,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-day__event-title-link:focus,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-latest-past__event-title-link:hover,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-latest-past__event-title-link:focus,
				.tribe-theme-enfold#top $tribe_events .tribe-events-calendar-list__event-title-link,
				.tribe-theme-enfold#top $tribe_events .tribe-events-calendar-month__calendar-event-title-link,
				.tribe-theme-enfold#top $tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link,
				.tribe-theme-enfold#top $tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link,
				.tribe-theme-enfold#top $tribe_events .tribe-events-calendar-day__event-title-link,
				.tribe-theme-enfold#top $tribe_events .tribe-events-calendar-latest-past__event-title-link {
					color: <%= global_elements.event_title_color %>;
				}

				$tribe_events .tribe-events-calendar-list__event-title-link:active,
				$tribe_events .tribe-events-calendar-list__event-title-link:hover,
				$tribe_events .tribe-events-calendar-list__event-title-link:focus,
				$tribe_events .tribe-events-calendar-month__calendar-event-title-link:active,
				$tribe_events .tribe-events-calendar-month__calendar-event-title-link:hover,
				$tribe_events .tribe-events-calendar-month__calendar-event-title-link:focus,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:active,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:hover,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:focus,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:active,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:hover,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-title-link:focus,
				$tribe_events .tribe-events-calendar-day__event-title-link:active,
				$tribe_events .tribe-events-calendar-day__event-title-link:hover,
				$tribe_events .tribe-events-calendar-day__event-title-link:focus,
				$tribe_events .tribe-events-calendar-latest-past__event-title-link:active,
				$tribe_events .tribe-events-calendar-latest-past__event-title-link:hover,
				$tribe_events .tribe-events-calendar-latest-past__event-title-link:focus {
					border-color: <%= global_elements.event_title_color %>;
				}
			";
		}

		if ( $customizer->has_option( $section->ID, 'event_date_time_color' ) ) {
			$color          = $section->get_option( 'event_date_time_color' );
			$date_color     = new \Tribe__Utils__Color( $color );
			$date_color_rgb = $date_color::hexToRgb( $color );
			$date_css_rgb   = $date_color_rgb['R'] . ',' . $date_color_rgb['G'] . ',' . $date_color_rgb['B'];

			// Event Date Time overrides.
			$css_template .= "
				.tribe-events-schedule h2,
				$tribe_events .tribe-events-calendar-list__event-datetime,
				$tribe_events .tribe-events-calendar-day__event-datetime,
				$tribe_events .tribe-events-calendar-latest-past__event-datetime,
				$tribe_events .tribe-events-widget-events-list__event-datetime {
					color: <%= global_elements.event_date_time_color %>;
				}

				$tribe_events .tribe-events-calendar-month__calendar-event-datetime,
				$tribe_events .tribe-events-calendar-month__day--past .tribe-events-calendar-month__calendar-event-datetime,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-datetime,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-datetime {
					color: rgba({$date_css_rgb}, .88);
				}
			";
		}

		if ( $customizer->has_option( $section->ID, 'link_color' ) ) {
			$css_template .= "
				.tribe-events-single-event-description a,
				.tribe-events-event-url a,
				.tribe-venue-url a,
				.tribe-organizer-url a,
				.tribe-block__organizer__website a,
				.tribe-block__venue__website a,
				$tribe_events p a {
					color: <%= global_elements.link_color %>;
				}
			";
		}

		if (
			$customizer->has_option( $section->ID, 'background_color_choice' )
			&& 'custom' === $customizer->get_option( [ $section->ID, 'background_color_choice' ] )
			&& $customizer->has_option( $section->ID, 'background_color' )
		) {
			$css_template .= '
				.tribe-events-view:not(.tribe-events-widget),
				#tribe-events,
				#tribe-events-pg-template {
					background-color: <%= global_elements.background_color %>;
				}
			';
		}

		if ( $customizer->has_option( $section->ID, 'accent_color' ) ) {
			$accent_color     = new \Tribe__Utils__Color( $settings['accent_color'] );
			$accent_color_rgb = $accent_color::hexToRgb( $settings['accent_color'] );
			$accent_css_rgb   = $accent_color_rgb['R'] . ',' . $accent_color_rgb['G'] . ',' . $accent_color_rgb['B'];

			$accent_color_hover          = 'rgba(' . $accent_css_rgb . ',0.8)';
			$accent_color_active         = 'rgba(' . $accent_css_rgb . ',0.9)';
			$accent_color_background     = 'rgba(' . $accent_css_rgb . ',0.07)';
			$accent_color_multiday       = 'rgba(' . $accent_css_rgb . ',0.24)';
			$accent_color_multiday_hover = 'rgba(' . $accent_css_rgb . ',0.34)';
			$color_background            = '#ffffff';

			// overrides for common base/full/forms/_toggles.pcss.
			$css_template .= "
				$tribe_common .tribe-common-form-control-toggle__input:checked {
					background-color: <%= global_elements.accent_color %>;
				}

				.tribe-common.tribe-events-widget .tribe-events-widget-events-list__view-more-link {
					color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for the widget view more link
			$css_template .= '
				.tribe-common.tribe-events-widget .tribe-events-widget-events-list__view-more-link:active,
				.tribe-common.tribe-events-widget .tribe-events-widget-events-list__view-more-link:focus,
				.tribe-common.tribe-events-widget .tribe-events-widget-events-list__view-more-link:hover {
					border-bottom-color: <%= global_elements.accent_color %>;
				}
			';

			// Theme overrides for widget view more link
			$css_template .= '
				.tribe-theme-twentyseventeen .tribe-events-widget .tribe-events-widget-events-list__view-more-link,
				.tribe-theme-twentytwentyone .tribe-events-widget .tribe-events-widget-events-list__view-more-link,
				.tribe-theme-twentyseventeen .site-footer .widget-area .tribe-events-widget .tribe-events-widget-events-list__view-more-link,
				.site-footer .widget-area .tribe-events-widget .tribe-events-widget-events-list__view-more-link {
					color: <%= global_elements.accent_color %>;
				}
			';

			// Widget featured icon color
			$css_template .= '
				.tribe-events-widget .tribe-events-widget-events-list__event-row--featured .tribe-events-widget-events-list__event-date-tag-datetime:after {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for common base/full/typography/_ctas.pcss.
			$css_template .= "
				$tribe_common .tribe-common-cta--alt,
				$tribe_common .tribe-common-cta--alt:active,
				$tribe_common .tribe-common-cta--alt:hover,
				$tribe_common .tribe-common-cta--alt:focus,
				$tribe_common .tribe-common-cta--thin-alt,
				$tribe_common .tribe-common-cta--thin-alt:active,
				$tribe_common .tribe-common-cta--thin-alt:focus,
				$tribe_common .tribe-common-cta--thin-alt:hover {
					color: <%= global_elements.accent_color %>;
					border-bottom-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				$tribe_common .tribe-common-cta--alt:active,
				$tribe_common .tribe-common-cta--alt:hover,
				$tribe_common .tribe-common-cta--alt:focus,
				$tribe_common .tribe-common-cta--thin-alt:active,
				$tribe_common .tribe-common-cta--thin-alt:hover,
				$tribe_common .tribe-common-cta--thin-alt:focus,
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-cta--alt:hover,
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-cta--alt:focus,
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-cta--thin-alt:hover,
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-cta--thin-alt:focus {
					color: <%= global_elements.accent_color %>;
				}
			";

			// Overrides for common components/full/buttons/_border.pcss.
			$css_template .= "
				$tribe_common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt),
				$tribe_common a.tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt) {
					border-color: <%= global_elements.accent_color %>;
					color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				$tribe_common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):focus,
				$tribe_common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):hover,
				$tribe_common a.tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):focus,
				$tribe_common a.tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):hover {
					color: $color_background;
					background-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):focus,
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):hover {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for common components/full/buttons/_solid.pcss.
			$css_template .= "
				$tribe_common .tribe-common-c-btn,
				$tribe_common a.tribe-common-c-btn {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				$tribe_common .tribe-common-c-btn:focus,
				$tribe_common .tribe-common-c-btn:hover,
				$tribe_common a.tribe-common-c-btn:focus,
				$tribe_common a.tribe-common-c-btn:hover {
					background-color: $accent_color_hover;
				}
			";

			$css_template .= "
				$tribe_common .tribe-common-c-btn:active,
				$tribe_common a.tribe-common-c-btn:active {
					background-color: $accent_color_active;
				}
			";

			$css_template .= "
				$tribe_common .tribe-common-c-btn:disabled,
				$tribe_common a.tribe-common-c-btn:disabled {
					background-color: $accent_color_background;
				}
			";

			// Override svg icons color.
			$css_template .= "
				$tribe_common .tribe-common-c-svgicon {
					color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				$tribe_common .tribe-events-virtual-virtual-event__icon-svg {
					color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				.tribe-theme-twentytwenty $tribe_common .tribe-common-c-btn {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-c-btn:hover,
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-c-btn:focus,
				.tribe-theme-twentytwenty $tribe_common .tribe-common-c-btn:hover,
				.tribe-theme-twentytwenty $tribe_common .tribe-common-c-btn:focus {
					background-color: $accent_color_hover;
				}
			";

			// overrides for tec components/full/_datepicker.pcss.
			$css_template .= "
				$tribe_events .datepicker .day.current,
				$tribe_events .datepicker .month.current,
				$tribe_events .datepicker .year.current,
				$tribe_events .datepicker .day.current:hover,
				$tribe_events .datepicker .day.current:focus,
				$tribe_events .datepicker .day.current.focused,
				$tribe_events .datepicker .month.current:hover,
				$tribe_events .datepicker .month.current:focus,
				$tribe_events .datepicker .month.current.focused,
				$tribe_events .datepicker .year.current:hover,
				$tribe_events .datepicker .year.current:focus,
				$tribe_events .datepicker .year.current.focused {
					background: $accent_color_background;
				}
			";

			$css_template .= "
				$tribe_events .datepicker .day.active,
				$tribe_events .datepicker .month.active,
				$tribe_events .datepicker .year.active,
				$tribe_events .datepicker .day.active:hover,
				$tribe_events .datepicker .day.active:focus,
				$tribe_events .datepicker .day.active.focused,
				$tribe_events .datepicker .month.active:hover,
				$tribe_events .datepicker .month.active:focus,
				$tribe_events .datepicker .month.active.focused,
				$tribe_events .datepicker .year.active:hover,
				$tribe_events .datepicker .year.active:focus,
				$tribe_events .datepicker .year.active.focused {
					background: <%= global_elements.accent_color %>;
				}
			";

			// overrides for tec components/full/_events-bar.pcss.
			$css_template .= "
				$tribe_events .tribe-events-c-events-bar__search-button:before {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for tec components/full/_ical-link.pcss.
			$css_template .= "
				$tribe_events .tribe-events-c-ical__link {
					border-color: <%= global_elements.accent_color %>;
					color: <%= global_elements.accent_color %>;
				}
			";

			/* @todo replace this with the variable var(--color-background) when we make those available */
			$css_template .= "
				$tribe_events .tribe-events-c-ical__link:hover,
				$tribe_events .tribe-events-c-ical__link:focus,
				$tribe_events .tribe-events-c-ical__link:active {
					color: #fff;
					background-color: <%= global_elements.accent_color %>;
					border-color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for tec components/full/_view-selector.pcss.
			$css_template .= "
				$tribe_events .tribe-events-c-view-selector__button:before {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for tec views/full/list/_event.pcss.
			$css_template .= "
				$tribe_events .tribe-events-calendar-list__event-row--featured .tribe-events-calendar-list__event-date-tag-datetime:after {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				.tribe-common--breakpoint-medium$tribe_events .tribe-events-calendar-list__event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for tec views/full/month/_calendar-event.pcss.
			$css_template .= "
				$tribe_events .tribe-events-calendar-month__calendar-event--featured:before {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for tec views/full/month/_day.pcss.
			$css_template .= "
				$tribe_events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date,
				$tribe_events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link {
					color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				$tribe_events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:hover,
				$tribe_events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:focus {
					color: $accent_color_hover;
				}
			";

			$css_template .= "
				$tribe_events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:active {
					color: $accent_color_active;
				}
			";

			$css_template .= "
				$tribe_events .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past ) .tribe-events-calendar-month__day-cell--selected,
				$tribe_events .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past ) .tribe-events-calendar-month__day-cell--selected:hover,
				$tribe_events .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past ) .tribe-events-calendar-month__day-cell--selected:focus,
				$tribe_events.tribe-events-widget .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past ) .tribe-events-calendar-month__day-cell--selected,
				$tribe_events.tribe-events-widget .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past ) .tribe-events-calendar-month__day-cell--selected:hover,
				$tribe_events.tribe-events-widget .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past ) .tribe-events-calendar-month__day-cell--selected:focus {
					background-color: <%= global_elements.accent_color %>;
				}

				$tribe_events .tribe-events-calendar-month__day-cell--selected .tribe-events-calendar-month__day-date {
					color: $color_background;
				}

				$tribe_events .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past ) .tribe-events-calendar-month__mobile-events-icon--event,
				$tribe_events.tribe-events-widget .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past ) .tribe-events-calendar-month__mobile-events-icon--event {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:hover,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:focus {
					color: $accent_color_hover;
				}
			";

			$css_template .= "
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:active {
					color: $accent_color_active;
				}
			";

			$css_template .= "
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__day-cell--selected:hover,
				.tribe-theme-twentyseventeen $tribe_events .tribe-events-calendar-month__day-cell--selected:focus {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				.tribe-theme-twentytwenty $tribe_events .tribe-events-calendar-month__day-cell--selected {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for tec views/full/month/_mobile-events.pcss.
			$css_template .= "
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			";

			// overrides for tec views/full/month/_multiday-events.pcss.
			$css_template .= "
				$tribe_events .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past )  .tribe-events-calendar-month__multiday-event-bar-inner {
					background-color: $accent_color_multiday;
				}
			";

			$css_template .= "
				$tribe_events .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past )  .tribe-events-calendar-month__multiday-event-bar-inner--hover,
				$tribe_events .tribe-events-calendar-month__day:not( .tribe-events-calendar-month__day--past )  .tribe-events-calendar-month__multiday-event-bar-inner--focus {
					background-color: $accent_color_multiday_hover;
				}
			";

			// overrides for tec views/full/day/_event.pcss.
			$css_template .= "
				$tribe_events .tribe-events-calendar-day__event--featured:after {
					background-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= "
				.tribe-common--breakpoint-medium$tribe_events .tribe-events-calendar-day__event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			";

			// Single Event styles overrides
			// This is under filter_global_elements_css_template() in order to have
			// access to global_elements.accent_color, which is under a different section.
			if ( $this->should_add_single_view_v2_styles() ) {
				$css_template .= '
					.tribe-events-cal-links .tribe-events-gcal,
					.tribe-events-cal-links .tribe-events-ical,
					.tribe-events-event-meta a,
					.tribe-events-event-meta a:active,
					.tribe-events-event-meta a:visited,
					.tribe-events-schedule .recurringinfo a,
					.tribe-related-event-info .recurringinfo a,
					.tribe-events-single ul.tribe-related-events li .tribe-related-events-title a,
					.tribe-events-single-event-description a:active,
					.tribe-events-single-event-description a:focus,
					.tribe-events-single-event-description a:hover {
						color: <%= global_elements.accent_color %>;
					}

					.tribe-events-event-meta a:focus,
					.tribe-events-event-meta a:hover {
						color: ' . $accent_color_hover . ';
					}

					.tribe-events-virtual-link-button {
						background-color: <%= global_elements.accent_color %>;
					}

					.tribe-events-virtual-link-button:active,
					.tribe-events-virtual-link-button:focus,
					.tribe-events-virtual-link-button:hover {
						background-color: ' . $accent_color_hover . ';
					}

					.tribe-events-single-event-description a,
					.tribe-events-single-event-description a:active,
					.tribe-events-single-event-description a:focus,
					.tribe-events-single-event-description a:hover,
					.tribe-events-content blockquote {
						border-color: <%= global_elements.accent_color %>;
					}
				';
			}
		}

		return $css_template;
	}

	/**
	 * Filters the Single Event section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 5.3.1
	 *
	 * @param string                      $css_template The CSS template, as produced by the Single Event.
	 * @param \Tribe__Customizer__Section $section      The Single Event section.
	 * @param \Tribe__Customizer|null     $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_single_event_css_template( $css_template, $section, $customizer = null ) {
		if ( null === $customizer ) {
			$customizer = tribe( 'customizer' );
		}

		if (
			$customizer->has_option( $section->ID, 'post_title_color_choice' )
			&& 'custom' === $customizer->get_option( [ $section->ID, 'post_title_color_choice' ] )
			&& $customizer->has_option( $section->ID, 'post_title_color' )
		) {
			$css_template .= '
				.single-tribe_events .tribe-events-single-event-title {
					color: <%= single_event.post_title_color %>;
				}
			';
		}

		return $css_template;
	}



	/**
	 * Check whether the Single Event styles overrides can be applied
	 *
	 * @return false/true
	 */
	public function should_add_single_view_v2_styles() {
		// Use the function from provider.php to check if V2 is not enabled
		// or the TRIBE_EVENTS_SINGLE_VIEW_V2_DISABLED constant is true.
		if ( ! tribe_events_single_view_v2_is_enabled() ) {
			return false;
		}

		// Bail if not Single Event.
		if ( ! tribe( Bootstrap::class )->is_single_event() ) {
			return false;
		}

		// Bail if Block Editor.
		if ( tribe( 'editor' )->should_load_blocks() && has_blocks( get_queried_object_id() ) ) {
			return false;
		}

		return true;
	}
}
