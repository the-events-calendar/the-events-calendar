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

		$this->assets();
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

