<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Global Elements
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since 4.4
 */
final class Tribe__Events__Customizer__Global_Elements extends Tribe__Customizer__Section {
	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 *
	 * Note: This is the only required method for a Connector to work
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance() {
		return tribe( 'tec.customizer.global-elements' );
	}

	/**
	 * Grab the CSS rules template
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Customizer::instance();
		$settings   = $customizer->get_option( [ $this->ID ] );

		if ( $customizer->has_option( $this->ID, 'accent_color' ) ) {

			$accent_color     = new Tribe__Utils__Color( $settings['accent_color'] );
			$accent_color_rgb = $accent_color::hexToRgb( $settings['accent_color'] );
			$accent_css_rgb   = $accent_color_rgb['R'] . ',' . $accent_color_rgb['G'] . ',' . $accent_color_rgb['B'];

			$accent_color_hover          = 'rgba(' . $accent_css_rgb . ',0.8)';
			$accent_color_active         = 'rgba(' . $accent_css_rgb . ',0.9)';
			$accent_color_background     = 'rgba(' . $accent_css_rgb . ',0.07)';
			$accent_color_multiday       = 'rgba(' . $accent_css_rgb . ',0.24)';
			$accent_color_multiday_hover = 'rgba(' . $accent_css_rgb . ',0.34)';
			$color_background            = '#ffffff';

			// overrides for common base/full/forms/_toggles.pcss.
			$template .= '
				.tribe-common .tribe-common-form-control-toggle__input:checked {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for common base/full/typography/_ctas.pcss.
			$template .= '
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

			$template .= '
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

			// overrides for common components/full/buttons/_solid.pcss.
			$template .= '
				.tribe-common .tribe-common-c-btn,
				.tribe-common a.tribe-common-c-btn {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-common .tribe-common-c-btn:focus,
				.tribe-common .tribe-common-c-btn:hover,
				.tribe-common a.tribe-common-c-btn:focus,
				.tribe-common a.tribe-common-c-btn:hover {
					background-color: ' . $accent_color_hover . ';
				}
			';

			$template .= '
				.tribe-common .tribe-common-c-btn:active,
				.tribe-common a.tribe-common-c-btn:active {
					background-color: ' . $accent_color_active . ';
				}
			';

			$template .= '
				.tribe-common .tribe-common-c-btn:disabled,
				.tribe-common a.tribe-common-c-btn:disabled {
					background-color: ' . $accent_color_background . ';
				}
			';

			$template .= '
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-c-btn:hover,
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-c-btn:focus,
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn:hover,
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn:focus {
					background-color: ' . $accent_color_hover . ';
				}
			';

			// overrides for common components/full/_loader.pcss.
			$template .= '
				@keyframes tribe-common-c-loader-bounce {
					0% {}
					50% { background-color: <%= global_elements.accent_color %>; }
					100% {}
				}
			';

			// overrides for tec components/full/_datepicker.pcss.
			$template .= '
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

			$template .= '
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
			$template .= '
				.tribe-events .tribe-events-c-events-bar__search-button:before {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec components/full/_ical-link.pcss.
			$template .= '
				.tribe-events .tribe-events-c-ical__link {
					border-color: <%= global_elements.accent_color %>;
					color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-events .tribe-events-c-ical__link:hover,
				.tribe-events .tribe-events-c-ical__link:focus,
				.tribe-events .tribe-events-c-ical__link:active {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec components/full/_view-selector.pcss.
			$template .= '
				.tribe-events .tribe-events-c-view-selector__button:before {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/list/_event.pcss.
			$template .= '
				.tribe-events .tribe-events-calendar-list__event-row--featured .tribe-events-calendar-list__event-date-tag-datetime:after {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-calendar-list__event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/month/_calendar-event.pcss.
			$template .= '
				.tribe-events .tribe-events-calendar-month__calendar-event--featured:before {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/month/_day.pcss.
			$template .= '
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date,
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link {
					color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:hover,
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:focus {
					color: ' . $accent_color_hover . ';
				}
			';

			$template .= '
				.tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:active {
					color: ' . $accent_color_active . ';
				}
			';

			$template .= '
				.tribe-events .tribe-events-calendar-month__day-cell--selected,
				.tribe-events .tribe-events-calendar-month__day-cell--selected:hover,
				.tribe-events .tribe-events-calendar-month__day-cell--selected:focus {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-events .tribe-events-calendar-month__day-cell--selected .tribe-events-calendar-month__day-date {
					color: ' . $color_background . ';
				}
			';

			$template .= '
				.tribe-events .tribe-events-calendar-month__mobile-events-icon--event {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:focus {
					color: ' . $accent_color_hover . ';
				}
			';

			$template .= '
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day--current .tribe-events-calendar-month__day-date-link:active {
					color: ' . $accent_color_active . ';
				}
			';

			$template .= '
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day-cell--selected:hover,
				.tribe-theme-twentyseventeen .tribe-events .tribe-events-calendar-month__day-cell--selected:focus {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-theme-twentytwenty .tribe-events .tribe-events-calendar-month__day-cell--selected {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/month/_mobile-events.pcss.
			$template .= '
				.tribe-events .tribe-events-calendar-month-mobile-events__mobile-event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			';

			// overrides for tec views/full/month/_multiday-events.pcss.
			$template .= '
				.tribe-events .tribe-events-calendar-month__multiday-event-bar-inner {
					background-color: ' . $accent_color_multiday . ';
				}
			';

			$template .= '
				.tribe-events .tribe-events-calendar-month__multiday-event-bar-inner--hover,
				.tribe-events .tribe-events-calendar-month__multiday-event-bar-inner--focus {
					background-color: ' . $accent_color_multiday_hover . ';
				}
			';

			// overrides for tec views/full/day/_event.pcss.
			$template .= '
				.tribe-events .tribe-events-calendar-day__event--featured:after {
					background-color: <%= global_elements.accent_color %>;
				}
			';

			$template .= '
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-calendar-day__event-datetime-featured-text {
					color: <%= global_elements.accent_color %>;
				}
			';
		}

		if ( tribe_events_views_v2_is_enabled() && $customizer->has_option( $this->ID, 'link_color' ) ) {
			$template .= '
				.tribe-events-single-event-description a,
				.tribe-events-event-url a,
				.tribe-venue-url a,
				.tribe-organizer-url a,

				.tribe-events-pro .tribe-events-pro-organizer__meta-website a,
				.tribe-block__organizer__website a,
				.tribe-events-pro .tribe-events-pro-venue__meta-website a,
				.tribe-block__venue__website a,
				.tribe_events p a {
					color: <%= global_elements.link_color %>;
				}
			';
		} elseif ( $customizer->has_option( $this->ID, 'link_color' ) ) {
			$template .= '
				#tribe-events-content a,
				.tribe-events-event-meta a {
					color: <%= global_elements.link_color %>;
				}
			';
		}


		if ( tribe_events_views_v2_is_enabled() ) {
			return $template;
		}

		if ( $customizer->has_option( $this->ID, 'filterbar_color' ) ) {
			$template .= '
				#tribe-bar-form {
					background-color: <%= global_elements.filterbar_color %>;
				}

				#tribe-bar-views .tribe-bar-views-inner {
					background-color: <%= global_elements.filterbar_color_darker %>;
				}

				#tribe-bar-collapse-toggle {
					background-color: transparent;
				}

				#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a {
					background-color: <%= global_elements.filterbar_color_darker %>;
				}

				#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option.tribe-bar-active a:hover {
					background-color: transparent;
				}

				#tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a:hover {
					background-color: <%= global_elements.filterbar_color %>;
				}

				#tribe-bar-form .tribe-bar-submit input[type=submit] {
					background-color: <%= global_elements.filterbar_color_darkest %>;
				}

				#tribe-bar-form input[type="text"] {
					border-bottom-color: <%= global_elements.filterbar_color_darkest %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'button_color' ) ) {
			$template .= '
				#tribe_events_filters_wrapper input[type=submit],
				.tribe-events-button,
				#tribe-events .tribe-events-button,
				.tribe-events-button.tribe-inactive,
				#tribe-events .tribe-events-button:hover,
				.tribe-events-button:hover,
				.tribe-events-button.tribe-active:hover {
					background-color: <%= global_elements.button_color %>;
				}
			';
		}

		return $template;
	}

	public function create_ghost_settings( $settings = array() ) {
		if ( ! empty( $settings['filterbar_color'] ) ) {
			$settings['filterbar_color_darker'] = new Tribe__Utils__Color( $settings['filterbar_color'] );
			$settings['filterbar_color_darker'] = '#' . $settings['filterbar_color_darker']->darken();

			$settings['filterbar_color_darkest'] = new Tribe__Utils__Color( $settings['filterbar_color'] );
			$settings['filterbar_color_darkest'] = '#' . $settings['filterbar_color_darkest']->darken( 30 );
		}

		return $settings;
	}

	public function setup() {
		$views_v2_is_enabled = tribe_events_views_v2_is_enabled();
		$title               = $views_v2_is_enabled ? esc_html__( 'General', 'the-events-calendar' ) : esc_html__( 'Global Elements', 'the-events-calendar' );
		$description         = $views_v2_is_enabled ? '' : esc_html__( 'Options selected here will override what was selected in the "General Theme" section.', 'the-events-calendar' );

		$this->defaults = [
			'link_color'           => '#141827',
		];

		$this->arguments = array(
			'priority'    => 20,
			'capability'  => 'edit_theme_options',
			'title'       => $title,
			'description' => $description,
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
				'default'              => '#334AFF',
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

		$customizer->add_setting_name( $customizer->get_setting_name( 'accent_color', $section ) );

		$manager->add_setting(
			$customizer->get_setting_name( 'link_color', $section ),
			array(
				'default'              => $this->get_default( 'link_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'link_color', $section ),
				array(
					'label'   => esc_html__( 'Link Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		$customizer->add_setting_name( $customizer->get_setting_name( 'link_color', $section ) );

		if ( tribe_events_views_v2_is_enabled() ) {
			return;
		}

		$manager->add_setting(
			$customizer->get_setting_name( 'filterbar_color', $section ),
			array(
				'default'              => $this->get_default( 'filterbar_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'filterbar_color', $section ),
				array(
					'label'   => esc_html__( 'Filter Bar Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'button_color', $section ),
			array(
				'default'              => $this->get_default( 'button_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'button_color', $section ),
				array(
					'label'   => esc_html__( 'Button Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		// Custom Map Pins are not supported with basic embeds.
		if ( ! tribe_is_using_basic_gmaps_api() ) {

			$manager->add_setting(
				$customizer->get_setting_name( 'map_pin', $section ),
				array(
					'default'              => $this->get_default( 'map_pin' ),
					'type'                 => 'option',
					'sanitize_callback'    => 'esc_url_raw',
				)
			);

			$manager->add_control(
				new WP_Customize_Image_Control(
					$manager,
					$customizer->get_setting_name( 'map_pin', $section ),
					array(
						'default'    => $this->get_default( 'button_color' ),
						'label'      => esc_html__( 'Map Pin', 'the-events-calendar' ),
						'section'    => $section->id,
					)
				)
			);
		}

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'filterbar_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'button_color', $section ) );

		// To add Live Edit Pins will require some JS refactor to be able to work
		// $customizer->add_setting_name( $customizer->get_setting_name( 'map_pin', $section ) );
	}
}
