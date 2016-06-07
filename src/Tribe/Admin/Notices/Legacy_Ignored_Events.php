<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Admin__Notices__Legacy_Ignored_Events extends Tribe__Admin__Notice__Abstract {
	/**
	 * On PHP 5.2 the child class doesn't get spawned on the Parent one, so we don't have
	 * access to that information on the other side unless we pass it around as a param
	 * so we throw __CLASS__ to the parent::instance() method to be able to spawn new instance
	 * of this class and save on the parent::$instances variable.
	 *
	 * @return Tribe__Events__Admin__Notices__Legacy_Ignored_Events
	 */
	public static function instance( $name = null ) {
		return parent::instances( __CLASS__ );
	}

	/**
	 * Method to get the Slug of this Notice
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'legacy-ignored-events';
	}

	/**
	 * Method returning a boolean to determine if the notice is visible
	 *
	 * @return boolean
	 */
	public function is_visible() {
		if ( ! Tribe__Admin__Helpers::instance()->is_post_type_screen( Tribe__Events__Main::POSTTYPE ) ) {
			return false;
		}

		if ( empty( $_GET['post_status'] ) || $_GET['post_status'] !== Tribe__Events__Ignored_Events::$ignored_status ) {
			return false;
		}

		if ( $this->has_user_dimissed() ) {
			return false;
		}

		if ( ! Tribe__Events__Ignored_Events::instance()->has_legacy_deleted_posts() ) {
			return false;
		}

		return true;
	}

	/**
	 * Display the Notice on the Admin page if `$this->is_visible()` returns true
	 *
	 * @return  void
	 */
	public function notice() {
		$html = '@TODO: Include a Cool message about why you are seen this Notice!';
		$button = '<p style="display:inline-block;">' . get_submit_button( esc_html__( 'Migrate Legacy Ignored Events' ), 'secondary', 'tribe-migrate-legacy-events', false ) . '<span class="spinner"></span>' . '</p>';
		echo sprintf( '<div id="message" class="notice notice-warning is-dismissible tribe-dismiss-notice" data-ref="%s"><p>%s</p>%s</div>', $this->get_slug(), $html, $button );
	}
}
