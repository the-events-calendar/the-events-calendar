<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class ViewsTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/views';

	/**
	 * Test render with views with tabs style
	 */
	public function test_render_with_views_with_tabs_style() {
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
			'disable_event_search' => false,
			'view_slug'            => 'list',
			'view_label'           => 'List',
			'public_views'         => $public_views,
		] ) );
	}

	/**
	 * Test render with views with label style
	 */
	public function test_render_with_views_with_label_style() {
		// fake a 4th view
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
			'week'  => (object) [
				'view_url'   => 'https://test.tri.be/events/week/',
				'view_class' => 'Tribe\Events\Views\V2\Views\Week_View',
				'view_label' => 'Week',
				'aria_label' => 'Display Events in Week View',
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'disable_event_search' => false,
			'view_slug'            => 'list',
			'view_label'           => 'List',
			'public_views'         => $public_views,
		] ) );
	}

	/**
	 * Test render with views with disabled event search
	 */
	public function test_render_with_views_with_disabled_event_search() {
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
			'disable_event_search' => true,
			'view_slug'            => 'list',
			'view_label'           => 'List',
			'public_views'         => $public_views,
		] ) );
	}
}
