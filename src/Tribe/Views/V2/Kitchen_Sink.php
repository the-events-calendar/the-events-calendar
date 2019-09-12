<?php
/**
 * A View Kitchen Sink for the implementation of all elements
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */
namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as Events;
use Tribe__Template as Template;
use Tribe__Events__Rewrite as Rewrite;

/**
 * Class Kitchen_Sink
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */
class Kitchen_Sink extends Template {
	/**
	 * Setup the Kitchen Sink Template constructor.
	 *
	 * @since 4.9.2
	 */
	public function __construct() {
		$this->set_template_origin( Events::instance() );
		$this->set_template_folder( 'src/views/kitchen-sink' );
		$this->set_template_folder_lookup( true );
	}

	/**
	 * Gets the available pages for the Kitchen sink code
	 *
	 * @since  4.9.2
	 *
	 * @return array
	 */
	public function get_available_pages() {
		return [
			'page',
			'grid',
			'typographical',
			'elements',
			'events-bar',
			'navigation',
			'manager',
		];
	}

	/**
	 * Add the events kitchen sink variable to the WP Query Vars
	 *
	 * @since  4.9.2
	 *
	 * @param  array $vars query vars array
	 *
	 * @return array
	 */
	public function filter_register_query_vars( $vars = [] ) {
		$vars[] = 'tribe_events_views_kitchen_sink';
		return $vars;
	}

	/**
	 * Add the rewrite rules for Kitchen Sink URL
	 *
	 * @since 4.9.2
	 *
	 * @param \Tribe__Events__Rewrite $rewrite
	 *
	 * @return void
	 */
	public function generate_rules( Rewrite $rewrite ) {
		$args = [
			'post_type' => Events::POSTTYPE,
			'tribe_events_views_kitchen_sink' => 'page',
		];
		$regex = [ 'tribe', 'events', 'kitchen-sink' ];
		$rewrite->add( $regex, $args );

		$pages_regular_exp = implode( '|', $this->get_available_pages() );

		$args = [
			'post_type' => Events::POSTTYPE,
			'tribe_events_views_kitchen_sink' => '%1',
		];
		$regex = [ 'tribe', 'events', 'kitchen-sink', '(' . $pages_regular_exp . ')' ];

		$rewrite->add( $regex, $args );
	}
}
