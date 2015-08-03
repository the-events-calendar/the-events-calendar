<?php
class Tribe__Events__Admin__Timezone_Settings {


	public function __construct() {
		$this->listen();
		add_action( 'wp_ajax_tribe_timezone_update', array( $this, 'ajax_updater' ) );
		add_filter( 'tribe_general_settings_tab_fields', array( $this, 'settings_ui' ) );
	}

	public function settings_ui( array $general_settings ) {
		$updater = new Tribe__Events__Admin__Timezone_Updater;

		// Load all timezone settings
		$timezone_settings = $this->get_settings_array();

		// Remove unneeded options: until timezone data has been updated, users should only see the update
		// button - after that point, they should see the "real" settings but not the update button
		if ( $updater->update_needed() ) {
			unset( $timezone_settings[ 'tribe_events_timezone_mode' ] );
			unset( $timezone_settings[ 'tribe_events_timezones_show_zone' ] );
		} else {
			unset( $timezone_settings[ 'tribe_events_enable_timezones' ] );
		}

		// Add the new section just before the settings form is closed
		return Tribe__Events__Main::array_insert_before_key(
			'tribe-form-content-end',
			$general_settings,
			$timezone_settings
		);
	}

	protected function get_settings_array() {
		$plugin_path = Tribe__Events__Main::instance()->pluginPath;
		return (array) include $plugin_path . 'src/admin-views/tribe-options-timezones.php';
	}

	protected function listen() {
		// Sanity check
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'timezone-settings' ) ) {
			return;
		}

		// Update request?
		if ( isset( $_GET['timezone-update'] ) ) {
			$updater = new Tribe__Events__Admin__Timezone_Updater;
			$updater->init_update();
		}
	}

	function ajax_updater() {
		if ( ! isset( $_POST['check'] ) || ! wp_verify_nonce( $_POST['check'], 'timezone-settings' ) ) {
			return;
		}

		$updater = new Tribe__Events__Admin__Timezone_Updater;
		$updater->init_update();

		exit( json_encode( array(
			'html'     => $updater->notice_inner(),
			'continue' => $updater->update_needed()
		) ) );
	}
}