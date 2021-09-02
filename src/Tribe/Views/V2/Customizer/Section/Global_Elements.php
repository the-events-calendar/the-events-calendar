<?php
/**
 * The Events Calendar Customizer Section Class
 * Global Elements
 *
 * @since 5.9.0
 */

namespace Tribe\Events\Views\V2\Customizer\Section;
/**
 * Global Elements
 *
 * @since 5.9.0
 */
final class Global_Elements extends \Tribe__Customizer__Section {

	/**
	 * ID of the section.
	 *
	 * @since 5.9.0
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
	 * Placeholder for filtered multiplier for small font size.
	 *
	 * @var float
	 */
	private $small_font_multiplier = .75;

	/**
	 * Placeholder for filtered multiplier for large font size.
	 *
	 * @var float
	 */
	private $large_font_multiplier = 1.5;

	/**
	 * Placeholder for filtered min font size.
	 *
	 * @var int
	 */
	private $min_font_size = 8;

	/**
	 * Placeholder for filtered max font size.
	 *
	 * @var int
	 */
	private $max_font_size = 72;

	/**
	 * This method will be executed when the Class is Initialized.
	 *
	 * @since 5.9.0
	 */
	public function setup() {
		parent::setup();

		/**
		 * Allows users and plugins to change the "small" font size multiplier.
		 *
		 * @since 5.9.0
		 *
		 * @param int $small_font_multiplier The multiplier for "small" font size.
		 *
		 * @return int The multiplier for "small" font size. This should be less than 1.
		 */
		$this->small_font_multiplier = apply_filters( 'tribe_customizer_small_font_size_multiplier', $this->small_font_multiplier );

		/**
		 * Allows users and plugins to change the "large" font size multiplier.
		 *
		 * @since 5.9.0
		 *
		 * @param int $large_font_multiplier The multiplier for "large" font size.
		 *
		 * @return int The multiplier for "large" font size. This should be greater than 1.
		 */
		$this->large_font_multiplier = apply_filters( 'tribe_customizer_large_font_size_multiplier', $this->large_font_multiplier );

		/**
		 * Allows users and plugins to change the minimum font size.
		 *
		 * @since 5.9.0
		 *
		 * @param int $min_font_size The enforced minimum font size.
		 *
		 * @return int The enforced minimum font size.
		 */
		$this->min_font_size = apply_filters( 'tribe_customizer_minimum_font_size', $this->min_font_size );

		/**
		 * Allows users and plugins to change the maximum font size.
		 *
		 * @since 5.9.0
		 *
		 * @param int $max_font_size The enforced maximum font size.
		 *
		 * @return int The enforced maximum font size.
		 */
		$this->max_font_size = apply_filters( 'tribe_customizer_maximum_font_size', $this->max_font_size );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_arguments() {
		return [
			'priority'   => 1,
			'capability' => 'edit_theme_options',
			'title'      => esc_html__( 'Global Elements', 'the-events-calendar' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_defaults() {
		return [
			'font_family'             => 'default',
			'font_size'               => '0',
			'font_size_base'          => '16',
			'event_title_color'       => '#141827',
			'event_date_time_color'   => '#141827',
			'link_color'              => '#141827',
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
			'font_family'             => [
				'sanitize_callback'    => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'font_size'               => [
				'sanitize_callback'    => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'font_size_base'          => [
				'sanitize_callback'    => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'event_title_color'       => [
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'event_date_time_color'   => [
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'link_color'              => [
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'background_color_choice' => [
				'sanitize_callback'    => 'sanitize_key',
				'sanitize_js_callback' => 'sanitize_key',
			],
			'background_color'        => [
				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			],
			'accent_color'            => [
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
			'font_family_heading'       => [
				'priority' => 0,
				'type'     => 'heading',
				'label'    => esc_html__( 'Select Font Family', 'the-events-calendar' ),
			],
			'font_size_heading'         => [
				'priority'    => 5,
				'type'        => 'heading',
				'label'       => esc_html__( 'Set Font Size', 'the-events-calendar' ),
				'description' => esc_html_x(
					'Choose a base font size. Event text will scale around the selected base size.',
					'The description for the base font size setting.',
					'the-events-calendar'
				),
			],
			'font_color_heading'        => [
				'priority' => 10,
				'type'     => 'heading',
				'label'    => esc_html__( 'Set Font Colors', 'the-events-calendar' ),
			],
			'global_elements_separator' => [
				'priority' => 20,
				'type'     => 'separator',
			],
			'adjust_appearance_heading' => [
				'priority' => 21,
				'type'     => 'heading',
				'label'    => esc_html__( 'Adjust Appearance', 'the-events-calendar' ),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_content_controls() {
		// Because Customizer doesn't show the default value.
		$font_size_base_value = ! empty( $this->get_option( 'font_size_base' ) )
			? $this->get_option( 'font_size_base' )
			: $this->defaults[ 'font_size_base' ];


		return [
			'font_family'             => [
				'priority' => 3,
				'type'     => 'radio',
				'choices' => [
					'default'  => esc_html_x(
						'Default',
						'Label for option to use default TEC fonts.',
						'the-events-calendar'
					),
					'theme'    => esc_html_x(
						"Inherit theme font(s)",
						'Label for option to use theme fonts.',
						'the-events-calendar'
					)
				],
			],
			'font_size_base'          => [
				'priority' => 6,
				'type'     => 'number',
				'label'    => esc_html_x(
					'By Pixel',
					'The base font size input setting label.',
					'the-events-calendar'
				),
				'input_attrs' => [
					'min'   => '8',
					'max'   => '24',
					'step'  => '1',
					'style' => 'width: 4em;',
					'value' => (int) $font_size_base_value,
				]
			],
			'font_size'               => [
				'priority' => 7,
				'type'     => 'range-slider',
				'label'    => esc_html_x(
					'By Scale',
					'The font size selector setting label.',
					'the-events-calendar'
				),
				'input_attrs' => [
					'min'  => -1,
					'max'  => 1,
					'step' => 1,
				],
				'choices'    => [
					'small'  => $this->small_font_multiplier,
					'medium' => '1',
					'large'  => $this->large_font_multiplier,
				],
			],
			'event_title_color'       => [
				'priority' => 15,
				'type'     => 'color',
				'label'    => esc_html_x(
					'Event Title',
					'The event title color setting label.',
					'the-events-calendar'
				),
			],
			'event_date_time_color'   => [
				'priority' => 17,
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
			'link_color'              => [
				'priority'    => 18,
				'type'        => 'color',
				'label'       => esc_html_x(
					'Link Color',
					'The link color setting label.',
					'the-events-calendar'
				),
				'description' => esc_html__(
					'For displayed URLs',
					'the-events-calendar'
				),
			],
			'background_color_choice' => [
				'priority'    => 25,
				'type'        => 'radio',
				'label'       => esc_html__( 'Background Color', 'the-events-calendar' ),
				'description' => esc_html__( 'All calendar and event pages', 'the-events-calendar' ),
				'choices'     => [
					'transparent' => esc_html_x(
						'Transparent.',
						'Label for option to leave transparent (default).',
						'the-events-calendar'
					),
					'custom'   => esc_html_x(
						'Select Custom Color',
						'Label for option to set a custom color.',
						'the-events-calendar'
					),
				],
			],
			'background_color'        => [
				'priority'  => 26, // Should come right after background_color_choice
				'type'      => 'color',
			],
			'accent_color'            => [
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

		$new_styles   = [];

		// It's all custom props now, baby...

		// Font family override.
		if ( $this->should_include_setting_css( 'font_family' ) ) {
			$new_styles[] = "--tec-font-family-sans-serif: inherit;";
			$new_styles[] = "--tec-font-family-base: inherit;";
		}

		/**
		 * It's about to get complicated - Font Size overrides!
		 *
		 * If they set a base font size, we set the font_size slider to match via js.
		 * If they set the slider, we set `font_size_base` to match via js.
		 * So we only have to do calculations based on the `font_size_base` setting.
		 *
		 * Original font sizes for reference:
		 * --tec-font-size-0: 11px;
		 * --tec-font-size-1: 12px;
		 * --tec-font-size-2: 14px;
		 * --tec-font-size-3: 16px;
		 * --tec-font-size-4: 18px;
		 * --tec-font-size-5: 20px;
		 * --tec-font-size-6: 22px;
		 * --tec-font-size-7: 24px;
		 * --tec-font-size-8: 28px;
		 * --tec-font-size-9: 32px;
		 * --tec-font-size-10: 42px;
		 */
		if ( $this->should_include_setting_css( 'font_size_base' ) ) {
			$sizes           = [ 11, 12, 14, 16, 18, 20, 22, 24, 28, 32, 42 ];
			$size_multiplier = 1;
			$size_multiplier = round( (int) $this->get_option( 'font_size_base' ) / 16, 3 );

			foreach ( $sizes as $key => $size ) {
				$font_size = $size_multiplier * (int) $size;
				// round to whole pixels.
				$font_size = round( $font_size );
				// Minimum font size, for sanity.
				$font_size = max( $font_size, $this->min_font_size );
				// Maximum font size, for sanity.
				$font_size = min( $font_size, $this->max_font_size );


				$new_styles[] = "--tec-font-size-{$key}: {$font_size}px;";
			}
		}

		// Event Title overrides.
		if ( $this->should_include_setting_css( 'event_title_color' ) ) {
			$title_color  = $this->get_option( 'event_title_color' );
			$new_styles[] = "--tec-color-text-events-title: {$title_color};";
			$new_styles[] = "--tec-color-text-event-title: {$title_color};";
		}

		// Event Date/Time overrides.
		if ( $this->should_include_setting_css( 'event_date_time_color' ) ) {
			$date_color   = $this->get_option( 'event_date_time_color' );
			$new_styles[] = "--tec-color-text-event-date: {$date_color};";
			$new_styles[] = "--tec-color-text-event-date-secondary: {$date_color};";
		}

		// Link color overrides.
		if ( $this->should_include_setting_css( 'link_color' ) ) {
			$link_color     = $this->get_option( 'link_color' );
			$link_color_rgb = $this->get_rgb_color( 'link_color' );
		} elseif ( $this->should_include_setting_css( 'accent_color' ) )  {
			$link_color     = $this->get_option( 'accent_color' );
			$link_color_rgb = $this->get_rgb_color( 'accent_color' );
		}

		if ( ! empty( $link_color ) ) {
			$new_styles[] = "--tec-color-link-primary: {$link_color};";
			$new_styles[] = "--tec-color-link-accent: {$link_color};";
			$new_styles[] = "--tec-color-link-accent-hover: rgba({$link_color_rgb}, 0.8);";
		}

		// Background color overrides.
		if ( $this->should_include_setting_css( 'background_color_choice' ) ) {
			if ( $this->should_include_setting_css( 'background_color' ) ) {
				$background_color = $this->get_option( 'background_color' );
				$new_styles[]     = "--tec-color-background-events: {$background_color};";
			}
		}

		// Accent color overrides.
		if ( $this->should_include_setting_css( 'accent_color' ) ) {
			$accent_color     = $this->get_option( 'accent_color' );
			$accent_color_rgb = $this->get_rgb_color( 'accent_color' );

			$new_styles[] = "--tec-color-accent-primary: {$accent_color};";
			$new_styles[] = "--tec-color-accent-primary-hover: rgba({$accent_color_rgb},0.8);";
			$new_styles[] = "--tec-color-accent-primary-multiday: rgba({$accent_color_rgb},0.24);";
			$new_styles[] = "--tec-color-accent-primary-multiday-hover: rgba({$accent_color_rgb},0.34);";
			$new_styles[] = "--tec-color-accent-primary-active: rgba({$accent_color_rgb},0.9);";
			$new_styles[] = "--tec-color-accent-primary-background: rgba({$accent_color_rgb},0.07);";
			$new_styles[] = "--tec-color-background-secondary-datepicker: rgba({$accent_color_rgb},0.5);";
			$new_styles[] = "--tec-color-accent-primary-background-datepicker: {$accent_color};";
			$new_styles[] = "--tec-color-button-primary: {$accent_color};";
			$new_styles[] = "--tec-color-button-primary-hover: rgba({$accent_color_rgb},0.8);";
			$new_styles[] = "--tec-color-button-primary-active: rgba({$accent_color_rgb},0.9);";
			$new_styles[] = "--tec-color-button-primary-background: rgba({$accent_color_rgb},0.07);";
			$new_styles[] = "--tec-color-day-marker-current-month: {$accent_color};";
			$new_styles[] = "--tec-color-day-marker-current-month-hover: rgba({$accent_color_rgb},0.8);";
			$new_styles[] = "--tec-color-day-marker-current-month-active: rgba({$accent_color_rgb},0.9);";

			if ( ! $this->should_include_setting_css( 'multiday_event_bar_color_choice', 'month_view' ) ) {
				$new_styles[] = "--tec-color-background-primary-multiday: rgba({$accent_color_rgb}, 0.24);";
				$new_styles[] = "--tec-color-background-primary-multiday-hover: rgba({$accent_color_rgb}, 0.34);";
				$new_styles[] = "--tec-color-background-primary-multiday-active: rgba({$accent_color_rgb}, 0.34);";
				$new_styles[] = "--tec-color-background-secondary-multiday: rgba({$accent_color_rgb}, 0.24);";
				$new_styles[] = "--tec-color-background-secondary-multiday-hover: rgba({$accent_color_rgb}, 0.34);";
			}
		}

		if ( empty( $new_styles ) ) {
			return $css_template;
		}

		$new_css = sprintf(
			':root {
				/* Customizer-added Global Event styles */
				%1$s
			}',
			implode( "\n", $new_styles )
		);

		return $css_template . $new_css;
	}
}
