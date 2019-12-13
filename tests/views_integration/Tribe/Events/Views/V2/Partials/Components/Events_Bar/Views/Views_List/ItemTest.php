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
			'public_view' => (object) [
				'view_url'        => 'https://test.tri.be/events/month/',
				'view_slug'       => 'month',
				'view_label'      => 'Month',
				'is_current_view' => false,
			],
		] ) );
	}

	/**
	 * Test render with view is current view
	 */
	public function test_render_with_view_is_current_view() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'public_view' => (object) [
				'view_url'        => 'https://test.tri.be/events/month/',
				'view_slug'       => 'month',
				'view_label'      => 'Month',
				'is_current_view' => true,
			],
		] ) );
	}
}
