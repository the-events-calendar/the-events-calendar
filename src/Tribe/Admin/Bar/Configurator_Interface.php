<?php


interface Tribe__Events__Admin__Bar__Configurator_Interface {

	/**
	 * Configures an admin bar object adding menus, groups and nodes to it.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 *
	 * @return mixed
	 */
	public function configure( WP_Admin_Bar $wp_admin_bar );
}