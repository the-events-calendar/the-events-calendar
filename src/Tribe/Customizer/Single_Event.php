<?php
// Don't load directly.
use Tribe\Customizer\Controls\Heading;

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Single Event
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since 4.4
 */
final class Tribe__Events__Customizer__Single_Event extends Tribe__Customizer__Section {
	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 *
	 * Note: This is the only required method for a Connector to work
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance() {
		return tribe( 'tec.customizer.single-event' );
	}

	/**
	 * Add the CSS rules template to the `tribe_events_pro_customizer_css_template`
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		// Sanity check.
		if ( tribe_events_views_v2_is_enabled() ) {
			return $template;
		}

		$customizer = tribe( 'customizer' );

		if ( $customizer->has_option( $this->ID, 'details_bg_color' ) ) {
			$template .= '
				.single-tribe_events .tribe-events-event-meta {
					background-color: <%= single_event.details_bg_color %>;
					color: <%= single_event.details_text_color %>;
				}
			';
		}

		if ( tribe_events_views_v2_is_enabled() ) {
			/**
			 * Allows filtering the CSS template with full knowledge of the Single Event section and the current Customizer instance.
			 *
			 * @since 5.3.1
			 *
			 * @param string                     $template   The CSS template, as produced by the Global Elements.
			 * @param Tribe__Customizer__Section $this       The Single Event section.
			 * @param Tribe__Customizer          $customizer The current Customizer instance.
			 */
			return apply_filters( 'tribe_customizer_single_event_css_template', $template, $this, $customizer );
		}

		if ( $customizer->has_option( $this->ID, 'post_title_color' ) ) {
			$template .= '
				.single-tribe_events .tribe-events-single-event-title {
					color: <%= single_event.post_title_color %>;
				}
			';
		}

		return $template;
	}

	public function create_ghost_settings( $settings = [] ) {
		if ( ! empty( $settings['details_bg_color'] ) ) {
			$details_bg_color = new Tribe__Utils__Color( $settings['details_bg_color'] );

			if ( $details_bg_color->isDark() ) {
				$settings['details_text_color'] = '#f9f9f9';
			} else {
				$settings['details_text_color'] = '#333333';
			}
		}

		return $settings;
	}

	public function setup() {
		$this->defaults = array(
			'post_title_color' => tribe_events_views_v2_is_enabled() ? '#141827' : '#333',
			'details_bg_color' => '#e5e5e5',
		);

		$description = tribe_events_views_v2_is_enabled()
			? esc_html__( 'These settings control the appearance of the single event pages.', 'the-events-calendar' )
			: esc_html__( 'Options selected here will override what was selected in the "General Theme" and "Global Elements" sections.', 'the-events-calendar' );

		$this->arguments = [
			'priority'    => 60,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Single Event', 'the-events-calendar' ),
			'description' => $description,
		];
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
		$customizer = tribe( 'customizer' );

		// Add an heading that is a Control only in name: it does not, actually, control or save any setting.
		$manager->add_control(
			new Heading(
				$manager,
				$customizer->get_setting_name( 'post_title_heading', $section ),
				[
					'label'    => esc_html__( 'Set Font Colors', 'the-events-calendar' ),
					'section'  => $section->id,
					'priority' => 0,
				]
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'post_title_color', $section ),
			[

				'default'              => $this->get_default( 'post_title_color' ),
				'type'                 => 'option',
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'post_title_color', $section ),
				[
					'label'   => tribe_events_views_v2_is_enabled()
						? esc_html__( 'Event Title Color', 'the-events-calendar' )
						: esc_html__( 'Post Title Color', 'the-events-calendar' ),
					'section' => $section->id,
				]
			)
		);

		// The Details Background Color control won't be present if the Single Event styles overrides are enabled
		if ( ! tribe_events_single_view_v2_is_enabled() ) {
			// Add an heading that is a Control only in name: it does not, actually, control or save any setting.
			$manager->add_control(
				new Heading(
					$manager,
					$customizer->get_setting_name( 'details_bg_color_heading', $section ),
					[
						'label'    => esc_html__( 'Adjust Appearance', 'the-events-calendar' ),
						'section'  => $section->id,
						'priority' => 10,
					]
				)
			);

			$manager->add_setting(
				$customizer->get_setting_name( 'details_bg_color', $section ),
				[
					'default'              => $this->get_default( 'details_bg_color' ),
					'type'                 => 'option',
					'sanitize_callback'    => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color',
				]
			);

			$manager->add_control(
				new WP_Customize_Color_Control(
					$manager,
					$customizer->get_setting_name( 'details_bg_color', $section ),
					[
						'label'       => esc_html__( 'Event Details Background Color', 'the-events-calendar' ),
						'description' => esc_html__( 'For classic editor', 'the-events-calendar' ),
						'section'     => $section->id,
					]
				)
			);
		}

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'post_title_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'details_bg_color', $section ) );
	}
}
