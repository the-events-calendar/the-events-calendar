<?php


/**
 * Run schema updates on plugin activation or updates
 */
class Tribe__Events__Updater {
	protected $version_option = 'schema-version';
	protected $current_version = 0;

	public function __construct( $current_version ) {
		$this->current_version = $current_version;
	}


	/**
	 * We've had problems with the notoptions and
	 * alloptions caches getting out of sync with the DB,
	 * forcing an eternal update cycle
	 *
	 * @return void
	 */
	protected function clear_option_caches() {
		wp_cache_delete( 'notoptions', 'options' );
		wp_cache_delete( 'alloptions', 'options' );
	}

	public function do_updates() {
		$this->clear_option_caches();
		$updates = $this->get_updates();
		uksort($updates, 'version_compare');
		try {
			foreach ( $updates as $version => $callback ) {
				if ( version_compare( $version, $this->current_version, '<=' ) && $this->is_version_in_db_less_than($version) ) {
					call_user_func($callback);
				}
			}
			$this->update_version_option( $this->current_version );
		} catch ( \Exception $e ) {
			// fail silently, but it should try again next time
		}
	}

	protected function update_version_option( $new_version ) {
		$tec = TribeEvents::instance();
		$tec->setOption( $this->version_option, $new_version );
	}

	/**
	 * Returns an array of callbacks with version strings as keys.
	 * Any key higher than the version recorded in the DB
	 * and lower than $this->current_version will have its
	 * callback called.
	 *
	 * @return array
	 */
	protected function get_updates() {
		return array(
			'3.8' => array( $this, 'flush_rewrites' ),
			'3.9' => array( $this, 'set_capabilities' ),
		);
	}

	protected function is_version_in_db_less_than( $version ) {
		$tec = TribeEvents::instance();
		$version_in_db = $tec->getOption( $this->version_option );

		if ( version_compare( $version, $version_in_db ) > 0 ) {
			return TRUE;
		}
		return FALSE;
	}

	public function update_required() {
		return $this->is_version_in_db_less_than( $this->current_version );
	}

	public function flush_rewrites() {
		// run after 'init' to ensure that all CPTs are registered
		add_action( 'wp_loaded', 'flush_rewrite_rules' );
	}

	public function set_capabilities() {
		require_once( dirname( __FILE__ ) . '/Capabilities.php' );
		$capabilities = new Tribe__Events__Capabilities();
		add_action( 'wp_loaded', array( $capabilities, 'set_initial_caps' ) );
	}
}