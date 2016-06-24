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
	protected $page;

	/**
	 * @var Tribe__Events__Aggregator__Service Event Aggregator service object
	 */
	protected $service;

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
			$className      = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}

	/**
	 * Constructor!
	 */
	public function __construct() {
		$this->page();
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
	 * Object accessor method for Tribe__Events__Aggregator__Page
	 *
	 * @return Tribe__Events__Aggregator__Page
	 */
	public function page() {
		if ( ! $this->page ) {
			$this->page = Tribe__Events__Aggregator__Page::instance();
		}

		return $this->page;
	}

	/**
	 * Object accessor method for Tribe__Events__Aggregator__Service
	 *
	 * @return Tribe__Events__Aggregator__Service
	 */
	public function service() {
		if ( ! $this->service ) {
			$this->service = Tribe__Events__Aggregator__Service::instance( $this );
		}

		return $this->service;
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
			$origins = $this->service()->fetch_origins();

			set_transient( "{$this->cache_group}_origins", $origins, 6 * HOUR_IN_SECONDS );
		}

		// Let's build out the translated text based on the names that come back from the EA service
		foreach ( $origins as &$origin ) {
			$origin->text = __( $origin->name, 'the-events-calendar' );
		}

		return apply_filters( 'tribe_ea_origins', $origins );
	}
}
