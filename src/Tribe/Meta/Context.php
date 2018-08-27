<?php


/**
 * Class Tribe__Events__Meta__Context
 *
 * Abstracts a post save operation context.
 *
 * @since 4.2.5
 */
class Tribe__Events__Meta__Context {

	/**
	 * @return bool
	 */
	public function doing_ajax() {
		return defined( 'DOING_AJAX' );
	}

	/**
	 * @return bool
	 */
	public function is_bulk_editing() {
		return isset( $_GET['bulk_edit'] );
	}

	/**
	 * @return bool
	 */
	public function is_inline_save() {
		return ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' );
	}

	/**
	 * @return bool
	 */
	public function has_nonce() {
		return isset( $_POST['ecp_nonce'] );
	}

	/**
	 * @return false|int
	 */
	public function verify_nonce( ) {
		return wp_verify_nonce( $_POST['ecp_nonce'], Tribe__Events__Main::POSTTYPE );
	}

	/**
	 * Whether the current user has the specified capability or not.
	 *
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function current_user_can_edit_events( ) {
		return current_user_can( 'edit_tribe_events' );
	}
}