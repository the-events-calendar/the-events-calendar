<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar\Views;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
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
			'list'  => List_View::class,
			'month' => Month_View::class,
			'day'   => Day_View::class,
		];
		$view  = View::make( List_View::class );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'views' => $views,
			'view'  => $view,
		] ) );
	}
}
