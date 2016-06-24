<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// bail if already defined
if ( class_exists( 'Tribe__Events__Aggregator' ) ) {
	return;
}

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
	 * @var string slug used for the plugin update engine
	 **/
	public $pue_slug = 'event-aggregator';

	/**
	 * @var string PUE update URL
	 **/
	public $pue_url = 'http://tri.be/';

	/**
	 * @var Tribe__PUE__Checker PUE Checker object
	 */
	public $pue_checker;

	/**
	 * @var string License key site option meta key
	 */
	public $license_meta_key = 'pue_install_key_event_aggregator';

	/**
	 * @var string Event Aggregator cache key prefix
	 */
	public $cache_group = 'tribe_ea';

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Aggregator
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor!
	 */
	public function __construct() {
		$this->page    = Tribe__Events__Aggregator__Page::instance();
		$this->service = Tribe__Events__Aggregator__Service::instance( $this );

		$this->hooks();
		$this->service();
		$this->register_with_pue();
	}

	/**
	 * Set up hooks
	 */
	protected function hooks() {
	}

	/**
	 * Registers Event Aggregator with PUE so the license field shows up
	 */
	protected function register_with_pue() {
		$this->pue_checker = new Tribe__PUE__Checker( $this->pue_url, $this->pue_slug, array(), '' );
	}

	/**
	 * Get the event-aggregator license key
	 *
	 * @return string
	 */
	public function get_license_key() {
		return get_option( $this->license_meta_key );
	}

	/**
	 * Get event-aggregator origins
	 */
	public function get_origins() {
		$origins = array();

		if ( $cached_origins = get_transient( "{$this->cache_group}_origins" ) ) {
			$origins = $cached_origins;
		} else {
			$origins = $this->service->fetch_origins();

			set_transient( "{$this->cache_group}_origins", $origins, 6 * HOUR_IN_SECONDS );
		}

		// Let's build out the translated text based on the names that come back from the EA service
		foreach ( $origins as &$origin ) {
			$origin->text = __( $origin->name, 'the-events-calendar' );
		}

		return apply_filters( 'tribe_ea_origins', $origins );
	}
}
