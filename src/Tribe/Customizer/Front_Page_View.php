<?php

/**
 * The Events Calendar Customizer
 * Add "Main Events Page" to Homepage Selection
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since 4.6.12
 */
class Tribe__Events__Customizer__Front_Page_View extends Tribe__Customizer__Section {

	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 * Note: This is the only required method for a Connector to work
	 *
	 * @since 4.6.12
	 *
	 * @return self The dynamic instance of this Class
	 */
	public static function instance( $name = null ) {
		return parent::instance( __CLASS__ );
	}

	/**
	 * Filter the wp_dropdown_pages markup in the Customizer "Homepage Settings" screen so users can
	 * select "Main Events Page" as an option.
	 *
	 * @since 4.6.12
	 */
	public function setup() {
		add_filter( 'wp_dropdown_pages', array( $this, 'add_events_page_option' ), 10, 3 );
	}

	/**
	 * Add "Main Events Page" option to the Customizer's "Homepage Settings" static-page dropdown.
	 *
	 * @since 4.6.12
	 *
	 * @param string $output HTML output for drop down list of pages.
	 * @param array  $args   The parsed arguments array.
	 * @param array  $pages  List of WP_Post objects returned by `get_pages()`
	 *
	 * @return string
	 */
	public function add_events_page_option( $output, $args, $pages ) {

		// Ensures we only modify the Homepage dropdown, not the Blog page.
		$valid_names = array( '_customize-dropdown-pages-page_on_front', 'page_on_front' );
		if ( ! isset( $args['name'] ) || ! in_array( $args['name'], $valid_names ) ) {
			return $output;
		}

		$already_chosen = tribe_get_option( 'front_page_event_archive', false ) && -1 === (int) get_option( 'page_on_front' );

		$label = sprintf(
			esc_html_x( 'Main %s Page', 'Customizer static front page setting', 'the-events-calendar' ),
			tribe_get_event_label_plural()
		);

		$selected = $already_chosen ? 'selected' : '';
		$option = '<option class="level-0" value="-1" ' . $selected . '>' . $label . '</option></select>';
		$output = str_replace( '</select>', $option, $output );

		return $output;
	}
}