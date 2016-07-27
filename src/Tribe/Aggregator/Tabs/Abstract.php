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
				array( 'datatables', 'vendor/datatables/media/js/jquery.dataTables.js', array( 'jquery' ) ),
				array( 'datatables-css', 'vendor/datatables/media/css/jquery.dataTables.css' ),
				array( 'datatables-scroller', 'vendor/datatables/extensions/Scroller/js/dataTables.scroller.js', array( 'jquery', 'datatables' ) ),
				array( 'datatables-scroller-css', 'vendor/datatables/extensions/Scroller/css/scroller.dataTables.css' ),
				array( 'datatables-fixedheader', 'vendor/datatables/extensions/FixedHeader/js/dataTables.fixedHeader.js', array( 'jquery', 'datatables' ) ),
				array( 'datatables-fixedheader-css', 'vendor/datatables/extensions/FixedHeader/css/fixedHeader.dataTables.css' ),
				array( 'tribe-ea-fields', 'aggregator-fields.js', array( 'jquery', 'datatables-scroller', 'datatables-fixedheader', 'underscore', 'tribe-bumpdown', 'tribe-dependency', 'tribe-events-select2' ) ),
				array( 'tribe-ea-page', 'aggregator-page.css', ),
			),
			'admin_enqueue_scripts',
			array(
				'conditionals' => array(
					array( $this, 'is_active' )
				),
				'localize' => (object) array(
					'name' => 'tribe_l10n_ea_fields',
					'data' => array(
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
