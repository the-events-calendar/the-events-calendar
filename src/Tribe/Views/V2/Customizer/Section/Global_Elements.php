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
			'priority'	=> 1,
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
			'accent_color'            => '#334aff',
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

	public function setup_content_headings() {
		$this->content_headings = [
			'font_color' => [
				'priority'	 => 0,
				'type'		 => 'heading',
				'label'    => esc_html__( 'Set Font Colors', 'the-events-calendar' ),
			],
			'global_elements_separator' => [
				'priority'	 => 20,
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
		$customizer             = tribe( 'customizer' );

		$this->content_controls = [
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
				'description' => esc_html__( 'All calendar and event pages - fnord', 'the-events-calendar' ),
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
				'label'    => esc_html_x(
					'Event Title',
					'The event title color setting label.',
					'the-events-calendar'
				),
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
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return $css_template;
		}

		if (
			! $this->should_include_setting_css( 'event_title_color' )
			&& ! $this->should_include_setting_css( 'event_date_time_color' )
			&& ! $this->should_include_setting_css( 'accent_color' )
			&& ! $this->should_include_setting_css( 'link_color' )
		) {
			return $css_template;
		}

		// These allow us to continue to _not_ target the shortcode.
		$apply_to_shortcode = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );
		$tribe_events = $apply_to_shortcode ? '.tribe-events, #tribe-events-pg-template' : '.tribe-events:not( .tribe-events-view--shortcode ), #tribe-events-pg-template';

		$css_template = "$tribe_events {\n";

		// Accent color overrides.
		if ( $this->should_include_setting_css( 'accent_color' ) ) {
			$accent_color_rgb = $this->to_rgb( $this->get_option( 'accent_color' ) );

			$css_template .= "
				/* Accent Color overrides. */
				--tec-color-accent-primary: <%= global_elements.accent_color %>;
				--tec-color-accent-primary-hover: rgba({$accent_color_rgb},0.8);
				--tec-color-accent-primary-multiday: rgba({$accent_color_rgb},0.24);
				--tec-color-accent-primary-multiday-hover: rgba({$accent_color_rgb},0.34);
				--tec-color-accent-primary-active: rgba({$accent_color_rgb},0.9);
				--tec-color-accent-primary-background: rgba({$accent_color_rgb},0.07);
				--tec-color-background-secondary-datepicker: rgba({$accent_color_rgb},0.5);
				--tec-color-accent-primary-background-datepicker: <%= global_elements.accent_color %>;
				--tec-color-button-primary: <%= global_elements.accent_color %>;
				--tec-color-button-primary-hover: rgba({$accent_color_rgb},0.8);
				--tec-color-button-primary-active: rgba({$accent_color_rgb},0.9);
				--tec-color-button-primary-background: rgba({$accent_color_rgb},0.07);
			";
		}

		// Event Title overrides.
		if ( $this->should_include_setting_css( 'event_title_color' ) ) {
			$css_template .= '
				/* Event Title overrides. */
				--tec-color-text-events-title: <%= global_elements.event_title_color %>;
				--tec-color-text-event-title: <%= global_elements.event_title_color %>;
			';
		}

		// Background color overrides.
		if ( $this->should_include_setting_css( 'background_color_choice' ) ) {
			if ( $this->should_include_setting_css( 'background_color' ) ) {
				$css_template             .= '
					/* Background Color overrides. */
					--tec-color-background-events: <%= global_elements.background_color %>;
				';
			}
		}

		// Event Date/Time overrides.
		if ( $this->should_include_setting_css( 'event_date_time_color' ) ) {
			$css_template .= '
				/* Event Date/Time overrides. */
				--tec-color-text-event-date: <%= global_elements.event_date_time_color %>;
				--tec-color-text-secondary-event-date: <%= global_elements.event_date_time_color %>;
			';
		}

		// Link color overrides.
		if ( $this->should_include_setting_css( 'link_color' ) ) {
			$css_template .= '
				/* Link Color overrides. */
				--tec-color-link-primary: <%= global_elements.link_color %>;
				--tec-color-link-accent: <%= global_elements.link_color %>;
				--tec-color-link-accent-hover: <%= global_elements.link_color %>CC;
			';
		}

		$css_template .= "\n}";

		return $css_template;
	}
}
