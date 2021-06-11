<?php
_deprecated_file( __FILE__, '5.1.5', 'Tribe__Events__Customizer__Global_Elements' );

/**
 * The Events Calendar Customizer Section: Text.
 *
 * @package The Events Calendar
 * @since   5.0.1
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
	 * @since 5.0.1
	 *
	 * @param string  $template
	 * @return string $template
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Customizer::instance();

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
	 * @since 5.0.1
	 *
	 * @return void
	 */
	public function setup() {
		$this->defaults = [
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
			$customizer->get_setting_name( 'link_color', $section ),
			[
				'default' => $this->get_default( 'link_color' ),
				'type'    => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'link_color', $section ),
				[
					'label'   => esc_html__( 'Link Color', 'the-events-calendar' ),
					'section' => $section->id,
				]
			)
		);

		// Introduced to make Selective Refresh have less code duplication.
		$customizer->add_setting_name( $customizer->get_setting_name( 'link_color', $section ) );
	}
}