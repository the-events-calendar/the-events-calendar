<?php

if ( ! function_exists( 'Tribe_Events_Importer_Load' ) ) {

	function Tribe_Events_Importer_Load() {
		Tribe__Events__Importer__Plugin::set_plugin_basename( plugin_basename( __FILE__ ) );
		if ( is_admin() ) {
			add_action( 'init', array( 'Tribe__Events__Importer__Plugin', 'initialize_admin' ), 10, 0 );
		}
	}

	add_action( 'plugins_loaded', 'Tribe_Events_Importer_Load' );
}