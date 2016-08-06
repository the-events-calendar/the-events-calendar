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
	 * Constructor!
	 */
	public function __construct() {
		$this->page        = Tribe__Events__Aggregator__Page::instance();
		$this->service     = Tribe__Events__Aggregator__Service::instance();
		$this->settings    = Tribe__Events__Aggregator__Settings::instance();
		$this->records     = Tribe__Events__Aggregator__Records::instance();
		$this->cron        = Tribe__Events__Aggregator__Cron::instance();
		$this->pue_checker = new Tribe__PUE__Checker( 'http://tri.be/', 'event-aggregator' );
		$this->api();

		// Register the Aggregator Endpoint
		add_action( 'tribe_events_pre_rewrite', array( $this, 'register_endpoint' ) );

		// Intercept the Endpoint and trigger actions
		add_action( 'parse_request', array( $this, 'intercept_endpoint' ) );

		// Add endpoint query vars
		add_filter( 'query_vars', array( $this, 'add_endpoint_query_vars' ) );

		// filter the "plugin name" for Event Aggregator
		add_filter( 'pue_get_plugin_name', array( $this, 'pue_plugin_name' ), 10, 2 );

		add_action( 'updated_option', array( $this, 'clear_aggregator_transients' ) );
	}

	public function register_endpoint( $rewrite ) {
		$rewrite->add(
			array( 'event-aggregator', '(insert)' ),
			array( 'tribe-aggregator' => 1, 'tribe-action' => '%1' )
		);
	}

	public function add_endpoint_query_vars( $query_vars = array() ) {
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
	public function intercept_endpoint( $wp ) {
		// If we don't have both of these we bail
		if ( ! isset( $wp->query_vars['tribe-aggregator'] ) || empty( $wp->query_vars['tribe-action'] ) ) {
			return;
		}

		$action = $wp->query_vars['tribe-action'];

		// Bail if we don't have an action
		if ( ! $action ) {
			return;
		}

		// @TODO: do something with the submission of events. $_POST['data']

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
			return new WP_Error( 'invalid-integer', esc_html__( 'The daily limits reduction amount must be an integer' ) );
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

	/**
	 * Handles the filtering of the PUE "plugin name" for event aggregator which...isn't a plugin
	 *
	 * @param string $plugin_name Plugin name to filter
	 * @param string $plugin_slug Plugin slug
	 *
	 * @return string
	 */
	public function pue_plugin_name( $plugin_name, $plugin_slug ) {
		if ( 'event-aggregator' !== $plugin_slug ) {
			return $plugin_name;
		}

		return __( 'Event Aggregator', 'the-events-calendar' );
	}

	/**
	 * Purges the aggregator transients that are tied to the event-aggregator license
	 *
	 * @param string $option Option key
	 */
	public function clear_aggregator_transients( $option ) {
		if ( 'pue_install_key_event_aggregator' !== $option ) {
			return;
		}

		$cache_group = $this->api( 'origins' )->cache_group;

		delete_transient( "{$cache_group}_origins" );
	}
}
