<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Settings {
	/** * @var Tribe__Events__Aggregator__Settings Event Aggregator settings bootstrap class
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Aggregator
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * A private method to prevent it to be created twice.
	 * It will add the methods and setup any dependecies
	 *
	 * Note: This should load on `plugins_loaded@P10`
	 */
	private function __construct() {
		add_action( 'tribe_settings_do_tabs', array( $this, 'do_import_settings_tab' ) );
	}

	public function get_fb_credentials() {
		$args = array(
			'token'   => tribe_get_option( 'fb_token' ),
			'expires' => tribe_get_option( 'fb_token_expires' ),
			'scopes'  => tribe_get_option( 'fb_token_scopes' ),
		);

		return (object) $args;
	}

	public function has_fb_credentials() {
		$credentials = $this->get_fb_credentials();
		return ! empty( $credentials->token ) && ! empty( $credentials->expires ) && ! empty( $credentials->scopes );
	}

	public function is_fb_credentials_valid( $time = null ) {
		if ( ! $this->has_fb_credentials() ) {
			return false;
		}

		$credentials = $this->get_fb_credentials();

		// Allow passing comparing time
		if ( is_null( $time ) ) {
			$time = time();
		}

		return $credentials->expires <= $time;
	}

	public function do_import_settings_tab() {
		include_once Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/aggregator/settings.php';
	}

	public function get_all_default_settings() {
		$origins = array(
			'csv',
			'gcal',
			'ical',
			'facebook',
			'meetup',
		);

		$settings = array();
		foreach ( $origins as $origin ) {
			$settings[ $origin ] = array(
				'post_status' => $this->default_post_status( $origin ),
				'category'    => $this->default_category( $origin ),
				'map'         => $this->default_map( $origin ),
			);
		}

		return $settings;
	}

	/**
	 * Returns the default update authority for imports
	 *
	 * Origin default settings trump global settings
	 *
	 * @param string $origin Origin
	 *
	 * @return string
	 */
	public function default_update_authority( $origin = null ) {
		$origin = $this->origin_translation( $origin );

		$setting = tribe_get_option( 'tribe_aggregator_default_update_authority', 'retain' );

		if ( $origin ) {
			$setting = tribe_get_option( "tribe_aggregator_default_{$origin}_update_authority", $setting );
		}

		return $setting;
	}

	/**
	 * Returns the default post status for imports
	 *
	 * Origin default settings trump global settings
	 *
	 * @param string $origin Origin
	 *
	 * @return string
	 */
	public function default_post_status( $origin = null ) {
		$origin = $this->origin_translation( $origin );

		$global_setting = $setting = tribe_get_option( 'tribe_aggregator_default_post_status', 'publish' );

		if ( $origin ) {
			$setting = tribe_get_option( "tribe_aggregator_default_{$origin}_post_status", $setting );
		}

		if ( ! $setting ) {
			$setting = $global_setting;
		}

		return $setting;
	}

	/**
	 * Returns the default category for imports
	 *
	 * Origin default settings trump global settings
	 *
	 * @param string $origin Origin
	 *
	 * @return string
	 */
	public function default_category( $origin = null ) {
		$origin = $this->origin_translation( $origin );

		$setting = tribe_get_option( 'tribe_aggregator_default_category', null );

		if ( $origin ) {
			$setting = tribe_get_option( "tribe_aggregator_default_{$origin}_category", $setting );
		}

		return $setting;
	}

	/**
	 * Returns the default map setting for imports
	 *
	 * Origin default settings trump global settings
	 *
	 * @param string $origin Origin
	 *
	 * @return string
	 */
	public function default_map( $origin = null ) {
		$origin = $this->origin_translation( $origin );

		$setting = tribe_get_option( 'tribe_aggregator_default_map', 'no' );

		if ( $origin ) {
			$setting = tribe_get_option( "tribe_aggregator_default_{$origin}_map", $setting );
		}

		return $setting;
	}

	/**
	 * Translates origins to origins used for settings
	 *
	 * Why? Because some origins are just aliases.
	 *
	 * @param string $origin Origin value
	 *
	 * @return string|null
	 */
	private function origin_translation( $origin ) {
		if ( 'ics' === $origin ) {
			$origin = 'ical';
		}

		return $origin;
	}
}
