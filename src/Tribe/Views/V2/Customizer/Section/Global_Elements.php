<?php
/**
 * The Events Calendar Customizer Section Class
 * Global Elements
 *
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Customizer\Section;
/**
 * Global Elements
 *
 * @since TBD
 */
final class Global_Elements extends \Tribe__Customizer__Section {

	/**
	 * ID of the section.
	 *
	 * @since TBD
	 *
	 * @access public
	 * @var string
	 */
	public $ID = 'global_elements';

	/**
	 * Allows sections to be loaded in order for overrides.
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
	public function setup_arguments() {
		return [
			'priority'	=> 1,
			'capability'  => 'edit_theme_options',
			'title'	   => esc_html__( 'Global Elements', 'the-events-calendar' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_defaults() {
		return [
			'event_title_color'       => '#141827',
			'event_date_time_color'   => '#141827',
			'background_color_choice' => 'transparent',
			'background_color'        => '',
			'accent_color'            => '#334aff',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_settings() {
		return [
			'event_title_color'       => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'event_date_time_color'   => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'background_color_choice' => [
				'sanitize_callback'	   => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'background_color'        => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'accent_color'            => [
				'sanitize_callback'	   => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
		];
	}

	public function setup_content_headings() {
		return [
			'font_color' => [
				'priority'	 => 0,
				'type'		 => 'heading',
				'label'    => esc_html__( 'Set Font Colors', 'the-events-calendar' ),
			],
			'global_elements_separator' => [
				'priority'	 => 15,
				'type'		 => 'separator',
			],
			'adjust_appearance' => [
				'priority'	 => 21,
				'type'		 => 'heading',
				'label'    => esc_html__( 'Adjust Appearance', 'the-events-calendar' ),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_controls() {
		$customizer = tribe( 'customizer' );
		return [
			'event_title_color' => [
				'priority' => 5,
				'type'     => 'color',
				'label'    => esc_html_x(
					'Event Title',
					'The event title color setting label.',
					'the-events-calendar'
				),
			],
			'event_date_time_color' => [
				'priority' => 10,
				'type'     => 'color',
				'label'    => esc_html_x(
					'Event Date and Time',
					'The event title color setting label.',
					'the-events-calendar'
				),
				'description' => esc_html_x(
					'Main date and time display on views and single event pages',
					'The description for the event date and time color setting.',
					'the-events-calendar'
				),
			],
			'background_color_choice' => [
				'priority'    => 25,
				'type'        => 'radio',
				'label'       => esc_html__( 'Background Color', 'the-events-calendar' ),
				'description' => esc_html__( 'All calendar and event pages', 'the-events-calendar' ),
				'choices'     => [
					'transparent' => _x(
						'Transparent.',
						'Label for option to leave transparent (default).',
						'the-events-calendar'
					),
					'custom'	  => esc_html_x(
						'Select Custom Color',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				],
			],
			'background_color' => [
				'priority' => 26, // Should come right after background_color_choice
				'type'     => 'color',
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'background_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return $this->defaults['background_color_choice'] !== $value;
				},
			],
			'accent_color' => [
				'priority' => 30,
				'type'     => 'color',
				'label'    => esc_html_x(
					'Accent Color',
					'The event accent color setting label.',
					'the-events-calendar'
				),
			],
		];
	}

	/**
	 * Filters the Global Elements section CSS template to add Views v2 related style templates to it.
	 *
	 * Please note: the order is important for proper cascading overrides!
	 *
	 * @since 5.3.1
	 *
	 * @param string                      $css_template The current CSS template, as produced by the Section.
	 * @param \Tribe__Customizer__Section $section      The Global Elements section.
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function get_css_template( $css_template ) {
		// For sanity's sake.
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return $css_template;
		}

		// Bail early, and often.
		if (
			! $this->should_include_setting_css( 'event_title_color' )
			&& ! $this->should_include_setting_css( 'event_date_time_color' )
			&& ! $this->should_include_setting_css( 'accent_color' )
		) {
			return $css_template;
		}

		// These allow us to continue to _not_ target the shortcode.
		$apply_to_shortcode = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );

		$tribe_events = $apply_to_shortcode ? '.tribe-events' : '.tribe-events:not( .tribe-events-view--shortcode )';
		$tribe_common = $apply_to_shortcode ? '.tribe-common' : '.tribe-common:not( .tribe-events-view--shortcode )';

		// Accent color overrides.
		if ( $this->should_include_setting_css( 'accent_color' ) ) {
			$accent_color_obj            = new \Tribe__Utils__Color( $this->get_option( 'accent_color' ) );
			$accent_color_arr            = $accent_color_obj->getRgb();
			$accent_color_rgb            = $accent_color_arr['R'] . ',' . $accent_color_arr['G'] . ',' . $accent_color_arr['B'];
			$accent_color_hover          = 'rgba(' . $accent_color_rgb . ',0.8)';
			$accent_color_active         = 'rgba(' . $accent_color_rgb . ',0.9)';
			$accent_color_background     = 'rgba(' . $accent_color_rgb . ',0.07)';
			$accent_color_multiday       = 'rgba(' . $accent_color_rgb . ',0.24)';
			$accent_color_multiday_hover = 'rgba(' . $accent_color_rgb . ',0.34)';
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

			$css_template .= "
				.tribe-theme-twentytwentyone $tribe_common .tribe-common-c-btn:not(:hover):not(:active) {
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
		}

		// Event Title overrides.
		if ( $this->should_include_setting_css( 'event_title_color' ) ) {
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

		// Background color overrides.
		if ( $this->should_include_setting_css( 'background_color_choice' ) ) {
			if ( $this->should_include_setting_css( 'background_color' ) ) {
				$css_template .= '
					.tribe-events-view:not(.tribe-events-widget),
					#tribe-events,
					#tribe-events-pg-template {
						background-color: <%= global_elements.background_color %>;
					}
				';
			}
		}

		// Event Date/Time overrides.
		if ( $this->should_include_setting_css( 'event_date_time_color' ) ) {
			// Event Date Time overrides.
			$css_template .= "
				.tribe-events-schedule h2,
				$tribe_events .tribe-events-calendar-list__event-datetime,
				$tribe_events .tribe-events-calendar-day__event-datetime,
				$tribe_events .tribe-events-calendar-month__calendar-event-datetime,
				$tribe_events .tribe-events-calendar-month__day--past .tribe-events-calendar-month__calendar-event-datetime,
				$tribe_events .tribe-events-calendar-month__calendar-event-tooltip-datetime,
				$tribe_events .tribe-events-calendar-month-mobile-events__mobile-event-datetime,
				$tribe_events .tribe-events-calendar-latest-past__event-datetime {
					color: <%= global_elements.event_date_time_color %>;
				}
			";
		}

		return $css_template;
	}
}
