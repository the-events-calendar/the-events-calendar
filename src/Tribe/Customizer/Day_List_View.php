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
		// Sanity check.
		if ( tribe_events_views_v2_is_enabled() ) {
			return $template;
		}

		$customizer = tribe( 'customizer' );

		if ( $customizer->has_option( $this->ID, 'price_bg_color' ) ) {
			$template .= '
				.tribe-events-list .tribe-events-event-cost span {
					background-color: <%= day_list_view.price_bg_color %>;
					border-color: <%= day_list_view.price_border_color %>;
					color: <%= day_list_view.price_color %>;
				}
			';
		}

		return $template;
	}

	public function create_ghost_settings( $settings = [] ) {

		if ( ! empty( $settings['price_bg_color'] ) ) {
			$price_bg_color = new Tribe__Utils__Color( $settings['price_bg_color'] );

			$settings['price_border_color'] = '#' . $price_bg_color->darken( 15 );
			if ( $price_bg_color->isDark() ) {
				$settings['price_color'] = '#f9f9f9';
			} else {
				$settings['price_color'] = '#333333';
			}
		}

		return $settings;
	}

	public function setup() {
		$this->defaults = [
			'price_bg_color' => '#eeeeee',
		];

		ob_start();
		?>
		<p>
			<?php esc_html_e( 'Options selected here will override what was selected in the "General Theme" and "Global Elements" sections.', 'the-events-calendar' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'These settings impact all list-style views, including List View and Day View.', 'the-events-calendar' ); ?>
		</p>
		<?php
		$description = ob_get_clean();

		$this->arguments = [
			'priority'    => 40,
			'capability'  => 'edit_theme_options',
			'title'       => esc_html__( 'List-style Views', 'the-events-calendar' ),
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

		$manager->add_setting(
			$customizer->get_setting_name( 'price_bg_color', $section ),
			[
				'default' => $this->get_default( 'price_bg_color' ),
				'type'    => 'option',

				'sanitize_callback'    => 'sanitize_hex_color',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
			]
		);

		$manager->add_control(
			new WP_Customize_Color_Control(
				$manager,
				$customizer->get_setting_name( 'price_bg_color', $section ),
				[
					'label'   => esc_html__( 'Price Background Color', 'the-events-calendar' ),
					'section' => $section->id,
				]
			)
		);

		// Introduced to make Selective Refresh have less code duplication
		$customizer->add_setting_name( $customizer->get_setting_name( 'price_bg_color', $section ) );
	}
}
