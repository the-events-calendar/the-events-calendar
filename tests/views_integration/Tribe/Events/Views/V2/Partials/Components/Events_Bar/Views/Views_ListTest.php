<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar\Views;

use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Views_ListTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/views/list';

	/**
	 * Test render without views
	 */
	public function test_render_without_views() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'views' => [],
		] ) );
	}

	/**
	 * Test render with views
	 */
	public function test_render_with_views() {
		$views = [
			'list'  => 'Tribe\Events\Views\V2\Views\List_View',
			'month' => 'Tribe\Events\Views\V2\Views\Month_View',
			'day'   => 'Tribe\Events\Views\V2\Views\Day_View',
		];
		$view  = View::make( 'Tribe\Events\Views\V2\Views\List_View' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'views' => $views,
			'view'  => $view,
		] ) );
	}
}
