<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Events_BarTest extends HtmlPartialTestCase
{
	use With_Uopz;

	protected $partial_path = 'components/events-bar';

	public function setUp() {
		parent::setUp();
		// Always return the same value when creating nonces.
		$this->set_fn_return( 'wp_create_nonce', '2ab7cc6b39' );
	}

	public function tearDown(){
		$this->unset_uopz_returns();

		parent::tearDown();
	}

	/**
	 * Test render with views
	 */
	public function test_render_with_views() {
		$public_views = [
			'list'  => (object) [
				'view_url'   => 'https://test.tri.be/events/list/',
				'view_class' => 'Tribe\Events\Views\V2\Views\List_View',
				'view_label' => 'List',
				'aria_label' => 'Display Events in List View',
			],
			'month' => (object) [
				'view_url'   => 'https://test.tri.be/events/month/',
				'view_class' => 'Tribe\Events\Views\V2\Views\Month_View',
				'view_label' => 'Month',
				'aria_label' => 'Display Events in Month View',
			],
			'day'   => (object) [
				'view_url'   => 'https://test.tri.be/events/today/',
				'view_class' => 'Tribe\Events\Views\V2\Views\Day_View',
				'view_label' => 'Day',
				'aria_label' => 'Display Events in Day View',
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
			'list'  => (object) [
				'view_url'   => 'https://test.tri.be/events/list/',
				'view_class' => 'Tribe\Events\Views\V2\Views\List_View',
				'view_label' => 'List',
				'aria_label' => 'Display Events in List View',
			],
			'month' => (object) [
				'view_url'   => 'https://test.tri.be/events/month/',
				'view_class' => 'Tribe\Events\Views\V2\Views\Month_View',
				'view_label' => 'Month',
				'aria_label' => 'Display Events in Month View',
			],
			'day'   => (object) [
				'view_url'   => 'https://test.tri.be/events/today/',
				'view_class' => 'Tribe\Events\Views\V2\Views\Day_View',
				'view_label' => 'Day',
				'aria_label' => 'Display Events in Day View',
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
}
