<?php
class Tribe__Events__Gutenberg {
	/**
	 * Extension hooks and initialization; exits if the extension is not authorized by Tribe Common to run.
	 *
	 * @since  4.6.13
	 */
	public function hook() {
		// Bail if we already have the extension
		if ( $this->is_extension_active() ) {
			return;
		}

		// Bail if Gutenberg is not active
		if ( ! $this->is_gutenberg_active() || ! $this->is_blocks_editor_active() ) {
			return;
		}

		tribe_notice(
			'gutenberg-extension',
			array( $this, 'notice' ),
			array(
				'type' => 'warning',
				'dismiss' => 1,
				'wrap' => 'p',
			)
		);
	}

	/**
	 * Checks if we have The Events Calendar Gutenberg Extension active
	 *
	 * @since  4.6.13
	 *
	 * @return boolean
	 */
	public function is_extension_active() {
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
	 * @return boolean
	 */
	public function is_gutenberg_active() {
		return function_exists( 'the_gutenberg_project' );
	}

	/**
	 * Checks if we have Editor Block active
	 *
	 * @since  4.6.13
	 *
	 * @return boolean
	 */
	public function is_blocks_editor_active() {
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
	 * @return boolean
	 */
	public function should_display() {
		// Hide when all of these three are active
		return ! ( $this->is_gutenberg_active() && $this->is_extension_active() && $this->is_blocks_editor_active() );
	}

	/**
	 * Checks if we are on the classic editor page
	 *
	 * @since  4.6.26
	 *
	 * @return boolean
	 */
	public function is_classic_editor_page() {
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
	 * @return string
	 */
	public function notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		$url = 'http://m.tri.be/19zc';
		$link = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( $url ),
			esc_html__( 'Download our Events Gutenberg extension and start using the block editor on your events!', 'the-events-calendar' )
		);
		$text = __( 'Looks like you are using Gutenberg on this WordPress installation. %1$s', 'the-events-calendar' );

		return sprintf( $text, $link );
	}
}
