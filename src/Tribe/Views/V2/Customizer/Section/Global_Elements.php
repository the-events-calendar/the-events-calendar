<?php
/**
 * The Events Calendar Customizer Section Class
 * Global Elements
 *
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Customizer\Section;

/**
 * Month View
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
		$this->arguments = [
			'priority'	=> 55,
			'capability'  => 'edit_theme_options',
			'title'	   => esc_html__( 'Global Elements', 'the-events-calendar' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_defaults() {
		$this->defaults = [
			'event_title_color'       => '#141827',
			'event_date_time_color'   => '#141827',
			'background_color_choice' => 'transparent',
			'background_color'        => '',
			'accent_color'            => '',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_settings() {
		$this->content_settings = [
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

		/**
	 * {@inheritdoc}
	 */
	public function setup_content_controls() {
		$customizer             = tribe( 'customizer' );

		$this->content_controls = [
			'event_title_color' => [
				'priority' => 3,
				'type'     => 'color',
				'label'    => esc_html_x(
					'Event Title',
					'The event title color setting label.',
					'the-events-calendar'
				),
			],
			'event_date_time_color' => [
				'priority' => 6,
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
				'priority'    => 9,
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
				'priority' => 10,
				'type'     => 'color',
				'label'    => esc_html_x(
					'Event Title',
					'The event title color setting label.',
					'the-events-calendar'
				),
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'background_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					bdump($value);
					return $this->defaults['background_color_choice'] !== $value;
				},
			],
			'accent_color' => [
				'priority' => 15,
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
	 * @param string                      $css_template The CSS template, as produced by the Global Elements.
	 * @param \Tribe__Customizer__Section $section      The Global Elements section.
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function get_css_template( $css_template ) {
		$settings    = $customizer->get_option( [ $section->ID ] );
		$has_options = $customizer->has_option( $section->ID, 'event_title_color' )
						|| $customizer->has_option( $section->ID, 'accent_color' )
						|| $customizer->has_option( $section->ID, 'event_date_time_color' )
						|| $customizer->has_option( $section->ID, 'link_color' );

		if ( $has_options ) {
			$css_template .= "
			:root{
			";

			// Override placeholders - we'll clean up and concat these at the end.
			$overrides = [
				'avada'           => '',
				'divi'            => '',
				'enfold'          => '',
				'genesis'         => '',
				'twentyseventeen' => '',
				'twentynineteen'  => '',
				'twentytwenty'    => '',
				'twentytwentyone' => '',
			];
		}


		// Accent color overrides.
		if ( $customizer->has_option( $section->ID, 'accent_color' ) ) {
			$accent_color     = new \Tribe__Utils__Color( $settings['accent_color'] );
			$accent_color_rgb = $accent_color::hexToRgb( $settings['accent_color'] );
			$accent_css_rgb   = $accent_color_rgb['R'] . ',' . $accent_color_rgb['G'] . ',' . $accent_color_rgb['B'];

			$css_template .= "
				/* Accent Color overrides. */
				--tec-color-accent-primary: <%= global_elements.accent_color %>;
				--tec-color-accent-primary-hover: rgba({$accent_css_rgb},0.8);
				--tec-color-accent-primary-multiday: rgba({$accent_css_rgb},0.24);
				--tec-color-accent-primary-multiday-hover: rgba({$accent_css_rgb},0.34);
				--tec-color-accent-primary-active: rgba({$accent_css_rgb},0.9);
				--tec-color-accent-primary-background: rgba({$accent_css_rgb},0.07);
				--tec-color-background-secondary-datepicker: rgba({$accent_css_rgb},0.5);
				--tec-color-accent-primary-background-datepicker: <%= global_elements.accent_color %>;
			";

			/*
			// overrides for common base/full/typography/_ctas.pcss.

			$css_template .= "
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):focus,
				.tribe-theme-twentyseventeen $tribe_common .tribe-common-c-btn-border:not(.tribe-common-c-btn-border--secondary):not(.tribe-common-c-btn-border--alt):hover,
					background-color: <%= global_elements.accent_color %>;
				}
			";

			$css_template .= '
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-c-btn:hover,
				.tribe-theme-twentyseventeen .tribe-common .tribe-common-c-btn:focus,
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn:hover,
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn:focus {
					background-color: var(--tec-color-accent-primary-hover);
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

			$css_template .= '
				.tribe-theme-twentytwenty .tribe-common .tribe-common-c-btn {
					background-color: <%= global_elements.accent_color %>;
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

					.tribe-events-virtual-link-button {
						background-color: <%= global_elements.accent_color %>;
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
			*/
		}

		// Event Title overrides.
		if ( $customizer->has_option( $section->ID, 'event_title_color' ) ) {
			$css_template .= '
				/* Event Title overrides. */
				--tec-color-text-events-title: <%= global_elements.event_title_color %>;
			';
		}

		// Background color overrides.
		if (
			$customizer->has_option( $section->ID, 'background_color_choice' )
			&& 'custom' === $customizer->get_option( [ $section->ID, 'background_color_choice' ] )
			&& $customizer->has_option( $section->ID, 'background_color' )
		) {
			$css_template .= '
				/* Background Color overrides. */
				--tec-color-background-events: <%= global_elements.background_color %>;
			';
			$overrides['twentytwenty'] .= '
				/* Background Color overrides. */
				--tec-color-background-events: <%= global_elements.background_color %>;
			';
		}

		// Event Date/Time overrides.
		if ( $customizer->has_option( $section->ID, 'event_date_time_color' ) ) {
			$css_template .= '
				/* Event Date/Time overrides. */
				--tec-color-text-event-date: <%= global_elements.event_date_time_color %>;
				--tec-color-text-secondary-event-date: <%= global_elements.event_date_time_color %>;
			';
		}

		// Link color overrides.
		if ( $customizer->has_option( $section->ID, 'link_color' ) ) {
			$css_template .= '
				/* Link Color overrides. */
				--tec-color-link-primary: <%= global_elements.link_color %>;
				--tec-color-link-accent: <%= global_elements.link_color %>;
				--tec-color-link-accent-hover: <%= global_elements.link_color %>CC;
			';
		}

		if ( $has_options ) {
			$css_template .= '
			}
			';
		}

		// Now for some magic...
		/**
		 * @var Theme_Compatibility $theme_compatibility
		 */
		$theme_compatibility = tribe( Theme_Compatibility::class );
		$themes = $theme_compatibility->get_active_themes();

		// Wrap each in the appropriate selector.
		foreach ( $themes as $generation => $theme ) {
			if ( 'child' === $generation ) {
				$theme = 'child-' . $theme;
			}

			$css_template .= "

			.tribe-theme-$theme .tribe-common {
				{$overrides[ $theme ]}
			}

			";
		}

		bdump($css_template);

		return $css_template;
	}
}
