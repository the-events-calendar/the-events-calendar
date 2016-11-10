<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Day List View
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since 4.4
 */
class Tribe__Events__Customizer__Day_List_View extends Tribe__Customizer__Section {
	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 *
	 * Note: This is the only required method for a Connector to work
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance( $name = null ) {
		return parent::instance( __CLASS__ );
	}

	/**
	 * Grab the CSS rules template
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Customizer::instance();

		if ( $customizer->has_option( $this->ID, 'price_bg_color' ) ) {
			$template .= '
				.tribe-events-list .tribe-events-event-cost span {
					background-color: <%= day_list_view.price_bg_color %>;
					border-color: <%= day_list_view.price_border_color %>;
					color: <%= day_list_view.price_color %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'featured_price_bg_color' ) ) {
			$template .= '
				.tribe-events-list .tribe-events-loop .tribe-event-featured .tribe-events-event-cost span {
					background-color: <%= day_list_view.featured_price_bg_color %>;
					border-color: <%= day_list_view.price_border_color %>;
					color: <%= day_list_view.featured_price_color %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'featured_bg_color' ) ) {
			$template .= '
				.tribe-events-list .tribe-events-loop .tribe-event-featured,
				.tribe-events-list #tribe-events-day.tribe-events-loop .tribe-event-featured,
				#tribe-events-content .tribe-events-tooltip.tribe-event-featured {
					background-color: <%= day_list_view.featured_bg_color %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'featured_title_color' ) ) {
			$template .= '
				#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured [class*="-event-title"] a,
				#tribe-events-content .tribe-event-featured.tribe-events-tooltip .entry-title {
					color: <%= day_list_view.featured_title_color %>;
				}

				#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured [class*="-event-title"] a:active,
				#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured [class*="-event-title"] a:hover {
					color: <%= day_list_view.featured_title_color_active %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'featured_text_color' ) ) {
			$template .= '
				.tribe-events-list .tribe-events-loop .tribe-event-featured,
				.tribe-events-list .tribe-events-loop .tribe-event-featured .entry-summary,
				.tribe-events-list #tribe-events-day.tribe-events-loop .tribe-event-featured,
				.tribe-events-list #tribe-events-day.tribe-events-loop .tribe-event-featured .entry-summary,
				#tribe-events-content .tribe-event-featured.tribe-events-tooltip {
					color: <%= day_list_view.featured_text_color %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'featured_link_color' ) ) {
			$template .= '
				#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured a,
				#tribe-events-content .tribe-event-featured.tribe-events-tooltip a {
					color: <%= day_list_view.featured_link_color %>;
				}

				#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured a:active,
				#tribe-events-content.tribe-events-list .tribe-events-loop .tribe-event-featured a:hover,
				#tribe-events-content .tribe-event-featured.tribe-events-tooltip a:hover {
					color: <%= day_list_view.featured_link_color_active %>;
				}
			';
		}

		return $template;
	}

	public function create_ghost_settings( $settings = array() ) {

		if ( ! empty( $settings['price_bg_color'] ) ) {
			$price_bg_color = new Tribe__Utils__Color( $settings['price_bg_color'] );

			$settings['price_border_color'] = '#' . $price_bg_color->darken( 15 );
			if ( $price_bg_color->isDark() ) {
				$settings['price_color'] = '#f9f9f9';
			} else {
				$settings['price_color'] = '#333333';
			}
		}

		if ( ! empty( $settings['featured_price_bg_color'] ) ) {
			$featured_price_bg_color = new Tribe__Utils__Color( $settings['featured_price_bg_color'] );

			$settings['featured_price_bg_color'] = '#' . $featured_price_bg_color->getHex();

			if ( $featured_price_bg_color->isDark() ) {
				$settings['featured_price_color'] = '#f9f9f9';
			} else {
				$settings['featured_price_color'] = '#333333';
			}
		}

		if ( ! empty( $settings['featured_bg_color'] ) ) {
			$featured_bg_color = new Tribe__Utils__Color( $settings['featured_bg_color'] );

			$settings['featured_bg_color'] = '#' . $featured_bg_color->getHex();
		}

		if ( ! empty( $settings['featured_title_color'] ) ) {
			$featured_title_color = new Tribe__Utils__Color( $settings['featured_title_color'] );

			$settings['featured_title_color'] = '#' . $featured_title_color->getHex();
			$settings['featured_title_color_active'] = '#' . $featured_title_color->lighten( 8 );
		}

		if ( ! empty( $settings['featured_text_color'] ) ) {
			$featured_text_color = new Tribe__Utils__Color( $settings['featured_text_color'] );

			$settings['featured_text_color'] = '#' . $featured_text_color->getHex();
		}

		if ( ! empty( $settings['featured_link_color'] ) ) {
			$featured_link_color = new Tribe__Utils__Color( $settings['featured_link_color'] );

			$settings['featured_link_color'] = '#' . $featured_link_color->getHex();

			if ( ! empty( $featured_bg_color ) ) {
				if ( $featured_bg_color->isDark() ) {
					$settings['featured_link_color_active'] = '#' . $featured_link_color->lighten( 8 );
				} else {
					$settings['featured_link_color_active'] = '#' . $featured_link_color->darken( 8 );
				}
			} else {
				if ( $featured_link_color->isDark() ) {
					$settings['featured_link_color_active'] = '#' . $featured_link_color->darken( 8 );
				} else {
					$settings['featured_link_color_active'] = '#' . $featured_link_color->lighten( 8 );
				}
			}
		}

		return $settings;
	}

	public function setup() {
		$this->defaults = array(
			'price_bg_color' => '#eeeeee',
		);

		$this->arguments = array(
			'priority'    => 40,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'List-style Views', 'the-events-calendar' ),
			'description' => esc_html__( 'These settings impact all list-style views, including List View and Day View.', 'the-events-calendar' ),
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
			$customizer->get_setting_name( 'price_bg_color', $section ),
			array(
				'default'              => $this->get_default( 'price_bg_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'price_bg_color', $section ),
				array(
					'label'   => esc_html__( 'Price Background Color', 'the-events-calendar' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'featured_bg_color', $section ),
			array(
				'default'              => $this->get_default( 'featured_bg_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'featured_bg_color', $section ),
				array(
					'label'   => __( 'Featured Background Color' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'featured_title_color', $section ),
			array(
				'default'              => $this->get_default( 'featured_title_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'featured_title_color', $section ),
				array(
					'label'   => __( 'Featured Title Color' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'featured_text_color', $section ),
			array(
				'default'              => $this->get_default( 'featured_text_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'featured_text_color', $section ),
				array(
					'label'   => __( 'Featured Text Color' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'featured_link_color', $section ),
			array(
				'default'              => $this->get_default( 'featured_link_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'featured_link_color', $section ),
				array(
					'label'   => __( 'Featured Link Color' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'featured_price_bg_color', $section ),
			array(
				'default'              => $this->get_default( 'featured_price_bg_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'featured_price_bg_color', $section ),
				array(
					'label'   => __( 'Featured Price Background Color' ),
					'section' => $section->id,
				)
			)
		);

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'price_bg_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'featured_bg_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'featured_title_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'featured_text_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'featured_link_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'featured_price_bg_color', $section ) );
	}
}
