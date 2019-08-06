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
		add_action( 'tribe_settings_do_tabs', [ $this, 'do_import_settings_tab' ] );
		add_action( 'current_screen', [ $this, 'maybe_clear_eb_credentials' ] );
		add_action( 'current_screen', [ $this, 'maybe_clear_meetup_credentials' ] );
	}

	/**
	 * Hooked to current_screen, this method identifies whether or not eb credentials should be cleared
	 *
	 * @param WP_Screen $screen
	 */
	public function maybe_clear_eb_credentials( $screen ) {
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
				&& 'disconnect-eventbrite' === $_GET['action']
				&& wp_verify_nonce( $_GET['_wpnonce'], 'disconnect-eventbrite' )
			)
		) {
			return;
		}

		$this->clear_eb_credentials();

		wp_redirect(
			Tribe__Settings::instance()->get_url( [ 'tab' => 'addons' ] )
		);
		die;
	}

	/**
	 * Get EB Security Key
	 *
	 * @since 4.6.18
	 *
	 */
	public function get_eb_security_key() {
		$args = [
			'security_key' => tribe_get_option( 'eb_security_key' ),
		];

		return (object) $args;
	}

	/**
	 * Check if Security Key
	 *
	 * @since 4.6.18
	 *
	 */
	public function has_eb_security_key() {
		$credentials = $this->get_eb_security_key();

		return ! empty( $credentials->security_key );
	}

	/**
	 * Handle Checking if there is a Security Key and Saving It
	 *
	 * @since 4.6.18
	 *
	 * @param object $eb_authorized object from EA service for EB Validation
	 *
	 * @return bool
	 */
	public function handle_eventbrite_security_key( $eb_authorized ) {

		// key is sent on initial authorization and save it if we have it
		if ( ! empty( $eb_authorized->data->secret_key ) ) {
			tribe_update_option( 'eb_security_key', esc_attr( $eb_authorized->data->secret_key ) );

			return true;
		}


		if ( $this->has_eb_security_key() ) {
			return true;
		}

		return false;
	}

	/**
	 * Disconnect Eventbrite from EA
	 *
	 * @since 4.6.18
	 *
	 */
	public function clear_eb_credentials() {

		tribe( 'events-aggregator.service' )->disconnect_eventbrite_token();

		tribe_update_option( 'eb_security_key', null );

	}

	/**
	 * Given a URL, tack on the parts of the URL that gets used to disconnect Eventbrite
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function build_disconnect_eventbrite_url( $url ) {
		return wp_nonce_url(
			add_query_arg(
				'action',
				'disconnect-eventbrite',
				$url
			),
			'disconnect-eventbrite'
		);
	}

	/**
	 * Check if the Eventbrite credentials are connected in EA
	 *
	 * @return bool Whether the Eventbrite credentials are valid
	 */
	public function is_ea_authorized_for_eb() {
		// if the service hasn't enabled oauth for Eventbrite, always assume it is valid
		if ( ! tribe( 'events-aggregator.main' )->api( 'origins' )->is_oauth_enabled( 'eventbrite' ) ) {
			return true;
		}

		$eb_authorized = tribe( 'events-aggregator.service' )->has_eventbrite_authorized();

		if ( empty( $eb_authorized->status ) || 'success' !== $eb_authorized->status ) {
			return false;
		}

		if ( ! $this->handle_eventbrite_security_key( $eb_authorized ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Hooked to current_screen, this method identifies whether or not eb credentials should be cleared
	 *
	 * @since 4.9.6
	 *
	 * @param WP_Screen $screen The current screen instance.
	 */
	public function maybe_clear_meetup_credentials( $screen ) {
		if ( 'tribe_events_page_tribe-common' !== $screen->base ) {
			return;
		}

		if ( tribe_get_request_var( 'tab', false ) !== 'addons' ) {
			return;
		}

		$action = tribe_get_request_var( 'action' ) === 'disconnect-meetup';
		$nonce  = tribe_get_request_var( '_wpnonce' );

		if ( ! ( $action && $nonce && wp_verify_nonce( $nonce, 'disconnect-meetup' ) ) ) {
			return;
		}

		$this->clear_meetup_credentials();

		wp_redirect(
			Tribe__Settings::instance()->get_url( [ 'tab' => 'addons' ] )
		);
		die;
	}

	/**
	 * Get EB Security Key
	 *
	 * @since 4.9.6
	 *
	 */
	public function get_meetup_security_key() {
		$args = [
			'security_key' => tribe_get_option( 'meetup_security_key' ),
		];

		return (object) $args;
	}

	/**
	 * Check if Security Key
	 *
	 * @since 4.9.6
	 *
	 * @return bool
	 *
	 */
	public function has_meetup_security_key() {
		$credentials = $this->get_meetup_security_key();

		return ! empty( $credentials->security_key );
	}

	/**
	 * Handle Checking if there is a Security Key and Saving It
	 *
	 * @since 4.9.6
	 *
	 * @param object $eb_authorized object from EA service for Meetup Validation
	 *
	 * @return bool
	 */
	public function handle_meetup_security_key( $meetup_authorized ) {

		// key is sent on initial authorization and save it if we have it
		if ( ! empty( $meetup_authorized->data->secret_key ) ) {
			tribe_update_option( 'meetup_security_key', esc_attr( $meetup_authorized->data->secret_key ) );

			// If we have a Meetup OAuth flow security key, then we can remove the old Meetup API key, if any.
			tribe_update_option( 'meetup_api_key', '' );

			return true;
		}


		if ( $this->has_meetup_security_key() ) {
			return true;
		}

		return false;
	}

	/**
	 * Disconnect Meetup from EA
	 *
	 * @since 4.9.6
	 */
	public function clear_meetup_credentials() {

		tribe( 'events-aggregator.service' )->disconnect_meetup_token();

		tribe_update_option( 'meetup_security_key', null );
		delete_transient( Tribe__Events__Aggregator__Service::$auth_transient_meetup );

	}

	/**
	 * Given a URL, tack on the parts of the URL that gets used to disconnect Meetup
	 *
	 * @param string $url
	 *
	 * @since 4.9.6
	 *
	 * @return string The URL to issue a Meeetup disconnect request to EA Service.
	 */
	public function build_disconnect_meetup_url( $url ) {
		return wp_nonce_url(
			add_query_arg(
				'action',
				'disconnect-meetup',
				$url
			),
			'disconnect-meetup'
		);
	}

	/**
	 * Check if the Meetup API credentials are connected in EA and correctly set.
	 *
	 * @since 4.9.6
	 *
	 * @return bool Whether the Meetup credentials are valid or not.
	 */
	public function is_ea_authorized_for_meetup() {
		// If the service hasn't enabled oauth for Meetup, always assume it is valid.
		if ( ! tribe( 'events-aggregator.main' )->api( 'origins' )->is_oauth_enabled( 'meetup' ) ) {
			return true;
		}

		$request_secret_key = ! $this->has_meetup_security_key();
		$meetup_authorized  = tribe( 'events-aggregator.service' )->has_meetup_authorized( $request_secret_key );

		if ( empty( $meetup_authorized->status ) || 'success' !== $meetup_authorized->status ) {
			return false;
		}

		if ( ! $this->handle_meetup_security_key( $meetup_authorized ) ) {
			return false;
		}

		return true;
	}

	public function do_import_settings_tab() {
		include_once Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/aggregator/settings.php';
	}

	public function get_all_default_settings() {
		$origins = [
			'csv',
			'gcal',
			'ical',
			'ics',
			'eventbrite',
			'meetup',
			'url',
		];

		/**
		 * Filters the origins available for the default import settings handling.
		 *
		 * @since 4.6.24.1
		 *
		 * @param array $origins List of origins that support import settings.
		 */
		$origins = apply_filters( 'tribe_aggregator_import_setting_origins', $origins );

		$settings = array();

		foreach ( $origins as $origin ) {
			$settings[ $origin ] = [
				'post_status' => $this->default_post_status( $origin ),
				'category'    => $this->default_category( $origin ),
				'map'         => $this->default_map( $origin ),
			];
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
		$options = $this->get_range_options();

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

	/**
	 * Returns the list of limit options that should be applied to imports.
	 *
	 * @since 4.5.13
	 *
	 * @return array An array of limit type options in the [ <limit_type> => <limit description> ]
	 *               format.
	 */
	public function get_import_limit_type_options(  ) {
		$options = [
			'range'    => __( 'By date range', 'the-events-calendar' ),
			'count'    => __( 'By number of events', 'the-events-calendar' ),
			'no_limit' => __( 'Do not limit (not recommended)', 'the-events-calendar' ),
		];

		/**
		 * Filters the options available for the default import limit options.
		 *
		 * @since 4.5.13
		 *
		 * @param array $options An array of arrays in the format
		 *                       [ <limit_type> => <limit description> ].
		 */
		$options = apply_filters( 'tribe_aggregator_import_limit_types', $options );

		return $options;
	}

	/**
	 * Returns a list of date range options.
	 *
	 * @since 4.5.13
	 *
	 * @return array $options An array of arrays in the format
	 *                      [ <range duration in seconds> => [ 'title' => <title>, 'range' => <range> ] ].
	 */
	protected function get_range_options() {
		return array(
			DAY_IN_SECONDS          => [
				'title' => __( '24 hours', 'the-events-calendar' ),
				'range' => __( '24 hours', 'the-events-calendar' ),
			],
			3 * DAY_IN_SECONDS      => [
				'title' => __( '72 hours', 'the-events-calendar' ),
				'range' => __( '72 hours', 'the-events-calendar' ),
			],
			WEEK_IN_SECONDS         => [
				'title' => __( 'One week', 'the-events-calendar' ),
				'range' => __( 'one week', 'the-events-calendar' ),
			],
			2 * WEEK_IN_SECONDS     => [
				'title' => __( 'Two weeks', 'the-events-calendar' ),
				'range' => __( 'two weeks', 'the-events-calendar' ),
			],
			3 * WEEK_IN_SECONDS     => [
				'title' => __( 'Three weeks', 'the-events-calendar' ),
				'range' => __( 'three weeks', 'the-events-calendar' ),
			],
			30 * DAY_IN_SECONDS     => [
				'title' => __( 'One month', 'the-events-calendar' ),
				'range' => __( 'one month', 'the-events-calendar' ),
			],
			2 * 30 * DAY_IN_SECONDS => [
				'title' => __( 'Two months', 'the-events-calendar' ),
				'range' => __( 'two months', 'the-events-calendar' ),
			],
			3 * 30 * DAY_IN_SECONDS => [
				'title' => __( 'Three months', 'the-events-calendar' ),
				'range' => __( 'three months', 'the-events-calendar' ),
			],
		);
	}

	/**
	 * Returns the range options available for imports.
	 *
	 * Titles are meant to be used in titles and make sense alone, range strings are meant to be used when using the
	 * duration in a sentence and do not make sense alone.
	 *
	 * @since 4.5.13
	 *
	 * @param bool $title Whether the values of the array should be for title or for use as range.
	 *
	 * @return array An associative array of durations and strings.
	 */
	public function get_import_range_options( $title = true ) {
		$options = $this->get_range_options();

		/**
		 * Filters the options available for the import date range.
		 *
		 * @since 4.5.13
		 *
		 * @param array $options An array of arrays in the format
		 *                       [ <range duration in seconds> => [ 'title' => <title>, 'range' => <range> ] ].
		 */
		$options = apply_filters( 'tribe_aggregator_import_range_options', $options );

		if ( $title ) {
			return array_combine( array_keys( $options ), wp_list_pluck( $options, 'title' ) );
		}

		return array_combine( array_keys( $options ), wp_list_pluck( $options, 'range' ) );
	}

	/**
	 * Return a list of available options for the import numeric limit.
	 *
	 * @since 4.5.13
	 *
	 * @return array $options An array of arrays in the format [ <number> => <number> ].
	 */
	public function get_import_limit_count_options() {
		$numbers = [ 50, 100, 200, 300, 500, 750, 1000, 1500, 2000, 2500, 3000, 3500, 4000 ];

		$options = array_combine( $numbers, $numbers );

		/**
		 * Filters the options available for the import numeric limit.
		 *
		 * @since 4.5.13
		 *
		 * @param array $options An array of arrays in the format [ <number> => <number> ].
		 */
		$options = apply_filters( 'tribe_aggregator_import_count_options', $options );

		return $options;
	}

	/**
	 * Returns the default value of the import count limit.
	 *
	 * @since 4.5.13
	 *
	 * @return int
	 */
	public function get_import_limit_count_default() {
		/**
		 * Filters the default value of the import count limit.
		 *
		 * @since 4.5.13
		 *
		 * @param int
		 */
		return apply_filters( 'tribe_aggregator_import_count_default', 200 );
	}

	/**
	 * Returns the default value of the import count limit.
	 *
	 * @since 4.5.13
	 *
	 * @return int
	 */
	public function get_import_range_default() {
		/**
		 * Filters the default value of the import range limit.
		 *
		 * @since 4.5.13
		 *
		 * @param int
		 */
		return apply_filters( 'tribe_aggregator_import_range_default', 30 * DAY_IN_SECONDS );
	}


	/**
	 * Gets all the possible regular-exp for external url sources
	 *
	 * @since 4.6.18
	 *
	 * @return array
	 */
	public function get_source_origin_regexp() {
		$origins = [
			'eventbrite' => Tribe__Events__Aggregator__Record__Eventbrite::get_source_regexp(),
			'meetup'     => Tribe__Events__Aggregator__Record__Meetup::get_source_regexp(),
		];

		/**
		 * Allows external plugins to filter which are the source Regular EXP
		 *
		 * @since 4.6.18
		 *
		 * @param  array $origins Which origins already exist
		 */
		return apply_filters( 'tribe_aggregator_source_origin_regexp', $origins );
	}

	/**
	 * Matches which other origin this source url might be
	 *
	 * @since 4.6.18
	 *
	 * @param  string $source Which source we are testing against
	 *
	 * @return string|bool
	 */
	public function match_source_origin( $source ) {
		$origins = $this->get_source_origin_regexp();

		if ( ! is_string( $source  ) ) {
			return false;
		}

		foreach ( $origins as $origin => $regexp ) {
			// Skip if we don't match the source to any of the URLs
			if ( ! preg_match( '/' . $regexp . '/', $source ) ) {
				continue;
			}

			return $origin;
		}

		return false;
	}

	/**
	 * Hooked to current_screen, this method identifies whether or not fb credentials should be cleared
	 *
	 * @deprecated 4.6.23
	 *
	 * @param WP_Screen $screen
	 */
	public function maybe_clear_fb_credentials( $screen ) {
		_deprecated_function( __FUNCTION__, '4.6.23', 'Importing from Facebook is no longer supported in Event Aggregator.' );

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
			Tribe__Settings::instance()->get_url( [ 'tab' => 'addons' ] )
		);
		die;
	}

	/**
	 * @deprecated 4.6.23
	 */
	public function get_fb_credentials() {
		_deprecated_function( __FUNCTION__, '4.6.23', 'Importing from Facebook is no longer supported in Event Aggregator.' );

		$args = [
			'token'   => tribe_get_option( 'fb_token' ),
			'expires' => tribe_get_option( 'fb_token_expires' ),
			'scopes'  => tribe_get_option( 'fb_token_scopes' ),
		];

		return (object) $args;
	}

	/**
	 * @deprecated 4.6.23
	 */
	public function has_fb_credentials() {
		_deprecated_function( __FUNCTION__, '4.6.23', 'Importing from Facebook is no longer supported in Event Aggregator.' );

		$credentials = $this->get_fb_credentials();
		return ! empty( $credentials->token ) && ! empty( $credentials->expires ) && ! empty( $credentials->scopes );
	}

	/**
	 * @deprecated 4.6.23
	 */
	public function clear_fb_credentials() {
		_deprecated_function( __FUNCTION__, '4.6.23', 'Importing from Facebook is no longer supported in Event Aggregator.' );

		tribe_update_option( 'fb_token', null );
		tribe_update_option( 'fb_token_expires', null );
		tribe_update_option( 'fb_token_scopes', null );
	}

	/**
	 * Given a URL, tack on the parts of the URL that gets used to disconnect Facebook
	 *
	 * @deprecated 4.6.23
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function build_disconnect_facebook_url( $url ) {
		_deprecated_function( __FUNCTION__, '4.6.23', 'Importing from Facebook is no longer supported in Event Aggregator.' );

		return wp_nonce_url(
			add_query_arg(
				'action',
				'disconnect-facebook',
				$url
			),
			'disconnect-facebook'
		);
	}

	/**
	 * @deprecated 4.6.23
	 */
	public function is_fb_credentials_valid( $time = null ) {
		_deprecated_function( __FUNCTION__, '4.6.23', 'Importing from Facebook is no longer supported in Event Aggregator.' );

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

	/**
	 * Returns a filtered map of import process slugs to classes.
	 *
	 * @since 4.6.23
	 *
	 * @param bool $pretty Whether to return human-readable and "pretty" name for the process
	 *                     or the class names.
	 *
	 * @return array A map of import process slugs to classes or names in the shape
	 *               [ <slug> => <class_or_name> ].
	 */
	public function get_import_process_options( $pretty = false ) {
		$options = array(
			'async' => array(
				'class' => 'Tribe__Events__Aggregator__Record__Async_Queue',
				'name'  => __( 'Asynchronous', 'the-events-calendar' ),
			),
			'cron'  => array(
				'class' => 'Tribe__Events__Aggregator__Record__Queue',
				'name'  => __( 'Cron-based', 'the-events-calendar' ),
			),
		);

		/**
		 * Filters the map of available import process options.
		 *
		 * @since 4.6.23
		 *
		 * @param array $options A map of import process options to import process classes
		 *                       in the shape [ <slug> => [ 'class' => <class>, 'name' => <name> ] ].
		 * @param bool $pretty Whether to return human-readable and "pretty" names for the options (`true`)
		 *                     or the class names ('false').
		 */
		$options = apply_filters( 'tribe_aggregator_import_process_options', $options, $pretty );

		if ( $pretty ) {
			return array_combine( array_keys( $options ), wp_list_pluck( $options, 'name' ) );
		}

		return array_combine( array_keys( $options ), wp_list_pluck( $options, 'class' ) );
	}

	/**
	 * Returns the filtered default import process slug or class.
	 *
	 * @since 4.6.23
	 *
	 * @param bool $return_class Whether to return the import process class (`true`) or
	 *                           slug (`false`).
	 *
	 * @return string The default import process slug or class.
	 */
	public function get_import_process_default( $return_class = true ) {
		$available = $this->get_import_process_options();

		if ( $return_class ) {
			$default = reset( $available );
		} else {
			$keys    = array_keys( $available );
			$default = reset( $keys );
		}

		/**
		 * Filters the default import process class or slug.
		 *
		 * @since 4.6.23
		 *
		 * @param string $default    The default import process class (if `$return_class` is `true`) or
		 *                           slug (if `$return_class` is `false`).
		 * @param bool $return_class Whether to return the default import process class (`true`) or
		 *                           slug (`false`).
		 * @param array $available   A map, in the shape [ <slug> => <class> ], of available import processes.
		 */
		$default = apply_filters( 'tribe_aggregator_import_process_default', $default, $return_class, $available );

		return $default;
	}

	/**
	 * Returns the currently selected, or a specific, import process class.
	 *
	 * @since 4.6.23
	 *
	 * @param null|string $slug The slug of the import process class to return; if not specified
	 *                          then the default import process class will be returned. If the
	 *                          slug is not available then the default class will be returned.
	 *
	 * @return string The import process class for the specified slug or the default class if the
	 *                slug was not specified or is not available.
	 */
	public function get_import_process_class( $slug = null ) {
		$default_slug  = $this->get_import_process_default( false );
		$default_class = $this->get_import_process_default();

		$available = $this->get_import_process_options();
		if ( null === $slug ) {
			$slug = tribe_get_option( 'tribe_aggregator_import_process_system', $default_slug );
		}

		$class = Tribe__Utils__Array::get( $available, $slug, $default_class );

		/**
		 * Filters the import process class that will be returned for an import process slug.
		 *
		 * @since 4.6.23
		 *
		 * @param string $class     The import process slug for the slug or the default class if the
		 *                          slug was not specified or the specified slug is not available.
		 * @param string|null $slug The specified slug or `null` if not specified.
		 * @param array $available  A map of available process classes in the shape
		 *                          [ <slug> => <class> ].
		 */
		$class = apply_filters( 'tribe_aggregator_import_process', $class, $slug, $available );

		return $class;
	}
}
