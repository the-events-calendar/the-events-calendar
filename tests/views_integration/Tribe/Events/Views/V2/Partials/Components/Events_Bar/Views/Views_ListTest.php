<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar\Views;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Views_ListTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/views/list';

	/**
	 * Test render without views
	 */
	public function test_render_without_views() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'public_views' => [],
		] ) );
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
				'is_current_view' => false,
			],
			(object) [
				'view_url'        => 'https://test.tri.be/events/month/',
				'view_slug'       => 'month',
				'view_label'      => 'Month',
				'is_current_view' => true,
			],
			(object) [
				'view_url'        => 'https://test.tri.be/events/today/',
				'view_slug'       => 'day',
				'view_label'      => 'Day',
				'is_current_view' => false,
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'public_views' => $public_views,
		] ) );
	}
}
