<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Settings {
	/** * @var Tribe__Events__Aggregator__Settings Event Aggregator settings bootstrap class
	 */
	protected static $instance;

	/**
	 * Default update authority setting
	 *
	 * @var string
	 */
	public static $default_update_authority = 'overwrite';

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Aggregator__Settings
	 */
	public static function instance() {
		return tribe( 'events-aggregator.settings' );
	}

	/**
	 * A private method to prevent it to be created twice.
	 * It will add the methods and setup any dependecies
	 *
	 * Note: This should load on `plugins_loaded@P10`
	 */
	public function __construct() {
		add_action( 'tribe_settings_do_tabs', array( $this, 'do_import_settings_tab' ) );
		add_action( 'current_screen', array( $this, 'maybe_clear_fb_credentials' ) );
	}

	/**
	 * Hooked to current_screen, this method identifies whether or not fb credentials should be cleared
	 *
	 * @param WP_Screen $screen
	 */
	public function maybe_clear_fb_credentials( $screen ) {
		if ( 'tribe_events_page_tribe-common' !== $screen->base ) {
			return;
		}

		if ( ! isset( $_GET['tab'] ) || 'addons' !== $_GET['tab'] ) {
			return;
		}

		if (
			! (
				isset( $_GET['action'] )
				&& isset( $_GET['_wpnonce'] )
				&& 'disconnect-facebook' === $_GET['action']
				&& wp_verify_nonce( $_GET['_wpnonce'], 'disconnect-facebook' )
			)
		) {
			return;
		}

		$this->clear_fb_credentials();

		wp_redirect(
			Tribe__Settings::instance()->get_url( array( 'tab' => 'addons' ) )
		);
		die;
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

	public function clear_fb_credentials() {
		tribe_update_option( 'fb_token', null );
		tribe_update_option( 'fb_token_expires', null );
		tribe_update_option( 'fb_token_scopes', null );
	}

	/**
	 * Given a URL, tack on the parts of the URL that gets used to disconnect Facebook
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function build_disconnect_facebook_url( $url ) {
		return wp_nonce_url(
			add_query_arg(
				'action',
				'disconnect-facebook',
				$url
			),
			'disconnect-facebook'
		);
	}

	public function is_fb_credentials_valid( $time = null ) {
		// if the service hasn't enabled oauth for facebook, always assume it is valid
		if ( ! tribe( 'events-aggregator.main' )->api( 'origins' )->is_oauth_enabled( 'facebook' ) ) {
			return true;
		}

		if ( ! $this->has_fb_credentials() ) {
			return false;
		}

		$credentials = $this->get_fb_credentials();

		// Allow passing comparing time
		if ( is_null( $time ) ) {
			$time = time();
		}

		return $credentials->expires > $time;
	}

	public function do_import_settings_tab() {
		include_once Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/aggregator/settings.php';
	}

	public function get_all_default_settings() {
		$origins = array(
			'csv',
			'gcal',
			'ical',
			'ics',
			'facebook',
			'meetup',
			'url',
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
		$setting = tribe_get_option( 'tribe_aggregator_default_update_authority', self::$default_update_authority );

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
		$setting = $setting = tribe_get_option( 'tribe_aggregator_default_post_status', 'publish' );

		if ( $origin ) {
			$origin_setting = tribe_get_option( "tribe_aggregator_default_{$origin}_post_status", $setting );

			if ( ! empty( $origin_setting ) ) {
				$setting = $origin_setting;
			}
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
		$setting = tribe_get_option( 'tribe_aggregator_default_category', null );

		if ( $origin ) {
			$origin_setting = tribe_get_option( "tribe_aggregator_default_{$origin}_category", $setting );

			if ( ! empty( $origin_setting ) ) {
				$setting = $origin_setting;
			}
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
		$setting = tribe_get_option( 'tribe_aggregator_default_show_map', 'no' );

		if ( $origin ) {
			$origin_setting = tribe_get_option( "tribe_aggregator_default_{$origin}_show_map", $setting );

			if ( ! empty( $origin_setting ) ) {
				$setting = $origin_setting;
			}
		}

		return $setting;
	}

	/**
	 * Returns the default value for an origin regarding applicable event settings.
	 *
	 * Event setttings are those settings related to an event presentation like Show Google Map, Hide from Listings and so on.
	 *
	 * @param string $origin The origin to look up the settings for.
	 *
	 * @return string The option value.
	 */
	public function default_settings_import( $origin ) {
		// by default do not import the event settings
		$setting = tribe_get_option( "tribe_aggregator_default_{$origin}_import_event_settings", 'no' );

		return $setting;
	}

	/**
	 * Returns the range options available for URL imports.
	 *
	 * Titles are meant to be used in titles and make sense alone, range strings are meant to be used when using the
	 * duration in a sentence and do not make sense alone.
	 *
	 * @param bool $title Whether the values of the array should be for title or for use as range.
	 *
	 * @return array An associative array of durations and strings.
	 */
	public function get_url_import_range_options( $title = true ) {
		$options = array(
			DAY_IN_SECONDS          => array(
				'title' => __( '24 hours', 'the-events-calendar' ),
				'range' => __( '24 hours', 'the-events-calendar' ),
			),
			3 * DAY_IN_SECONDS      => array(
				'title' => __( '72 hours', 'the-events-calendar' ),
				'range' => __( '72 hours', 'the-events-calendar' ),
			),
			WEEK_IN_SECONDS         => array(
				'title' => __( 'One week', 'the-events-calendar' ),
				'range' => __( 'a week', 'the-events-calendar' ),
			),
			2 * WEEK_IN_SECONDS     => array(
				'title' => __( 'Two weeks', 'the-events-calendar' ),
				'range' => __( 'two weeks', 'the-events-calendar' ),
			),
			3 * WEEK_IN_SECONDS     => array(
				'title' => __( 'Three weeks', 'the-events-calendar' ),
				'range' => __( 'three weeks', 'the-events-calendar' ),
			),
			30 * DAY_IN_SECONDS     => array(
				'title' => __( 'One month', 'the-events-calendar' ),
				'range' => __( 'a month', 'the-events-calendar' ),
			),
			2 * 30 * DAY_IN_SECONDS => array(
				'title' => __( 'Two months', 'the-events-calendar' ),
				'range' => __( 'two months', 'the-events-calendar' ),
			),
			3 * 30 * DAY_IN_SECONDS => array(
				'title' => __( 'Three months', 'the-events-calendar' ),
				'range' => __( 'three months', 'the-events-calendar' ),
			),
		);

		/**
		 * Filters the options available for the URL import range.
		 *
		 * @param array $options An array of arrays in the format
		 *                       [ <range duration in seconds> => [ 'title' => <title>, 'range' => <range> ] ].
		 */
		$options = apply_filters( 'tribe_aggregator_url_import_range_options', $options );

		if ( $title ) {
			return array_combine( array_keys( $options ), wp_list_pluck( $options, 'title' ) );
		}

		return array_combine( array_keys( $options ), wp_list_pluck( $options, 'range' ) );
	}
}
