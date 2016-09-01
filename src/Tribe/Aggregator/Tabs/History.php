<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Tabs__History extends Tribe__Events__Aggregator__Tabs__Abstract {
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

	public $priority = 30;

	public function is_visible() {
		$records = Tribe__Events__Aggregator__Records::instance();

		return $records->has_scheduled() || $records->has_history();
	}

	public function get_slug() {
		return 'history';
	}

	public function get_label() {
		return esc_html__( 'History', 'the-events-calendar' );
	}

}
