<?php

if ( ! function_exists( 'Tribe_Events_Importer_Load' ) ) {

	function Tribe_Events_Importer_Load() {
		TribeEventsImporter_Plugin::set_plugin_basename( plugin_basename( __FILE__ ) );
		if ( is_admin() ) {
			add_action( 'init', array( 'TribeEventsImporter_Plugin', 'initialize_admin' ), 10, 0 );
		}
	}

	add_action( 'plugins_loaded', 'Tribe_Events_Importer_Load' );

	function TribeEventsImporter_autoload( $classname ) {
		if ( strpos( $classname, 'TribeEventsImporter' ) === 0 ) {
			$path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $classname . '.php';
			if ( file_exists( $path ) ) {
				include( $path );
			}
		}
	}

	spl_autoload_register( 'TribeEventsImporter_autoload' );
}