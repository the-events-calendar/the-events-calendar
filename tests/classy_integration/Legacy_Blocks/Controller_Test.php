<?php

namespace TEC\Tests\Events\Classy\Legacy_Blocks;

use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\Classy\Legacy_Blocks\Controller;
use Tribe\Events\Views\V2\Template_Bootstrap;

class Controller_Test extends Controller_Test_Case {
	protected $controller_class = Controller::class;

	use SnapshotAssertions;

	public function test_should_render_block_events_with_blocks() {
		$event = tribe_events()->set_args(
			[
				'title'      => 'Test Event',
				'status'     => 'publish',
				'start_date' => '2020-01-01 09:00:00',
				'end_date'   => '2020-01-01 11:30:00',
			]
		)->create();

		global $wpdb;
		$wpdb->update( $wpdb->posts, [ 'post_content' => 'Test description <!-- wp:' ], [ 'ID' => $event->ID ], '%s', '%d' );
		clean_post_cache( $event->ID );

		$event = tribe_get_event( $event->ID );

//		$context = tribe_context()->alter(
//			[
//				'post_id'         => $event->ID,
//				'single'          => true,
//				'event_post_type' => true,
//			]
//		);

		global $post;
		$post = $event;
		
		setup_postdata( $event );
		
		add_filter( 'tribe_events_views_v2_bootstrap_should_display_single', '__return_true' );
		
		$controller = $this->make_controller();
		
		$html = tribe( Template_Bootstrap::class )->get_view_html();
		
		$this->assertTrue( str_contains( $html, 'tec-block__single-event' ) );

		remove_filter( 'tribe_events_views_v2_bootstrap_should_display_single', '__return_true' );
	}
}
