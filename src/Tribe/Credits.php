<?php

/**
 * Handles output of The Events Calendar credtis
 */
class Tribe__Events__Credits {

	/**
	 * @var $instance
	 */
	private static $instance = null;

	public static function init() {
		self::instance()->hook();
	}

	/**
	 * Hook the functionality of this class into the world
	 */
	public function hook() {
		add_filter( 'tribe_events_after_html', array( $this, 'html_comment_credit' ) );
		add_filter( 'admin_footer_text', array( $this, 'rating_nudge' ), 1, 2 );
	}

	/**
	 * Add credit in HTML page source
	 *
	 * @return void
	 **/
	public function html_comment_credit( $after_html ) {
		$html_credit = "\n<!--\n" . __( 'This calendar is powered by The Events Calendar.', 'tribe-events-calendar' ) . "\nhttp://eventscalendarpro.com/\n-->\n";
		$after_html .= apply_filters( 'tribe_html_credit', $html_credit );
		return $after_html;
	}

	/**
	 * Add ratings nudge in admin footer
	 *
	 * @param $footer_text
	 *
	 * @return string
	 */
	public function rating_nudge( $footer_text ) {
		$admin_helpers = Tribe__Events__Admin__Helpers::instance();

		// only display custom text on Tribe Admin Pages
		if ( $admin_helpers->is_screen() || $admin_helpers->is_post_type_screen() ) {
			$footer_text = sprintf( __( 'Rate <strong>The Events Calendar</strong> <a href="%1$s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%1$s" target="_blank">WordPress.org</a> to keep this plugin free.  Thanks from the friendly folks at Modern Tribe.', 'tribe-events-calendar' ), __( 'http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5', 'tribe-events-calendar' ) );
		}

		return $footer_text;
	}

	/**
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
