<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Aggregator__Tabs__Past extends Tribe__Events__Aggregator__Tabs__Abstract {
	/**
	 * Static Singleton Holder
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public $priority = 30;

	public function is_visible() {
		return true;
	}

	public function get_slug() {
		return 'past';
	}

	public function get_label() {
		return esc_html__( 'Past Imports', 'the-events-calendar' );
	}

}