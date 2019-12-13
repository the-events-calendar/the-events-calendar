<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Events_BarTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar';

	public function setUp() {
		parent::setUp();
		// Start Function Mocker.
		Test::setUp();
		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );
	}

	/**
	 * Test render with views
	 */
	public function test_render_with_views() {
		$public_views = [
			(object) [
				'view_url'        => 'https://test.tri.be/events/list/',
				'view_slug'       => 'list',
				'view_label'      => 'List',
				'is_current_view' => true,
			],
			(object) [
				'view_url'        => 'https://test.tri.be/events/month/',
				'view_slug'       => 'month',
				'view_label'      => 'Month',
				'is_current_view' => false,
			],
			(object) [
				'view_url'        => 'https://test.tri.be/events/today/',
				'view_slug'       => 'day',
				'view_label'      => 'Day',
				'is_current_view' => false,
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'url'                  => 'http://test.tri.be',
			'view_slug'            => 'list',
			'view_label'           => 'List',
			'public_views'         => $public_views,
			'display_events_bar'   => true,
			'disable_event_search' => false,
		] ) );
	}

	/**
	 * Test render with views disabled event search
	 */
	public function test_render_with_views_disabled_event_search() {
		$public_views = [
			(object) [
				'view_url'        => 'https://test.tri.be/events/list/',
				'view_slug'       => 'list',
				'view_label'      => 'List',
				'is_current_view' => true,
			],
			(object) [
				'view_url'        => 'https://test.tri.be/events/month/',
				'view_slug'       => 'month',
				'view_label'      => 'Month',
				'is_current_view' => false,
			],
			(object) [
				'view_url'        => 'https://test.tri.be/events/today/',
				'view_slug'       => 'day',
				'view_label'      => 'Day',
				'is_current_view' => false,
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'url'                  => 'http://test.tri.be',
			'view_slug'            => 'list',
			'view_label'           => 'List',
			'public_views'         => $public_views,
			'display_events_bar'   => true,
			'disable_event_search' => true,
		] ) );
	}

	/**
	 * Test render with display events bar false
	 */
	public function test_render_with_display_events_bar_false() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'display_events_bar' => false,
		] ) );
	}

	public function tearDown(){
		Test::tearDown();
		parent::tearDown();
	}
}
