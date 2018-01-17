<?php

abstract class Tribe__Events__Editor__Blocks__Abstract
implements Tribe__Events__Editor__Blocks__Interface {
	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since  TBD
	 *
	 * @param  array $attributes
	 *
	 * @return string
	 */
	abstract public function render( $attributes = array() );

	/**
	 * Sends a valid JSON response to the AJAX request for the block contents
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function ajax() {
		wp_send_json_error( esc_attr__( 'Problem loading the block, please remove this block to restart.', 'the-events-calendar' ) );
	}

	/**
	 * Fetches which ever is the plugin we are dealing with
	 *
	 * @since  TBD
	 *
	 * @return mixed
	 */
	public function plugin() {
		return Tribe__Events__Main::instance();
	}

	/**
	 * Does the registration for PHP rendering for the Block, important due to been
	 * an dynamic Block
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function register() {
		$block_args = array(
			'render_callback' => array( $this, 'render' ),
		);

		register_block_type( $this->name(), $block_args );

		add_action( 'wp_ajax_' . $this->get_ajax_action(), array( $this, 'ajax' ) );

		$this->assets();
	}

	/**
	 * Fetches the name for the block we are working with and converts it to the
	 * correct `wp_ajax_{$action}` string for us to Hook
	 *
	 * @since  TBD
	 *
	 * @return string
	 */
	public function get_ajax_action() {
		return str_replace( 'tribe/', 'tribe_editor_block_', $this->name() );
	}

	/**
	 * Used to include any Assets for the Block we are registering
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function assets() {

	}
}

