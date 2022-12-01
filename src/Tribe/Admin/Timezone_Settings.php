<?php
/**
 * Manages the admin settings UI in relation to timezones.
 */
class Tribe__Events__Admin__Timezone_Settings {
	/**
	 * Sets up the display of timezone-related settings and listeners to deal with timezone-update
	 * requests (which are initiated from within the settings screen).
	 */
	public function __construct() {
		$this->listen();
		add_action( 'wp_ajax_tribe_timezone_update', [ $this, 'ajax_updater' ] );
		add_filter( 'tec_events_display_settings_tab_fields', [ $this, 'settings_ui' ], 20 );
	}

	/**
	 * Adds timezone settings to the *Display* settings tab.
	 *
	 * When it is determined that timezone data still needs to be generated for one or more
	 * events then only the update tool will be exposed in this area, in all other cases this
	 * is not exposed and the ordinary timezone settings will be visible.
	 *
	 * @param array $display_settings
	 *
	 * @return array
	 */
	public function settings_ui( array $display_settings ) {
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
		return Tribe__Main::array_insert_before_key(
			'tribe-events-currency-title',
			$display_settings,
			$timezone_settings
		);
	}

	/**
	 * Loads the timezone settings from an admin-view file and returns them as an array.
	 *
	 * @return array
	 */
	protected function get_settings_array() {
		$plugin_path = Tribe__Events__Main::instance()->plugin_path;
		return (array) include $plugin_path . 'src/admin-views/tribe-options-timezones.php';
	}

	/**
	 * Accommodates timezone update requests.
	 *
	 * Usually, the result is that an initial batch of events will be updated and any
	 * remaining events will be dealt with by an "ajax loop" - however in the event
	 * of a JS conflict this could actually be called repeatedly (by the user simply
	 * clicking the "Update Timezone Data" button until it is cleared.
	 */
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

	/**
	 * Facilitates updates of timezone data via an ajax loop.
	 *
	 * This approach helps to avoid potential timeout issues on sites with poor performance
	 * or large numbers of events, besides facilitating visual feedback as to progress.
	 */
	public function ajax_updater() {
		if ( ! isset( $_POST['check'] ) || ! wp_verify_nonce( $_POST['check'], 'timezone-settings' ) ) {
			return;
		}

		$updater = new Tribe__Events__Admin__Timezone_Updater;
		$updater->init_update();

		wp_send_json( [
			'html'     => $updater->notice_inner(),
			'continue' => $updater->update_needed(),
		] );
	}
}
