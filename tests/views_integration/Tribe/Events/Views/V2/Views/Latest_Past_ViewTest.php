<?php

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\Manager as View_Manager;
use Tribe\Events\Views\V2\View;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Latest_Past_ViewTest extends ViewTestCase {
	use With_Post_Remapping;

	/**
	 * @before
	 */
	public function clean_caches() {
		wp_cache_flush();
	}

	public function latest_past_events_snapshot_data_provider() {
		return [
			'Month View' => [ Month_View::class, 'month' ],
			'Day View'   => [ Day_View::class, 'day' ],
			'List View'  => [ List_Views::class, 'list' ],
		];
	}

	/**
	 * It should correctly render in the context of Month View
	 *
	 * @test
	 * @dataProvider latest_past_events_snapshot_data_provider
	 */
	public function should_correctly_render_in_the_context_of_month_view( $view_class, $view_slug ) {
		// Ensure this View is the default View, else the Latest Past View will not render.
		tribe_update_option( View_Manager::$option_default, $view_slug );
		tribe_update_option( View_Manager::$option_mobile_default, $view_slug );
		// Create a mock context that will ensure the Latest Past Events View will show.
		$context = tribe_context()->alter( [
			'today'            => '2020-03-01',
			'now'              => '2020-03-01 11:00:00',
			'event_date'       => '2020-03-01',
			'show_latest_past' => true,
		] );
		$view    = View::make( $view_class, $context );

		// Let's make sure we're starting from a clean slate.
		$this->assertEquals( 0, tribe_events()->found() );

		// Create 2 events in the past of the mock "now" date and time.
		$mock_events[] = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'           => 23,
			'post_content' => 'Snapshot event 23',
			'start_date'   => '2020-02-15',
			'end_date'     => '2020-02-15',
		] );
		$mock_events[] = $this->get_mock_event( 'events/single/1.template.json', [
			'ID'           => 89,
			'post_content' => 'Snapshot event 89',
			'start_date'   => '2020-02-20',
			'end_date'     => '2020-02-20',
		] );
		/*
		 * Create 2 real events and remap them to the mock ones; the date is not important as long as it's in the past
		 * of the mock "now".
		 */
		$ids            = array_map( static function ( $time ) {
			return static::factory()->event->create( [ 'when' => $time ] );
		}, [ '2020-02-15', '2020-02-20' ] );
		$mock_event_ids = array_map( static function ( \WP_Post $mock_event ) {
			return $mock_event->ID;
		}, $mock_events );
		$this->remap_post_ids( $ids, $mock_event_ids );

		// Test the Month View HTML, it will include the Latest Past Events View.
		$this->assertMatchesSnapshot( $view->get_html() );
	}
}
