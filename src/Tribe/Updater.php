<?php


/**
 * Run schema updates on plugin activation or updates
 */
class Tribe__Events__Updater {
	protected $version_option = 'schema-version';
	protected $reset_version = '3.9'; // when a reset() is called, go to this version
	protected $current_version = 0;
	public $capabilities;

	public function __construct( $current_version ) {
		$this->current_version = $current_version;
	}


	/**
	 * We've had problems with the notoptions and
	 * alloptions caches getting out of sync with the DB,
	 * forcing an eternal update cycle
	 *
	 */
	protected function clear_option_caches() {
		wp_cache_delete( 'notoptions', 'options' );
		wp_cache_delete( 'alloptions', 'options' );
	}

	public function do_updates() {
		$this->clear_option_caches();
		$updates = $this->get_updates();
		uksort( $updates, 'version_compare' );

		try {
			foreach ( $updates as $version => $callback ) {

				if ( ! $this->is_new_install() && version_compare( $version, $this->current_version, '<=' ) && $this->is_version_in_db_less_than( $version ) ) {
					call_user_func( $callback );
				}
			}

			foreach ( $this->constant_updates() as $callback )  {
				call_user_func( $callback );
			}

			$this->update_version_option( $this->current_version );
		} catch ( Exception $e ) {
			// fail silently, but it should try again next time
		}
	}

	public function update_version_option( $new_version ) {
		Tribe__Settings_Manager::set_option( $this->version_option, $new_version );
	}

	/**
	 * Returns an array of callbacks with version strings as keys.
	 * Any key higher than the version recorded in the DB
	 * and lower than $this->current_version will have its
	 * callback called.
	 *
	 * This method has been deprecated in favor of a more testable public function
	 *
	 * @return array
	 * @deprecated 4.0
	 */
	protected function get_updates() {
		return $this->get_update_callbacks();
	}

	/**
	 * Returns an array of callbacks with version strings as keys.
	 * Any key higher than the version recorded in the DB
	 * and lower than $this->current_version will have its
	 * callback called.
	 *
	 * @return array
	 */
	public function get_update_callbacks() {
		return array(
			'2.0.1'  => array( $this, 'migrate_from_sp_events' ),
			'2.0.6'  => array( $this, 'migrate_from_sp_options' ),
			'3.10a4' => array( $this, 'set_enabled_views' ),
			'3.10a5' => array( $this, 'remove_30_min_eod_cutoffs' ),
			'4.2'    => array( $this, 'migrate_import_option' ),
			'4.6.23' => array( $this, 'migrate_wordpress_custom_field_option' ),
		);
	}

	/**
	 * Returns an array of callbacks that should be called
	 * every time the version is updated
	 *
	 * This method has been deprecated in favor of a more testable public function
	 *
	 * @return array
	 * @deprecated 4.0
	 */
	protected function constant_updates() {
		return $this->get_constant_update_callbacks();
	}

	/**
	 * Returns an array of callbacks that should be called
	 * every time the version is updated
	 *
	 * @return array
	 */
	public function get_constant_update_callbacks() {
		return array(
			array( $this, 'flush_rewrites' ),
			array( $this, 'set_capabilities' ),
		);
	}

	public function get_version_from_db() {
		return Tribe__Settings_Manager::get_option( $this->version_option );
	}

	/**
	 * Returns true if the version in the DB is less than the provided version
	 *
	 * @return boolean
	 */
	public function is_version_in_db_less_than( $version ) {
		$version_in_db = $this->get_version_from_db();

		return ( version_compare( $version, $version_in_db ) > 0 );
	}

	/**
	 * Returns true if this is a new install
	 *
	 * @return boolean
	 */
	public function is_new_install() {
		$version_in_db = $this->get_version_from_db();

		return empty( $version_in_db );
	}

	/**
	 * Returns true if an update is required
	 *
	 * @return boolean
	 */
	public function update_required() {
		return $this->is_version_in_db_less_than( $this->current_version );
	}

	public function migrate_from_sp_events() {
		$legacy_option = get_option( 'sp_events_calendar_options' );
		if ( empty( $legacy_option ) ) {
			return;
		}

		$new_option = get_option( Tribe__Events__Main::OPTIONNAME );
		if ( ! $new_option ) {
			update_option( Tribe__Events__Main::OPTIONNAME, $legacy_option );
		}
		delete_option( 'sp_events_calendar_options' );

		/** @var wpdb $wpdb */
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ( 'sp_events', 'sp_venue', 'sp_organizer' )" );
		if ( ! $count ) {
			return;
		}

		// update post type names
		$wpdb->update( $wpdb->posts, array( 'post_type' => Tribe__Events__Main::POSTTYPE ), array( 'post_type' => 'sp_events' ) );
		$wpdb->update( $wpdb->posts, array( 'post_type' => Tribe__Events__Main::VENUE_POST_TYPE ), array( 'post_type' => 'sp_venue' ) );
		$wpdb->update( $wpdb->posts, array( 'post_type' => Tribe__Events__Main::ORGANIZER_POST_TYPE ), array( 'post_type' => 'sp_organizer' ) );

		// update taxonomy names
		$wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => Tribe__Events__Main::TAXONOMY ), array( 'taxonomy' => 'sp_events_cat' ) );
		wp_cache_flush();
	}

	public function migrate_from_sp_options() {
		$tec_options = Tribe__Settings_Manager::get_options();
		$option_names     = array(
			'spEventsTemplate'   => 'tribeEventsTemplate',
			'spEventsBeforeHTML' => 'tribeEventsBeforeHTML',
			'spEventsAfterHTML'  => 'tribeEventsAfterHTML',
		);
		foreach ( $option_names as $old_name => $new_name ) {
			if ( isset( $tec_options[ $old_name ] ) && empty( $tec_options[ $new_name ] ) ) {
				$tec_options[ $new_name ] = $tec_options[ $old_name ];
				unset( $tec_options[ $old_name ] );
			}
		}
		Tribe__Settings_Manager::set_options( $tec_options );
	}

	public function flush_rewrites() {
		// run after 'init' to ensure that all CPTs are registered
		add_action( 'wp_loaded', 'flush_rewrite_rules' );
	}

	public function set_capabilities() {
		$this->capabilities = new Tribe__Events__Capabilities();
		add_action( 'wp_loaded', array( $this->capabilities, 'set_initial_caps' ) );
		add_action( 'wp_loaded', array( $this, 'reload_current_user' ), 11, 0 );
	}

	/**
	 * Reset the $current_user global after capabilities have been changed
	 *
	 */
	public function reload_current_user() {
		global $current_user;
		if ( isset( $current_user ) && ( $current_user instanceof WP_User ) ) {
			$id = $current_user->ID;
			$current_user = null;
			wp_set_current_user( $id );
		}
	}

	/**
	 * Reset update flags. All updates past $this->reset_version will
	 * run again on the next page load
	 *
	 */
	public function reset() {
		$this->update_version_option( $this->reset_version );
	}

	/**
	 * Make sure the tribeEnableViews option is always set
	 *
	 */
	public function set_enabled_views() {
		$enabled_views = tribe_get_option( 'tribeEnableViews', null );
		if ( $enabled_views == null ) {
			$views = wp_list_pluck( apply_filters( 'tribe-events-bar-views', array() ), 'displaying' );
			tribe_update_option( 'tribeEnableViews', $views );
		}
	}

	/**
	 * Bump the :30 min EOD cutoff option to the next full hour
	 *
	 */
	public function remove_30_min_eod_cutoffs() {
		$eod_cutoff = tribe_end_of_day();
		if ( Tribe__Date_Utils::minutes_only( $eod_cutoff ) == '29' ) {
			$eod_cutoff = date_create( '@' . ( strtotime( $eod_cutoff ) + 1 ) );
			$eod_cutoff->modify( '+30 minutes' );
			tribe_update_option( 'multiDayCutoff', $eod_cutoff->format( 'h:i' ) );
		}
	}

	/**
	 * Migrate the previous import mapping to the new naming and cleanup
	 * the old.
	 */
	public function migrate_import_option() {
		$legacy_option = get_option( 'tribe_events_import_column_mapping' );
		$type = get_option( 'tribe_events_import_type' );
		if ( empty( $legacy_option ) || empty( $type ) ) {
			return;
		}

		update_option( 'tribe_events_import_column_mapping_' . $type, $legacy_option );
		delete_option( 'tribe_events_import_column_mapping' );
	}

	/**
	 * Update WordPress Custom Field Setting moved from Pro
	 * only update setting if show|hide
	 *
	 * @since 4.6.23
	 */
	public function migrate_wordpress_custom_field_option() {
		$show_box = tribe_get_option( 'disable_metabox_custom_fields' );
		if ( 'show' === $show_box ) {
			tribe_update_option( 'disable_metabox_custom_fields', true );
		} elseif ( 'hide' === $show_box ) {
			tribe_update_option( 'disable_metabox_custom_fields', false );
		}
	}
}
