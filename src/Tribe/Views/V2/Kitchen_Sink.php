<?php
/**
 * A View Kitchen Sink for the implementation of all elements
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as Events;
use Tribe__Template as Template;
use Tribe__Events__Rewrite as Rewrite;

/**
 * Class Kitchen_Sink
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
class Kitchen_Sink extends Template {
	/**
	 * Setup the Kitchen Sink Template constructor.
	 *
	 * @since TBD
	 */
	public function __construct() {
		$this->set_template_origin( Events::instance() );
		$this->set_template_folder( 'src/views/kitchen-sink' );
		$this->set_template_folder_lookup( true );
	}

	/**
	 * Add the rewrite rules for Kitchen Sink URL
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Events__Rewrite $rewrite
	 *
	 * @return void
	 */
	public function generate_rules( Rewrite $rewrite ) {
		$args = array(
			'tribe_views_kitchen_sink' => 1,
			'post_type' => Events::POSTTYPE,
			'tribe_kitchen_sink' => 'base',
		);
		$regex = [ 'tribe', 'events', 'kitchen-sink' ];
		$rewrite->add( $regex, $args );

		$args = array(
			'tribe_views_kitchen_sink' => 1,
			'post_type' => Events::POSTTYPE,
			'tribe_kitchen_sink' => '%1',
		);
		$regex = [ 'tribe', 'events', 'kitchen-sink', '(grid|typographical|elements|events-bar|navigation)' ];

		$rewrite->add( $regex, $args );
	}
}
