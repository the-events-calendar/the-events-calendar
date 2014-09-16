<?php

/**
 * Update class, contains functions to run on update
 */
class TribeEventsUpdate {

	var $slug = 'TribeEventsCalendar';

	function __construct() {
		add_action( 'tribe_events_suite_upgrade', array( $this, 'update_dispatch' ), 10, 5 );
	}

	/**
	 * Dispatch the update functions
	 *
	 * @return bool
	 **/
	public function update_dispatch( $updated, $slug, $plugin_name, $new_version, $old_version ) {
		if ( $slug == $this->slug ) {

			$from_version_func = 'update_from_' . str_replace( '.', '_', $old_version );
			$to_version_func   = 'update_to_' . str_replace( '.', '_', $new_version );

			if ( method_exists( $this, $from_version_func ) ) {
				$updated = call_user_func( array( $this, $from_version_func ), $new_version );
			}

			if ( method_exists( $this, $to_version_func ) ) {
				$updated = call_user_func( array( $this, $to_version_func ), $old_version );
			}
		}

		return $updated;
	}

	/**
	 * Update to version 3.1
	 *
	 * @return bool
	 **/
	function update_to_3_1( $old_version ) {
		// flush rewrite rules needed when updating to 3.1
		flush_rewrite_rules();

		return true;
	}

	/**
	 * Update to version 3.8
	 *
	 * @return bool
	 **/
	function update_to_3_8( $old_version ) {
		// flush rewrite rules needed when updating to 3.8
		flush_rewrite_rules();

		return true;
	}
}

new TribeEventsUpdate();