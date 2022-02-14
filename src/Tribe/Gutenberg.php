<?php
_deprecated_file( __FILE__, '5.14.0', 'Tribe__Editor' );

class Tribe__Events__Gutenberg {
	/**
	 * Extension hooks and initialization; exits if the extension is not authorized by Tribe Common to run.
	 *
	 * @since  4.6.13
	 *
	 * @deprecated 5.14.0
	 */
	public function hook() {
		_deprecated_function( __METHOD__, '5.14.0', 'Use Tribe__Editor instead.' );
		// Bail if we already have the extension
		if ( $this->is_extension_active() ) {
			return;
		}

		// Bail if Gutenberg is not active
		if ( ! tribe( 'editor' )->should_load_blocks() ) {
			return;
		}
	}

	/**
	 * Checks if we have The Events Calendar Gutenberg Extension active
	 *
	 * @since  4.6.13
	 *
	 * @deprecated 5.14.0
	 *
	 * @return boolean
	 */
	public function is_extension_active() {
		_deprecated_function( __METHOD__, '5.14.0', 'Use Tribe__Editor functionality instead.' );
		return class_exists( 'Tribe__Gutenberg__Plugin' );
	}

	/**
	 * Checks if we have Gutenberg Project online, only useful while
	 * its a external plugin
	 *
	 * @todo   Revise when Gutenberg is merged into core
	 *
	 * @since  4.6.13
	 *
	 * @deprecated 5.14.0
	 *
	 * @return boolean
	 */
	public function is_gutenberg_active() {
		_deprecated_function( __METHOD__, '5.14.0', 'Use Tribe__Editor instead.' );
		return function_exists( 'the_gutenberg_project' );
	}

	/**
	 * Checks if we have Editor Block active
	 *
	 * @since  4.6.13
	 *
	 * @deprecated 5.14.0
	 *
	 * @return boolean
	 */
	public function is_blocks_editor_active() {
		_deprecated_function( __METHOD__, '5.14.0', 'Use Tribe__Editor functionality instead.' );
		return function_exists( 'register_block_type' ) && function_exists( 'unregister_block_type' );
	}

	/**
	 * Checks if we should display Event Metabox fields
	 *
	 * Currently only used for fields that we want to hide because they
	 * already have a block to replace.
	 *
	 * @since  4.6.25
	 *
	 * @deprecated 5.14.0
	 *
	 * @return boolean
	 */
	public function should_display() {
		_deprecated_function( __METHOD__, '5.14.0', 'Use Tribe__Editor instead.' );
		// Hide when all of these three are active
		return ! ( tribe( 'editor' )->should_load_blocks() );
	}

	/**
	 * Checks if we are on the classic editor page
	 *
	 * @since  4.6.26
	 *
	 * @return boolean
	 */
	public function is_classic_editor_page() {
		_deprecated_function( __METHOD__, '5.14.0', 'Use Tribe__Editor functionality instead.' );
		$on_classic_editor_page = tribe_get_request_var( 'classic-editor', false );

		// Bail if in classic editor
		if ( '' === $on_classic_editor_page || $on_classic_editor_page ) {
			return true;
		}

		return false;
	}

	/**
	 * HTML for the notice from Gutenberg Extension download
	 *
	 * @since  4.6.13
	 *
	 * @deprecated 5.14.0
	 *
	 * @return string
	 */
	public function notice() {
		_deprecated_function( __METHOD__, '5.14.0', 'Use Tribe__Editor instead.' );
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		$url = 'http://evnt.is/19zc';
		$link = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( $url ),
			esc_html__( 'Download our Events Gutenberg extension and start using the block editor on your events!', 'the-events-calendar' )
		);
		$text = __( 'Looks like you are using Gutenberg on this WordPress installation. %1$s', 'the-events-calendar' );

		return sprintf( $text, $link );
	}
}
