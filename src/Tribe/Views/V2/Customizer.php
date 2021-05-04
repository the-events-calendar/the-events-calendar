<?php
/**
 * Handles Views v2 Customizer settings.
 *
 * @since   5.3.1
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
					'label'       => 'Background Color',
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
	public function filter_global_elements_css_template( $css_template, $section, $customizer ) {
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
				--tec-color-text-event-title: <%= single_event.post_title_color %>;
			';
		}

		return $css_template;
	}

	/**
	 * Enqueues Customizer controls styles specific to Views v2 components.
	 *
	 * @since 5.4.0
	 */
	public function enqueue_customizer_controls_styles() {
		tribe_asset_enqueue( 'tribe-customizer-views-v2-controls' );
	}

	/**
	 * Check whether the Single Event styles overrides can be applied
	 *
	 * @return false/true
	 */
	public function should_add_single_view_v2_styles() {
		// Bail if not Single Event.
		if ( ! tribe( Template_Bootstrap::class )->is_single_event() ) {
			return false;
		}

		// Bail if Block Editor.
		if ( has_blocks( get_queried_object_id() ) ) {
			return false;
		}

		// Use the function from provider.php to check if V2 is not enabled
		// or the TRIBE_EVENTS_WIDGETS_V2_DISABLED constant is true.
		if ( ! tribe_events_single_view_v2_is_enabled() ) {
			return false;
		}

		return true;
	}
}
