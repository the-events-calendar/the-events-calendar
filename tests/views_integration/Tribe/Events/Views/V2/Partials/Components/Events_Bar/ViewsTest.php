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
			(object) [
				'view_url'        => 'https://test.tri.be/events/month2/',
				'view_slug'       => 'month2',
				'view_label'      => 'Month2',
				'is_current_view' => false,
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
			'disable_event_search' => true,
			'view_slug'            => 'list',
			'view_label'           => 'List',
			'public_views'         => $public_views,
		] ) );
	}
}
