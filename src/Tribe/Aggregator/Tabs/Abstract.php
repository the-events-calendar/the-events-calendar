<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

abstract class Tribe__Events__Aggregator__Tabs__Abstract {

	/**
	 * To Order the Tabs on the UI you need to change the priority
	 * @var integer
	 */
	public $priority = 50;

	/**
	 * Creates a way to include the this tab HTML easily
	 *
	 * @return string Content of the tab
	 */
	public function render() {
		$data = array(
			'tab' => $this,
		);

		return Tribe__Events__Aggregator__Page::instance()->template( 'tabs/' . $this->get_slug(), $data );
	}

	/**
	 * The constructor for any new Tab on the Aggregator,
	 * If you need an action to be hook to any Tab, use this.
	 */
	public function __construct() {
		$plugin = Tribe__Events__Main::instance();

		// Only Load if this Is active
		tribe_assets( $plugin,
			array(
				array( 'tribe-ea-fields', 'aggregator-fields.js', array( 'jquery', 'tribe-datatables', 'underscore', 'tribe-bumpdown', 'tribe-dependency', 'tribe-events-select2' ) ),
				array( 'tribe-ea-page', 'aggregator-page.css', array( 'datatables-css' ) ),
			),
			'admin_enqueue_scripts',
			array(
				'conditionals' => array(
					array( $this, 'is_active' ),
				),
				'localize' => (object) array(
					'name' => 'tribe_l10n_ea_fields',
					'data' => array(
						'preview_fetch_error_prefix' => __( 'There was an error fetching the results from your import:', 'the-events-calendar' ),
						'import_all' => __( 'Import All (%d)', 'the-events-calendar' ),
						'import_checked' => __( 'Import Checked (%d)', 'the-events-calendar' ),
						'events_required_for_manual_submit' => __( 'Your import must include at least one event', 'the-events-calendar' ),
						'debug' => defined( 'WP_DEBUG' ) && true === WP_DEBUG,
					),
				),
			)
		);
	}

	/**
	 * Enforces a method to display the tab or not
	 *
	 * @return boolean
	 */
	abstract public function is_visible();

	/**
	 * Enforces a method to return the Tab Slug
	 *
	 * @return string
	 */
	abstract public function get_slug();

	/**
	 * Enforces a method to return the Label of the Lab
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Fetches the link to this tab
	 *
	 * @param array|string $args     Query String or Array with the arguments
	 * @param boolean      $relative Return a relative URL or absolute
	 *
	 * @return string
	 */
	public function get_url( $args = array(), $relative = false ) {
		$defaults = array(
			'tab' => $this->get_slug(),
		);

		// Allow the link to be "changed" on the fly
		$args = wp_parse_args( $args, $defaults );

		// Escape after the filter
		return Tribe__Events__Aggregator__Page::instance()->get_url( $args, $relative );
	}

	/**
	 * Determines if this Tab is currently been displayed
	 *
	 * @return boolean
	 */
	public function is_active() {
		return Tribe__Events__Aggregator__Tabs::instance()->is_active( $this->get_slug() );
	}
}
