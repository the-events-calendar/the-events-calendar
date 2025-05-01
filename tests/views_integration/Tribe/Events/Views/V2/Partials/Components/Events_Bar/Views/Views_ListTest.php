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
			'view_slug'    => 'month',
			'public_views' => $public_views,
		] ) );
	}
}
