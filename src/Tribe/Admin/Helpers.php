<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class with a few helpers for the Administration Pages
 */
class Tribe__Events__Admin__Helpers {
	/**
	 * Static Singleton Holder
	 * @var Tribe__Events__Admin__Helpers|null
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Admin__Helpers
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}


	/**
	 * Matcher for Admin Pages related to Post Types
	 *
	 * @param string|array|null $id What will be checked to see if we return true or false
	 *
	 * @return boolean
	 */
	public function is_post_type_screen( $post_type = null ) {
		global $current_screen;

		// Not in the admin we don't even care
		if ( ! is_admin() ) {
			return false;
		}

		// Not doing AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		// Avoid Notices by checking the object type of WP_Screen
		if ( ! ( $current_screen instanceof WP_Screen ) ) {
			return false;
		}

		$defaults = array(
			Tribe__Events__Main::POSTTYPE,
			Tribe__Events__Main::VENUE_POST_TYPE,
			Tribe__Events__Main::ORGANIZER_POST_TYPE,
		);

		// Match any Post Type form Tribe
		if ( is_null( $post_type ) && in_array( $current_screen->post_type, $defaults ) ) {
			return true;
		}

		// Match any of the post_types set
		if ( ! is_scalar( $post_type ) && in_array( $current_screen->post_type, (array) $post_type ) ) {
			return true;
		}

		// Match a specific Post Type
		if ( $current_screen->post_type === $post_type ) {
			return true;
		}

		return false;
	}

	/**
	 * Matcher for administration pages that are from Tribe the easier way
	 *
	 * @param  string|array|null $id What will be checked to see if we return true or false
	 *
	 * @return boolean
	 */
	public function is_screen( $id = null ) {
		global $current_screen;

		// Not in the admin we don't even care
		if ( ! is_admin() ) {
			return false;
		}

		// Not doing AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		// Avoid Notices by checking the object type of WP_Screen
		if ( ! ( $current_screen instanceof WP_Screen ) ) {
			return false;
		}

		// Match any screen from Tribe
		if ( is_null( $id ) && false !== strpos( $current_screen->id, 'tribe' ) ) {
			return true;
		}

		// Match any of the pages set
		if ( ! is_scalar( $id ) && in_array( $current_screen->id, (array) $id ) ) {
			return true;
		}

		// Match a specific page
		if ( $current_screen->id === $id ) {
			return true;
		}

		return false;
	}

	/**
	 * Matcher for administration pages action
	 *
	 * @param  string|array|null $action What will be checked to see if we return true or false
	 *
	 * @return boolean
	 */
	public function is_action( $action = null ) {
		global $current_screen;

		// Not in the admin we don't even care
		if ( ! is_admin() ) {
			return false;
		}

		// Not doing AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		// Avoid Notices by checking the object type of WP_Screen
		if ( ! ( $current_screen instanceof WP_Screen ) ) {
			return false;
		}

		// Match any of the pages set
		if ( ! is_scalar( $action ) && in_array( $current_screen->action, (array) $action ) ) {
			return true;
		}

		// Match a specific page
		if ( $current_screen->action === $action ) {
			return true;
		}

		return false;
	}

}
