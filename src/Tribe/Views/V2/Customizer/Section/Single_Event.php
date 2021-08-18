<?php
/**
 * The Events Calendar Customizer Section Class
 * Single Event
 *
 * @since 5.9.0
 */

namespace Tribe\Events\Views\V2\Customizer\Section;

use Tribe\Events\Views\V2\Template_Bootstrap as Bootstrap;
/**
 * Single Event
 *
 * @since 5.9.0
 */
final class Single_Event extends \Tribe__Customizer__Section {

	/**
	 * ID of the section.
	 *
	 * @since 5.9.0
	 *
	 * @access public
	 * @var string
	 */
	public $ID = 'single_event';

	/**
	 * Allows sections to be loaded in order for overrides.
	 *
	 * @var integer
	 */
	public $queue_priority = 20;

	/**
	 * This method will be executed when the Class is Initialized.
	 *
	 * @since 5.9.0
	 */
	public function setup() {
		parent::setup();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_arguments() {
		return [
			'priority'	=> 20,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Single Event', 'the-events-calendar' ),
			'description' => esc_html__( 'Options selected here will override what was selected in the "Global Elements" section on Single Event views.', 'the-events-calendar' ),
		];
	}


	/**
	 * {@inheritdoc}
	 */
	public function setup_defaults() {
		return [
			'post_title_color_choice' => 'default',
			'post_title_color'        => '#141827',
			'details_bg_color'        => '#e5e5e5',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_settings() {
		return [
			'post_title_color_choice' => [
				'sanitize_callback'    => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'post_title_color'        => [
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'details_bg_color'        => [
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_headings() {
		return [
			'font_colors' => [
				'priority' => 0,
				'type'     => 'heading',
				'label'    => esc_html__( 'Set Font Colors', 'the-events-calendar' ),
			],
			'single_view_separator' => [
				'priority'        => 10,
				'type'            => 'separator',
				'active_callback' => function() {
					// Heading should not show if the new Single View is enabled.
					return ! tribe_events_single_view_v2_is_enabled();
				},
			],
			'adjust_appearance' => [
				'priority'        => 20,
				'label'           => esc_html__( 'Adjust Appearance', 'the-events-calendar' ),
				'active_callback' => function() {
					// Heading should not show if the new Single View is enabled.
					return ! tribe_events_single_view_v2_is_enabled();
				},
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_controls() {
		$customizer = tribe( 'customizer' );
		return [
			'post_title_color_choice' => [
				'priority' => 5,
				'type'     => 'radio',
				'label'    => esc_html__( 'Event Title Color', 'the-events-calendar' ),
				'choices'  => [
					'default' => esc_html__( 'Use General', 'the-events-calendar' ),
					'custom'  => esc_html__( 'Custom', 'the-events-calendar' ),
				],
			],
			'post_title_color'        => [
				'priority' => 6, // Should come immediately after post_title_color_choice
				'type'     => 'color',
				'active_callback' => function( $control ) use ( $customizer ) {
					$setting_name = $customizer->get_setting_name( 'post_title_color_choice', $control->section );
					$value = $control->manager->get_setting( $setting_name )->value();
					return $this->defaults['post_title_color_choice'] !== $value;
				},
			],
			'details_bg_color'        => [
				'priority'    => 25,
				'type'        => 'color',
				'label'       => esc_html__( 'Event Details Background Color', 'the-events-calendar' ),
				'active_callback' => function() {
					// Control should not show if the new Single View is enabled.
					return ! tribe_events_single_view_v2_is_enabled();
				},
			],
		];
	}

	/**
	 * Filters the Single Event section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 5.3.1
	 *
	 * @param string                      $css_template The CSS template, as produced by the Single Event.
	 *
	 * @return string The filtered CSS template.
	 */
	public function get_css_template( $css_template ) {
		// For sanity's sake.
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return $css_template;
		}

		$new_styles = [];

		/**
		 * Note the pattern here: if a custom color is set it MUST override the general setting -
		 *  even if it is the default value.
		 *
		 * Thus we don't check should_include_setting_css() on post_title_color but we do add a check to make sure
		 * it's not empty (which could "erase" the custom prop by setting it to an empty string) as a safeguard.
		 */
		if (
			$this->should_include_setting_css( 'post_title_color_choice' )
			&& ! empty( $this->get_option( 'post_title_color' ) )
		) {
			$post_title_color = $this->get_option( 'post_title_color' );

			$new_styles[] = "--tec-color-text-event-title: {$post_title_color};";
		}

		if ( empty( $new_styles ) ) {
			return $css_template;
		}

		$new_css = sprintf(
			':root {
				/* Customizer-added Single Event styles */
				%1$s
			}',
			implode( "\n", $new_styles )
		);

		return $css_template . $new_css;
	}

	/**
	 * Check whether the Single Event styles overrides can be applied
	 * Deprecated:  The new Customizer applies to both, this function was moved to src/Tribe/Views/V2/Customizer.php
	 *              for backwards-compatibility.
	 *
	 * @deprecated 5.9.0
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
		if ( has_blocks( get_queried_object_id() ) ) {
			return false;
		}

		return true;
	}
}
