<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Tabs__Scheduled extends Tribe__Events__Aggregator__Tabs__Abstract {
	/**
	 * Static Singleton Holder
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public $priority = 20;

	public function is_visible() {
		return true;
	}

	public function get_slug() {
		return 'scheduled';
	}

	public function get_label() {
		return esc_html__( 'Scheduled Imports', 'the-events-calendar' );
	}


}