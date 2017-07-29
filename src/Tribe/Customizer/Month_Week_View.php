<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * The Events Calendar Customizer Section Class
 * Month Week View
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since 4.4
 */
final class Tribe__Events__Customizer__Month_Week_View extends Tribe__Customizer__Section {
	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 *
	 * Note: This is the only required method for a Connector to work
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance() {
		return tribe( 'tec.customizer.month-week-view' );
	}

	/**
	 * Grab the CSS rules template
	 *
	 * @return string
	 */
	public function get_css_template( $template ) {
		$customizer = Tribe__Customizer::instance();

		if ( $customizer->has_option( $this->ID, 'table_bg_color' ) ) {
			$template .= '
				#tribe-events .tribe-events-calendar td.tribe-events-othermonth,
				#tribe-events .tribe-grid-allday,
				#tribe-events .tribe-week-today,
				#tribe-events .tribe-events-calendar td:hover {
					background-color: <%= month_week_view.cell_inactive_bg_color %>;
				}

				#tribe-events .tribe-events-calendar td,
				#tribe-events .tribe-week-grid-block div,
				#tribe-events .tribe-events-grid,
				#tribe-events .tribe-grid-allday,
				#tribe-events .tribe-events-grid .tribe-scroller,
				#tribe-events .tribe-events-grid .tribe-grid-body .column,
				#tribe-events .tribe-events-grid .tribe-grid-allday .column {
					border-color: <%= month_week_view.border_dark_color %>;
				}

				.events-archive.events-gridview #tribe-events-content table .type-tribe_events {
					border-color: <%= month_week_view.border_light_color %>;
				}

				.tribe-events-calendar td.tribe-events-past div[id*="tribe-events-daynum-"],
				.tribe-events-calendar td.tribe-events-past div[id*="tribe-events-daynum-"] > a {
					background-color: <%= month_week_view.cell_inactive_header_bg_color %>;
				}

				.tribe-events-calendar div[id*="tribe-events-daynum-"],
				.tribe-events-calendar div[id*="tribe-events-daynum-"] a {
					background-color: <%= month_week_view.cell_header_bg_color %>;
				}

				.tribe-events-calendar thead th,
				.tribe-events-grid .tribe-grid-header .tribe-grid-content-wrap .column,
				.tribe-grid-header {
					background-color: <%= month_week_view.table_header_bg_color %>;
					border-left-color: <%= month_week_view.table_header_bg_color %>;
					border-right-color: <%= month_week_view.table_header_bg_color %>;
				}
			';
		}

		if ( $customizer->has_option( $this->ID, 'highlight_color' ) ) {
			$template .= '
				#tribe-events td.tribe-events-present div[id*="tribe-events-daynum-"],
				#tribe-events td.tribe-events-present div[id*="tribe-events-daynum-"] > a {
					background-color: <%= month_week_view.highlight_color %>;
					color: #fff;
				}

				#tribe-events .tribe-events-grid .tribe-grid-header div.tribe-week-today {
					background-color: <%= month_week_view.highlight_color %>;
				}

				.tribe-grid-allday .tribe-events-week-allday-single,
				.tribe-grid-body .tribe-events-week-hourly-single,
				.tribe-grid-allday .tribe-events-week-allday-single:hover,
				.tribe-grid-body .tribe-events-week-hourly-single:hover {
					background-color: <%= month_week_view.highlight_color %>;
					background-color: <%= month_week_view.highlight_color_rgba %>;
					border-color: <%= month_week_view.highlight_border_color %>
				}

			';
		}

		return $template;
	}

	public function create_ghost_settings( $settings = array() ) {
		if ( ! empty( $settings['table_bg_color'] ) ) {
			$table_bg_color = new Tribe__Utils__Color( $settings['table_bg_color'] );

			$settings['table_header_bg_color'] = '#' . $table_bg_color->darken( 70 );

			$settings['border_light_color'] = '#' . $table_bg_color->darken( 8 );
			$settings['border_dark_color'] = '#' . $table_bg_color->darken( 15 );

			$settings['cell_inactive_bg_color'] = '#' . $table_bg_color->darken( 3 );
			$settings['cell_inactive_header_bg_color'] = '#' . $table_bg_color->darken( 15 );
			$settings['cell_header_bg_color'] = '#' . $table_bg_color->darken( 30 );

		}

		if ( ! empty( $settings['highlight_color'] ) ) {
			$highlight_color = new Tribe__Utils__Color( $settings['highlight_color'] );

			$settings['highlight_color_rgba'] = 'rgba( ' . implode( ', ', $highlight_color->getRgb() ) . ', .75 )';
			$settings['highlight_border_color'] = '#' . $highlight_color->darken( 15 );
		}

		return $settings;
	}

	/**
	 * A way to apply filters when getting the Customizer options
	 * @return array
	 */
	public function setup() {
		$this->defaults = array(
			'table_bg_color' => '#f9f9f9',
			'highlight_color' => '#21759b',
		);

		$this->arguments = array(
			'priority'    => 30,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'Month View', 'the-events-calendar' ),
			'description' => esc_html__( 'Options selected here will override what was selected in the "General Theme" and "Global Elements" sections', 'the-events-calendar' ),
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
			$customizer->get_setting_name( 'table_bg_color', $section ),
			array(
				'default'              => $this->get_default( 'table_bg_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'table_bg_color', $section ),
				array(
					'label'   => __( 'Calendar Table Color' ),
					'section' => $section->id,
				)
			)
		);

		$manager->add_setting(
			$customizer->get_setting_name( 'highlight_color', $section ),
			array(
				'default'              => $this->get_default( 'highlight_color' ),
				'type'                 => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			)
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'highlight_color', $section ),
				array(
					'label'   => __( 'Calendar Highlight Color' ),
					'section' => $section->id,
				)
			)
		);

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'table_bg_color', $section ) );
		$customizer->add_setting_name( $customizer->get_setting_name( 'highlight_color', $section ) );
	}

}
