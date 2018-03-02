<?php

/**
 * The Events Calendar Customizer
 * Add "Main Events Page" to Homepage Selection
 *
 * @package The Events Calendar
 * @subpackage Customizer
 * @since TBD
 */
class Tribe__Events__Customizer__Front_Page_View extends Tribe__Customizer__Section {

	/**
	 * PHP 5.2 method of creating "instances" of an abstract require this
	 * Note: This is the only required method for a Connector to work
	 *
	 * @since TBD
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
	 * @since TBD
	 */
	public function setup() {
		add_filter( 'wp_dropdown_pages', array( $this, 'add_events_page_option' ), 10, 3 );
		add_action('customize_controls_print_scripts', array( $this, 'test' ) );
	}

	public function test() {
		wp_enqueue_script( 'jquery' );
	?>
		<script>
			(function($) {

				var $page_on_front      = $( document.getElementById( '_customize-input-page_on_front' ) );
				var $events_page_option = $page_on_front.find( 'option[value="main_events_page"]' );

				$page_on_front.find( ':selected' ).prop( 'selected', false );
				$events_page_option.prop( 'selected', true );

			})(jQuery);
		</script>
	<?php
	}

	/**
	 * Add "Main Events Page" option to the Customizer's "Homepage Settings" static-page dropdown.
	 *
	 * @since TBD
	 *
	 * @param string $output HTML output for drop down list of pages.
	 * @param array  $r      The parsed arguments array.
	 * @param array  $pages  List of WP_Post objects returned by `get_pages()`
	 *
	 * @return string
	 */
	public function add_events_page_option( $output, $r, $pages ) {

		// Ensures we don't show the "Main Events Page" option outside the Customizer pane.
		if ( ! is_customize_preview() ) {
			return $output;
		}

		// Ensures we only modify the Homepage dropdown, not the Blog page.
		if ( ! isset( $r['name'] ) || '_customize-dropdown-pages-page_on_front' !== $r['name'] ) {
			return $output;
		}

		$already_chosen = tribe_get_option( 'front_page_event_archive', false );

		$label = sprintf(
			esc_html_x( 'Main %s Page', 'Customizer static front page setting', 'the-events-calendar' ),
			tribe_get_event_label_plural()
		);

		$option = sprintf(
			'<option class="level-0" value="%1$s" selected="%2$s">%3$s</option></select>',
			'main_events_page',
			( $already_chosen ? 'selected' : '' ),
			$label
		);

		$output = str_replace( '</select>', $option, $output );

		return $output;
	}
}