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
	 * @var Tribe__PUE__Checker PUE Checker object
	 */
	public $pue_checker;

	/**
	 * @var array Collection of API objects
	 */
	protected $api;

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
		$this->record      = Tribe__Events__Aggregator__Record::instance();
		$this->cron        = Tribe__Events__Aggregator__Cron::instance();
		$this->pue_checker = new Tribe__PUE__Checker( 'http://tri.be/', 'event-aggregator' );
		$this->api();

		// $this->api( 'import' )->create( array(
		// 	'type' => 'manual',
		// 	'origin' => 'facebook',
		// 	'source' => '453553174769258',
		// 	'facebook_app_id' => '',
		// 	'facebook_secret' => '',
		// ) );

		// $this->api( 'import' )->get( 'd1885e7b2ed7dab8e3d908cecec8780daf55a0ac55e421ed10dadf89f7f51bd1' );

		// Register the Aggregator Endpoint
		add_action( 'tribe_events_pre_rewrite', array( $this, 'register_endpoint' ) );

		// Intercept the Endpoint and trigger actions
		add_action( 'parse_request', array( $this, 'intercept_endpoint' ) );

		// Add endpoint query vars
		add_filter( 'query_vars', array( $this, 'add_endpoint_query_vars' ) );
	}

	public function register_endpoint( $rewrite ) {
		$rewrite->add( array( 'event-aggregator', '(insert)' ), array( 'tribe-aggregator' => 1, 'tribe-action' => '%1' ) );
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

		/**
		 * Allow developers to hook on Event Aggregator endpoint
		 * We will always exit with a JSON answer error
		 *
		 * @param string  $action  Which action was requested
		 * @param WP      $wp      The WordPress Request object
		 */
		do_action( 'tribe_ea_endpoint', $action, $wp );

		/**
		 * Allow developers to hook to a specific Event Aggregator endpoint
		 * We will always exit with a JSON answer error
		 *
		 * @param WP      $wp      The WordPress Request object
		 */
		do_action( "tribe_ea_endpoint_{$action}", $wp );

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
}
