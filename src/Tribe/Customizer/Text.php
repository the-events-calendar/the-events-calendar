<?php
/**
 * The Events Calendar Customizer Section: Text.
 *
 * @package The Events Calendar
 * @since   TBD
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

final class Tribe__Events__Customizer__Text extends Tribe__Customizer__Section {

	/**
	 * Object instance.
	 *
	 * @return mixed|object|Tribe__Container The instance of the requested class.
	 */
	public static function instance() {
		return tribe( 'tec.customizer.text' );
	}

	/**
	 * Grab the CSS rules template.
	 *
	 * @since TBD
	 *
	 * @param string  $template
	 * @return string $template
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Customizer::instance();
		$settings   = $customizer->get_option( [ $this->ID ] );
		if ( $customizer->has_option( $this->ID, 'primary_text_color' ) ) {
			$primary_text_color     = new Tribe__Utils__Color( $settings['primary_text_color'] );
			$primary_text_color_rgb = $primary_text_color::hexToRgb( $settings['primary_text_color'] );

			$template .= '
				.tribe-common .tribe-common-b1,
				.tribe-common .tribe-common-b2,
				.tribe-common .tribe-common-b3 {
					color: <%= text.primary_text_color %>;
				}
			';

			$template .= '
				.tribe-common .tribe-common-h1,
				.tribe-common .tribe-common-h2,
				.tribe-common .tribe-common-h3,
				.tribe-common .tribe-common-h4,
				.tribe-common .tribe-common-h5,
				.tribe-common .tribe-common-h6,
				.tribe-common .tribe-common-h7,
				.tribe-common .tribe-common-h8 {
					color: <%= text.primary_text_color %>;
				}
			';

			$template .= '
				.tribe-common a,
				.tribe-common a:hover,
				.tribe-common a:focus,
				.tribe-common a:visited,
				.tribe-common .tribe-common-c-btn-border:hover,
				.tribe-common .tribe-common-c-btn-border:focus,
				.tribe-common a.tribe-common-c-btn-border:active,
				.tribe-common a.tribe-common-c-btn-border:focus,
				.tribe-common a.tribe-common-c-btn-border:hover,
				.tribe-theme-twentyseventeen .tribe-common a:hover,
				.tribe-theme-twentyseventeen .tribe-common a:focus {
					color: <%= text.primary_text_color %>;
				}
			';

			$template .= '
				.tribe-common .tribe-common-form-control-text__input,
				.tribe-common--breakpoint-medium.tribe-common .tribe-common-form-control-text__input {
					color: <%= text.primary_text_color %>;
				}
			';

			$template .= '
				.tribe-events .tribe-events-c-view-selector__list-item-text,
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-c-view-selector--labels .tribe-events-c-view-selector__button-text {
					color: <%= text.primary_text_color %>;
				}
			';

			$template .= '
				.tribe-events .datepicker .dow,
				.tribe-events .datepicker .day,
				.tribe-events .datepicker .month,
				.tribe-events .datepicker .year,
				.tribe-events .datepicker .datepicker-switch {
					color: <%= text.primary_text_color %>;
				}
			';

			$template .= '
				.tribe-common .tribe-common-anchor-thin:active,
				.tribe-common .tribe-common-anchor-thin:focus,
				.tribe-common .tribe-common-anchor-thin:hover {
					border-bottom-color: <%= text.primary_text_color %>;
				}
			';

			$template .= '
				.tribe-events .tribe-events-calendar-month__day-date-link,
				.tribe-events .tribe-events-calendar-month__calendar-event-tooltip-datetime {
					color: <%= text.primary_text_color %>;
				}
			';

			$primary_rgb = $primary_text_color_rgb['R'] . ',' . $primary_text_color_rgb['G'] . ',' . $primary_text_color_rgb['B'];

			$template .= '
				.tribe-events .datepicker .datepicker-switch:hover,
				.tribe-events .datepicker .datepicker-switch:focus,
				.tribe-events .tribe-events-c-view-selector__list-item-link:hover .tribe-events-c-view-selector__list-item-text,
				.tribe-events .tribe-events-c-view-selector__list-item-link:focus .tribe-events-c-view-selector__list-item-text {
					color: rgba( ' . $primary_rgb . ', 0.8 );
				}
			';

		}

		if ( $customizer->has_option( $this->ID, 'secondary_text_color' ) ) {
			$template .= '
				.tribe-common .tribe-common-form-control-toggle__label,
				.tribe-events .tribe-events-calendar-list__event-date-tag-weekday {
					color: <%= text.secondary_text_color %>;
				}
			';

			$template .= '
				.tribe-common .tribe-common-c-btn-border,
				.tribe-common a.tribe-common-c-btn-border {
					color: <%= text.secondary_text_color %>;
				}
			';

			$template .= '
				.tribe-common .tribe-common-form-control-text__input::placeholder {
					color: <%= text.secondary_text_color %>;
				}
			';

			$template .= '
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-c-nav__prev,
				.tribe-common--breakpoint-medium.tribe-events .tribe-events-c-nav__next {
					color: <%= text.secondary_text_color %>;
				}
			';

			$template .= '
				.tribe-events .tribe-events-calendar-month__calendar-event-datetime {
					color: <%= text.secondary_text_color %>;
				}
			';

		}

		if ( $customizer->has_option( $this->ID, 'link_color' ) ) {
			$template .= '
				#tribe-events-content a,
				.tribe-events-event-meta a {
					color: <%= text.link_color %>;
				}
			';

			$template .= '
				.tribe-common .tribe-common-b1 a:not(.tribe-events-c-small-cta__link):not(.tribe-events-c-ical__link),
				.tribe-common .tribe-common-b2 a:not(.tribe-events-c-small-cta__link):not(.tribe-events-c-ical__link),
				.tribe-common .tribe-common-b3 a:not(.tribe-events-c-small-cta__link):not(.tribe-events-c-ical__link) {
					color: <%= text.link_color %>;
				}
			';
		}

		return $template;
	}

	/**
	 * Setup the Customizer section.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function setup() {
		$this->defaults = [
			'primary_text_color'   => '#141827',
			'secondary_text_color' => '#727272',
			'link_color'           => '#141827',
		];

		$this->arguments = [
			'priority'    => 60,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Text', 'the-events-calendar' ),
			'description' => esc_html__( 'Options selected here will override what was selected in the General section.', 'the-events-calendar' ),
		];
	}

	/**
	 * Create the Fields/Settings for this sections.
	 *
	 * @param  WP_Customize_Section $section The WordPress section instance.
	 * @param  WP_Customize_Manager $manager [description]
	 *
	 * @return void
	 */
	public function register_settings( WP_Customize_Section $section, WP_Customize_Manager $manager ) {
		$customizer = Tribe__Customizer::instance();

		$manager->add_setting(
			$customizer->get_setting_name( 'primary_text_color', $section ),
			[
				'default'              => $this->get_default( 'primary_text_color' ),
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'primary_text_color', $section ),
				[
					'label'   => esc_html__( 'Primary Text Color', 'the-events-calendar' ),
					'section' => $section->id,
				]
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'secondary_text_color', $section ),
			[
				'default'              => $this->get_default( 'secondary_text_color' ),
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'secondary_text_color', $section ),
				[
					'label'   => esc_html__( 'Secondary Text Color', 'the-events-calendar' ),
					'section' => $section->id,
				]
			)
		);

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

		// Introduced to make Selective Refresh have less code duplication.
		$customizer->add_setting_name( $customizer->get_setting_name( 'primary_text_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'secondary_text_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'link_color', $section ) );
	}
}
