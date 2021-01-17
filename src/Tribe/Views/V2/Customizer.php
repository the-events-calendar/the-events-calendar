<?php
/**
 * Handles Views v2 Customizer settings.
 *
 * @since   5.3.1
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;
use \WP_Customize_Control as Control;
use \WP_Customize_Color_Control as Color_Control;

/**
 * Class Customizer
 *
 * @since   5.3.1
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
	 * @param \Tribe__Customizer          $customizer The Customizer object.
	 */
	public function include_global_elements_settings( $section, $manager, $customizer ) {
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
				'default'              => '#141827',
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
					'description' => esc_html__( 'Main date and time display on views and single event pages.', 'the-events-calendar' ),
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
					'label'       => 'Background Color',
					'section'     => $section->id,
					'description' => esc_html__( 'All calendar and event pages.', 'the-events-calendar' ),
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
	 * @param \Tribe__Customizer          $customizer The Customizer object.
	 */
	public function include_single_event_settings( $section, $manager, $customizer ) {
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
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_global_elements_css_template( $css_template, $section, $customizer ) {
		$settings = $customizer->get_option( [ $section->ID ] );

		if ( $customizer->has_option( $section->ID, 'event_title_color' ) ) {
			// Event Title overrides.
			$css_template .= '
				.single-tribe_events .tribe-events-single-event-title,
				.tribe-events .tribe-events-calendar-list__event-title-link,
				.tribe-events .tribe-events-calendar-list__event-title-link:active,
				.tribe-events .tribe-events-calendar-list__event-title-link:visited,
				.tribe-events .tribe-events-calendar-list__event-title-link:hover,
				.tribe-events .tribe-events-calendar-list__event-title-link:focus,
				.tribe-events .tribe-events-calendar-month__calendar-event-title-link,
				.tribe-events .tribe-events-calendar-month__calendar-event-title-link:active,
				.tribe-events .tribe-events-calendar-month__calendar-event-title-link:visited,
				.tribe-events .tribe-events-calendar-month__calendar-event-title-link:hover,
				.tribe-events .tribe-events-calendar-month__calendar-event-title-link:focus,
				.tribe-events .tribe-events-calendar-month__multiday-event-bar-title,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:active,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:visited,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:hover,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:focus,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:active,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:visited,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:hover,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:focus,
				.tribe-events .tribe-events-calendar-day__event-title-link,
				.tribe-events .tribe-events-calendar-day__event-title-link:active,
				.tribe-events .tribe-events-calendar-day__event-title-link:visited,
				.tribe-events .tribe-events-calendar-day__event-title-link:hover,
				.tribe-events .tribe-events-calendar-day__event-title-link:focus,
				.tribe-events .tribe-events-calendar-latest-past__event-title-link,
				.tribe-events .tribe-events-calendar-latest-past__event-title-link:active,
				.tribe-events .tribe-events-calendar-latest-past__event-title-link:visited,
				.tribe-events .tribe-events-calendar-latest-past__event-title-link:hover,
				.tribe-events .tribe-events-calendar-latest-past__event-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-list__event-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-list__event-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__calendar-event-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__calendar-event-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-day__event-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-day__event-title-link:focus,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-latest-past__event-title-link:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-latest-past__event-title-link:focus,
				.tribe-theme-enfold#top .tribe-events .tribe-events-calendar-list__event-title-link,
				.tribe-theme-enfold#top .tribe-events .tribe-events-calendar-month__calendar-event-title-link,
				.tribe-theme-enfold#top .tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link,
				.tribe-theme-enfold#top .tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link,
				.tribe-theme-enfold#top .tribe-events .tribe-events-calendar-day__event-title-link,
				.tribe-theme-enfold#top .tribe-events .tribe-events-calendar-latest-past__event-title-link {
					color: <%= global_elements.event_title_color %>;
				}

				.tribe-events .tribe-events-calendar-list__event-title-link:active,
				.tribe-events .tribe-events-calendar-list__event-title-link:hover,
				.tribe-events .tribe-events-calendar-list__event-title-link:focus,
				.tribe-events .tribe-events-calendar-month__calendar-event-title-link:active,
				.tribe-events .tribe-events-calendar-month__calendar-event-title-link:hover,
				.tribe-events .tribe-events-calendar-month__calendar-event-title-link:focus,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:active,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:hover,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-title-link:focus,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:active,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:hover,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-title-link:focus,
				.tribe-events .tribe-events-calendar-day__event-title-link:active,
				.tribe-events .tribe-events-calendar-day__event-title-link:hover,
				.tribe-events .tribe-events-calendar-day__event-title-link:focus,
				.tribe-events .tribe-events-calendar-latest-past__event-title-link:active,
				.tribe-events .tribe-events-calendar-latest-past__event-title-link:hover,
				.tribe-events .tribe-events-calendar-latest-past__event-title-link:focus {
					border-color: <%= global_elements.event_title_color %>;
				}
			';
		}

		if ( $customizer->has_option( $section->ID, 'event_date_time_color' ) ) {
			// Event Date Time overrides.
			$css_template .= '
				.tribe-events-schedule h2,
				.tribe-events .tribe-events-calendar-list__event-datetime,
				.tribe-events .tribe-events-calendar-day__event-datetime,
				.tribe-events .tribe-events-calendar-month__calendar-event-datetime,
				.tribe-events .tribe-events-calendar-month__day--past .tribe-events-calendar-month__calendar-event-datetime,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-datetime,
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-datetime,
				.tribe-events .tribe-events-calendar-latest-past__event-datetime {
					color: <%= global_elements.event_date_time_color %>;
				}
			';
		}

		if ( $customizer->has_option( $section->ID, 'link_color' ) ) {
			$css_template .= '
				.tribe-events-single-event-description a,
				.tribe-events-event-url a,
				.tribe-venue-url a,
				.tribe-organizer-url a,
				.tribe-block__organizer__website a,
				.tribe-block__venue__website a,
				.tribe_events p a {
					color: <%= global_elements.link_color %>;
				}
			';
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
			$css_template .= '
				.tribe-common .tribe-common-form-control-toggle__input:checked {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for common base/full/typography/_ctas.pcss.
			$css_template .= '
				.tribe-common .tribe-common-cta--alt,
				.tribe-common .tribe-common-cta--alt:active,
				.tribe-common .tribe-common-cta--alt:hover,
				.tribe-common .tribe-common-cta--alt:focus,
				.tribe-common .tribe-common-cta--thin-alt,
				.tribe-common .tribe-common-cta--thin-alt:active,
				.tribe-common .tribe-common-cta--thin-alt:focus,
				.tribe-common .tribe-common-cta--thin-alt:hover {
					border-bottom-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-common .tribe-common-cta--alt:active,
				.tribe-common .tribe-common-cta--alt:hover,
				.tribe-common .tribe-common-cta--alt:focus,
				.tribe-common .tribe-common-cta--thin-alt:active,
				.tribe-common .tribe-common-cta--thin-alt:hover,
				.tribe-common .tribe-common-cta--thin-alt:focus,
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-cta--alt:hover,
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-cta--alt:focus,
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-cta--thin-alt:hover,
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-cta--thin-alt:focus {
					color: <%= global_elements.accent_color %>;
				}
			';

			// Overrides for common components/full/buttons/_border.pcss.
			$css_template .= '
				.tribe-common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt),
				.tribe-common a.tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt) {
					border-color: <%= global_elements.accent_color %>;
					color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):focus,
				.tribe-common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):hover,
				.tribe-common a.tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):focus,
				.tribe-common a.tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):hover {
					color: ' . $color_background . ';
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):focus,
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):hover,
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for common components/full/buttons/_solid.pcss.
			$css_template .= '
				.tribe-common .tribe-common-c-btn,
				.tribe-common a.tribe-common-c-btn {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-common .tribe-common-c-btn:focus,
				.tribe-common .tribe-common-c-btn:hover,
				.tribe-common a.tribe-common-c-btn:focus,
				.tribe-common a.tribe-common-c-btn:hover {
					background-color: ' . $accent_color_hover . ';
				}
			';

			$css_template .= '
				.tribe-common .tribe-common-c-btn:active,
				.tribe-common a.tribe-common-c-btn:active {
					background-color: ' . $accent_color_active . ';
				}
			';

			$css_template .= '
				.tribe-common .tribe-common-c-btn:disabled,
				.tribe-common a.tribe-common-c-btn:disabled {
					background-color: ' . $accent_color_background . ';
				}
			';

			// Override svg icons color.
			$css_template .= '
				.tribe-common .tribe-common-c-svgicon {
					color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-common .tribe-events-virtual-virtual-event__icon-svg {
					color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-c-btn:hover,
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-c-btn:focus,
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn:hover,
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn:focus {
					background-color: ' . $accent_color_hover . ';
				}
			';

			// overrides for tec components/full/_datepicker.pcss.
			$css_template .= '
				.tribe-events .datepicker .day.current,
				.tribe-events .datepicker .month.current,
				.tribe-events .datepicker .year.current,
				.tribe-events .datepicker .day.current:hover,
				.tribe-events .datepicker .day.current:focus,
				.tribe-events .datepicker .day.current.focused,
				.tribe-events .datepicker .month.current:hover,
				.tribe-events .datepicker .month.current:focus,
				.tribe-events .datepicker .month.current.focused,
				.tribe-events .datepicker .year.current:hover,
				.tribe-events .datepicker .year.current:focus,
				.tribe-events .datepicker .year.current.focused {
					background: ' . $accent_color_background . ';
				}
			';

			$css_template .= '
				.tribe-events .datepicker .day.active,
				.tribe-events .datepicker .month.active,
				.tribe-events .datepicker .year.active,
				.tribe-events .datepicker .day.active:hover,
				.tribe-events .datepicker .day.active:focus,
				.tribe-events .datepicker .day.active.focused,
				.tribe-events .datepicker .month.active:hover,
				.tribe-events .datepicker .month.active:focus,
				.tribe-events .datepicker .month.active.focused,
				.tribe-events .datepicker .year.active:hover,
				.tribe-events .datepicker .year.active:focus,
				.tribe-events .datepicker .year.active.focused {
					background: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec components/full/_events-bar.pcss.
			$css_template .= '
				.tribe-events .tribe-events-c-events-bar__search-button:before {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec components/full/_ical-link.pcss.
			$css_template .= '
				.tribe-events .tribe-events-c-ical__link {
					border-color: <%= global_elements.accent_color %>;
					color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-events .tribe-events-c-ical__link:hover,
				.tribe-events .tribe-events-c-ical__link:focus,
				.tribe-events .tribe-events-c-ical__link:active {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec components/full/_view-selector.pcss.
			$css_template .= '
				.tribe-events .tribe-events-c-view-selector__button:before {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/list/_event.pcss.
			$css_template .= '
				.tribe-events .tribe-events-calendar-list__event-row--featured .tribe-events-calendar-list__event-date-tag-datetime:after {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-calendar-list__event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/month/_calendar-event.pcss.
			$css_template .= '
				.tribe-events .tribe-events-calendar-month__calendar-event--featured:before {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/month/_day.pcss.
			$css_template .= '
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date,
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link {
					color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:hover,
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:focus {
					color: ' . $accent_color_hover . ';
				}
			';

			$css_template .= '
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:active {
					color: ' . $accent_color_active . ';
				}
			';

			$css_template .= '
				.tribe-events .tribe-events-calendar-month__day-cell--selected,
				.tribe-events .tribe-events-calendar-month__day-cell--selected:hover,
				.tribe-events .tribe-events-calendar-month__day-cell--selected:focus {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-events .tribe-events-calendar-month__day-cell--selected .tribe-events-calendar-month__day-date {
					color: ' . $color_background . ';
				}
			';

			$css_template .= '
				.tribe-events .tribe-events-calendar-month__mobile-events-icon--event {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:focus {
					color: ' . $accent_color_hover . ';
				}
			';

			$css_template .= '
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:active {
					color: ' . $accent_color_active . ';
				}
			';

			$css_template .= '
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day-cell--selected:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day-cell--selected:focus {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-theme-twentytwenty .tribe-events .tribe-events-calendar-month__day-cell--selected {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/month/_mobile-events.pcss.
			$css_template .= '
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/month/_multiday-events.pcss.
			$css_template .= '
				.tribe-events .tribe-events-calendar-month__multiday-event-bar-inner {
					background-color: ' . $accent_color_multiday . ';
				}
			';

			$css_template .= '
				.tribe-events .tribe-events-calendar-month__multiday-event-bar-inner--hover,
				.tribe-events .tribe-events-calendar-month__multiday-event-bar-inner--focus {
					background-color: ' . $accent_color_multiday_hover . ';
				}
			';

			// overrides for tec views/full/day/_event.pcss.
			$css_template .= '
				.tribe-events .tribe-events-calendar-day__event--featured:after {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$css_template .= '
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-calendar-day__event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			';
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
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_single_event_css_template( $css_template, $section, $customizer ) {
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
}
