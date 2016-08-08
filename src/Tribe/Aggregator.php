<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator {
	/**
	 * @var Tribe__Events__Aggregator Event Aggregator bootstrap class
	 */
	protected static $instance;

	/**
	 * @var Tribe__Events__Aggregator__Page Event Aggregator page root object
	 */
	public $page;

	/**
	 * @var Tribe__Events__Aggregator__Service Event Aggregator service object
	 */
	public $service;

	/**
	 * @var Tribe__Events__Aggregator__Settings Event Aggregator settings object
	 */
	public $settings;

	/**
	 * @var Tribe__PUE__Checker PUE Checker object
	 */
	public $pue_checker;

	/**
	 * @var array Collection of API objects
	 */
	protected $api;

	/**
	 * People who modify this value are not nice people.
	 *
	 * @var int Maximum number of import requests per day
	 */
	private $daily_limit = 100;

	/**
	 * A variable holder if Aggreator is loaded
	 * @var boolean
	 */
	private $is_loaded = false;

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
		/**
		 * As previously seen by other major features some users would rather have it not active
		 * @var bool
		 */
		$should_load = (bool) apply_filters( 'tribe_aggregator_should_load', true );

		// You shall not Load!
		if ( false === $should_load ) {
			return;
		}

		// Loads the Required Classes and saves then as proprieties
		$this->page        = Tribe__Events__Aggregator__Page::instance();
		$this->service     = Tribe__Events__Aggregator__Service::instance();
		$this->settings    = Tribe__Events__Aggregator__Settings::instance();
		$this->records     = Tribe__Events__Aggregator__Records::instance();
		$this->cron        = Tribe__Events__Aggregator__Cron::instance();
		$this->pue_checker = new Tribe__PUE__Checker( 'http://tri.be/', 'event-aggregator' );

		// Intializes the Classes related to the API
		$this->api();

		// Flags that the Aggregator has been fully loaded
		$this->is_loaded = true;

		// Register the Aggregator Endpoint
		add_action( 'tribe_events_pre_rewrite', array( $this, 'action_endpoint_configuration' ) );

		// Intercept the Endpoint and trigger actions
		add_action( 'parse_request', array( $this, 'action_endpoint_parse_request' ) );

		// Add endpoint query vars
		add_filter( 'query_vars', array( $this, 'filter_endpoint_query_vars' ) );

		// Filter the "plugin name" for Event Aggregator
		add_filter( 'pue_get_plugin_name', array( $this, 'filter_pue_plugin_name' ), 10, 2 );

		// To make sure that meaninful cache is purged when settings are changed
		add_action( 'updated_option', array( $this, 'action_purge_transients' ) );
	}

	/**
	 * Initializes and provides the API objects
	 *
	 * @param string $api Which API to provide
	 *
	 * @return Tribe__Events__Aggregator__API__Abstract|stdClass|null
	 */
	public function api( $api = null ) {
		if ( ! $this->api ) {
			$this->api = (object) array(
				'origins' => new Tribe__Events__Aggregator__API__Origins,
				'import'  => new Tribe__Events__Aggregator__API__Import,
				'image'   => new Tribe__Events__Aggregator__API__Image,
			);
		}

		if ( ! $api ) {
			return $this->api;
		}

		if ( empty( $this->api->$api ) ) {
			return null;
		}

		return $this->api->$api;
	}

	/**
	 * Creates the Required Endpoint for the Aggregator Service to Query
	 *
	 * @param array $query_vars
	 *
	 * @return void
	 */
	public function action_endpoint_configuration( $rewrite ) {
		$rewrite->add(
			array( 'event-aggregator', '(insert)' ),
			array( 'tribe-aggregator' => 1, 'tribe-action' => '%1' )
		);
	}

	/**
	 * Adds the required Query Vars for the Aggregator Endpoint to work
	 *
	 * @param array $query_vars
	 *
	 * @return array
	 */
	public function filter_endpoint_query_vars( $query_vars = array() ) {
		$query_vars[] = 'tribe-aggregator';
		$query_vars[] = 'tribe-action';

		return $query_vars;
	}

	/**
	 * Allows the API to call the website
	 *
	 * @param  WP    $wp
	 *
	 * @return void
	 */
	public function action_endpoint_parse_request( $wp ) {
		// If we don't have both of these we bail
		if ( ! isset( $wp->query_vars['tribe-aggregator'] ) || empty( $wp->query_vars['tribe-action'] ) ) {
			return;
		}

		// Fetches which action we are talking about `/event-aggregator/{$action}`
		$action = $wp->query_vars['tribe-action'];

		// Bail if we don't have an action
		if ( ! $action ) {
			return;
		}

		/**
		 * Allow developers to hook on Event Aggregator endpoint
		 * We will always exit with a JSON answer error
		 *
		 * @param string  $action  Which action was requested
		 * @param WP      $wp      The WordPress Request object
		 */
		do_action( 'tribe_aggregator_endpoint', $action, $wp );

		/**
		 * Allow developers to hook to a specific Event Aggregator endpoint
		 * We will always exit with a JSON answer error
		 *
		 * @param WP      $wp      The WordPress Request object
		 */
		do_action( "tribe_aggregator_endpoint_{$action}", $wp );

		// If we reached this point this endpoint call was invalid
		return wp_send_json_error();
	}

	/**
	 * Handles the filtering of the PUE "plugin name" for event aggregator which...isn't a plugin
	 *
	 * @param string $plugin_name Plugin name to filter
	 * @param string $plugin_slug Plugin slug
	 *
	 * @return string
	 */
	public function filter_pue_plugin_name( $plugin_name, $plugin_slug ) {
		if ( 'event-aggregator' !== $plugin_slug ) {
			return $plugin_name;
		}

		return __( 'Event Aggregator', 'the-events-calendar' );
	}

	/**
	 * Purges the aggregator transients that are tied to the event-aggregator license
	 *
	 * @param string $option Option key
	 *
	 * @return boolean
	 */
	public function action_purge_transients( $option ) {
		if ( 'pue_install_key_event_aggregator' !== $option ) {
			return false;
		}

		$cache_group = $this->api( 'origins' )->cache_group;

		return delete_transient( "{$cache_group}_origins" );
	}

	/**
	 * Verify if Aggregator was fully loaded and is active
	 *
	 * @param  boolean $service  Should compare if the service is also active
	 *
	 * @return boolean
	 */
	public function is_active( $service = false ) {
		// If it's not loaded just bail false
		if ( false === (bool) $this->is_loaded ) {
			return false;
		}

		if ( true === $service ) {
			return $this->is_service_active();
		}

		return true;
	}

	/**
	 * Verifies if the service is active
	 *
	 * @return boolean
	 */
	public function is_service_active() {
		return ! is_wp_error( Tribe__Events__Aggregator__Service::instance()->api() );
	}

	/**
	 * Returns the daily import limit
	 *
	 * @return int
	 */
	public function get_daily_limit() {
		return $this->daily_limit;
	}

	/**
	 * Returns the available daily limit of import requests
	 *
	 * @return int
	 */
	public function get_daily_limit_available() {
		$available = get_transient( $this->daily_limit_transient_key() );

		$daily_limit = $this->get_daily_limit();

		if ( false === $available ) {
			return $daily_limit;
		}

		return (int) $available < $daily_limit ? $available : $daily_limit;
	}

	/**
	 * Reduces the daily limit by the provided amount
	 *
	 * @param int $amount Amount to reduce the daily limit by
	 *
	 * @return bool
	 */
	public function reduce_daily_limit( $amount = 1 ) {
		if ( ! is_numeric( $amount ) ) {
			return new WP_Error( 'tribe-invalid-integer', esc_html__( 'The daily limits reduction amount must be an integer' ) );
		}

		if ( $amount < 0 ) {
			return true;
		}

		$available = $this->get_daily_limit_available();

		$available -= $amount;

		if ( $available < 0 ) {
			$available = 0;
		}

		return set_transient( $this->daily_limit_transient_key(), $available, DAY_IN_SECONDS );
	}

	/**
	 * Generates the current daily transient key
	 */
	private function daily_limit_transient_key() {
		return 'tribe-aggregator-limit-used_' . date( 'Y-m-d' );
	}
}
