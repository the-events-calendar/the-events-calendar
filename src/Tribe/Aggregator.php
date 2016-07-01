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
		if ( ! isset( self::$instance ) ) {
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
		$this->pue_checker = new Tribe__PUE__Checker( 'http://tri.be/', 'event-aggregator' );
		$this->api();

		// THESE ARE JUST HERE FOR TESTING PURPOSES AS WE WAIT FOR THE UI
		/*
		$this->api( 'import' )->create( array(
			'type' => 'manual',
			'origin' => 'facebook',
			'source' => '998482400171215',
			'facebook_app_id' => '',
			'facebook_secret' => '',
		) );
		//*/
		//$this->api( 'import' )->get( 'd1885e7b2ed7dab8e3d908cecec8780daf55a0ac55e421ed10dadf89f7f51bd1' );
	}

	/**
	 * Initializes and provides the API objects
	 *
	 * @param string $api Which API to provide
	 *
	 * @return Tribe__Events__Aggregator__API__Origins|Tribe__Events__Aggregator__API__Import|Tribe__Events__Aggregator__API__Image|object
	 */
	public function api( $api = null ) {
		if ( ! $this->api ) {
			$this->api = (object) array(
				'origins' => new Tribe__Events__Aggregator__API__Origins( $this->service ),
				'import'  => new Tribe__Events__Aggregator__API__Import( $this->service ),
				'image'   => new Tribe__Events__Aggregator__API__Image( $this->service ),
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
