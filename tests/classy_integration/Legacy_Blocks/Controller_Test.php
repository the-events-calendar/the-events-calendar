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
				'start_date' => '+2 days 14:00:00',
				'duration'   => HOUR_IN_SECONDS,
			]
		)->create();

		global $wpdb;
		$wpdb->update( $wpdb->posts, [ 'post_content' => 'Test description <!-- wp:' ], [ 'ID' => $event->ID ], '%s', '%d' );
		clean_post_cache( $event->ID );

		global $post;
		$post = tribe_get_event( $event->ID );
		
		add_filter( 'tribe_events_views_v2_bootstrap_should_display_single', '__return_true' );
		
		$this->make_controller()->register();
		
		$html = tribe( Template_Bootstrap::class )->get_view_html();
		
		$this->assertTrue( str_contains( $html, 'tribe-events-single tribe-blocks-editor' ) );

		remove_filter( 'tribe_events_views_v2_bootstrap_should_display_single', '__return_true' );
		wp_reset_postdata();
	}
}
