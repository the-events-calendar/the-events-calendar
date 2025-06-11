<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar\Views\Views_List;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class ItemTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/views/list/item';

	/**
	 * Test render with view is not current view
	 */
	public function test_render_with_view_is_not_current_view() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'view_slug'        => 'list',
			'public_view_slug' => 'month',
			'public_view_data' => (object) [
				'view_url'   => 'https://test.tri.be/events/month/',
				'view_class' => 'Tribe\Events\Views\V2\Views\Month_View',
				'view_label' => 'Month',
				'aria_label' => 'Display Events in Month View',
			],
		] ) );
	}

	/**
	 * Test render with view is current view
	 */
	public function test_render_with_view_is_current_view() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'view_slug'        => 'month',
			'public_view_slug' => 'month',
			'public_view_data' => (object) [
				'view_url'   => 'https://test.tri.be/events/month/',
				'view_class' => 'Tribe\Events\Views\V2\Views\Month_View',
				'view_label' => 'Month',
				'aria_label' => 'Display Events in Month View',
			],
		] ) );
	}
}
