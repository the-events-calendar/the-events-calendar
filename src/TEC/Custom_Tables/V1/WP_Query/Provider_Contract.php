<?php
namespace TEC\Custom_Tables\V1\WP_Query;

interface Provider_Contract {
	/**
	 * Register the filters and bindings required to integrate the plugin custom tables in the normal
	 * WP_Query flow.
	 *
	 * @since TBD
	 */
	public function register();

	/**
	 * {@inheritdoc}
	 */
	public function unregister();
}
