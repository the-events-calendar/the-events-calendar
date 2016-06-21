<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

abstract class Tribe__Events__Aggregator__Tabs__Abstract {

	public $priority = 50;

	public function render() {
		$data = array(
			'tab' => $this,
		);
		Tribe__Events__Aggregator__Page::instance()->template( 'tabs/' . $this->get_slug(), $data );
	}

	public function __construct() {
		$plugin = Tribe__Events__Main::instance();

		// Only Load if this Is active
		tribe_assets( $plugin,
			array(
				array( 'tribe-ea-fields', 'aggregator-fields.js', array( 'jquery', 'underscore', 'tribe-inline-bumpdown', 'tribe-events-select2' ) ),
				array( 'tribe-ea-page', 'aggregator-page.css' ),
			),
			'admin_enqueue_scripts',
			array(
				'conditionals' => array(
					array( $this, 'is_active' )
				)
			)
		);
	}

	abstract public static function instance();

	abstract public function is_visible();

	abstract public function get_slug();

	abstract public function get_label();

	public function get_url( $args = array(), $relative = false ) {
		$defaults = array(
			'tab' => $this->get_slug(),
		);

		// Allow the link to be "changed" on the fly
		$args = wp_parse_args( $args, $defaults );

		// Escape after the filter
		return Tribe__Events__Aggregator__Page::instance()->get_url( $args, $relative );
	}

	public function is_active() {
		return Tribe__Events__Aggregator__Tabs::instance()->is_active( $this->get_slug() );
	}
}