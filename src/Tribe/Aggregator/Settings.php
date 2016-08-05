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

	public function __construct() {
		if ( Tribe__Events__Aggregator__Service::instance()->api()->key ) {
			add_action( 'tribe_settings_do_tabs', array( $this, 'do_import_settings_tab' ) );
		}
	}

	public function do_import_settings_tab() {
		include_once Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/aggregator/settings.php';
	}

	public function get_all_default_settings() {
		$origins = array(
			'csv',
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

		$setting = tribe_get_option( 'tribe_aggregator_default_post_status', 'publish' );

		if ( $origin ) {
			$setting = tribe_get_option( "tribe_aggregator_default_{$origin}_post_status", $setting );
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

		$setting = tribe_get_option( 'tribe_aggregator_default_category', 'draft' );

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
