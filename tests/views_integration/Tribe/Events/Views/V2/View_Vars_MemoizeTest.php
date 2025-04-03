<?php

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
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
		$context = tribe_context()->alter( [
			'event_date'     => '2010-01-01',
			'event_category' => 'frank'
		] );
		$view    = View::make( List_View::class, $context );

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
	public function should_skip_memoize_based_on_context() {
		// Category Archive.
		$context = tribe_context()->alter( [
			'event_date'     => '2010-01-01',
			'event_category' => 'frank'
		] );

		/** @var Month_View $month_view */
		$view = View::make( Month_View::class, $context );

		// This will flag that memoize was not hit.
		$memoize_missed = false;
		add_action( 'tec_events_views_v2_after_get_events', function ( $events, $view ) use ( &$memoize_missed ) {
			$memoize_missed = true;
		}, 10, 2 );

		$view->get_template_vars();
		$this->assertTrue( $memoize_missed, 'First call to get_template_vars() should bypass memoize.' );

		// This should cause the memoized cache to fail.
		$memoize_missed = false;
		// Change context, this should bypass the memoized values.
		$view->set_context( $context->alter( [ 'event_category' => 'bob' ] ) );
		$view->get_template_vars();
		$this->assertTrue( $memoize_missed, 'Second call to get_template_vars() should miss memoize.' );
	}

	/**
	 * @test
	 */
	public function should_skip_memoize_based_on_late_filters() {
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
		// Change page number.
		$view->get_repository()->page( 10 );
		$view->get_template_vars();
		$this->assertTrue( $memoize_missed, 'Second call to get_template_vars() should bypass memoize.' );
	}
}