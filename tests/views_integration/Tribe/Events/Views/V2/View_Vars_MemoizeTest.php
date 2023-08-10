<?php

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\List_View;
use Tribe__Events__Dates__Known_Range;


class View_Vars_MemoizeTest extends \Codeception\TestCase\WPTestCase {

	public function _setUp() {
		parent::_setUp();
		// This was being updated mid-view processing, expiring memoized `save_post` listener data.
		Tribe__Events__Dates__Known_Range::instance()->rebuild_known_range();
	}

	/**
	 * @test
	 */
	public function should_memoize_basic_get_vars() {
		$view = View::make( List_View::class );

		// This will flag that memoize was not hit.
		$memoize_missed = false;
		add_action( 'tec_events_views_v2_after_get_events', function ( $events, $view ) use ( &$memoize_missed ) {
			$memoize_missed = true;
		}, 10, 2 );

		$view->get_template_vars();
		$this->assertTrue( $memoize_missed, 'First call to get_template_vars() should bypass memoize.' );
		$memoize_missed = false;
		$view->get_template_vars();
		$this->assertFalse( $memoize_missed, 'Second call to get_template_vars() should hit memoize.' );
	}

	/**
	 * @test
	 */
	public function should_clear_memoize_based_on_late_filters() {
		$view = View::make( List_View::class );

		// This will flag that memoize was not hit.
		$memoize_missed = false;
		add_action( 'tec_events_views_v2_after_get_events', function ( $events, $view ) use ( &$memoize_missed ) {
			$memoize_missed = true;
		}, 10, 2 );

		$view->get_template_vars();
		$this->assertTrue( $memoize_missed, 'First call to get_template_vars() should bypass memoize.' );

		// This should cause the memoized cache to fail.
		$memoize_missed = false;
		add_filter( "tribe_repository_events_query_args", function ( $query_args, $query, $view ) {
			$query_args['post_status'] = 'draft';

			return $query_args;
		}, 10, 3 );
		$view->get_template_vars();
		$this->assertFalse( $memoize_missed, 'Second call to get_template_vars() should hit memoize.' );
	}
}