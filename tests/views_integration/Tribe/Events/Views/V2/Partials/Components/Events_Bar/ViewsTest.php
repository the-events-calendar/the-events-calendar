<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class ViewsTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/views';

	/**
	 * Test render with views with tabs style
	 */
	public function test_render_with_views_with_tabs_style() {
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

	/**
	 * Test render with views without tabs style
	 */
	public function test_render_with_views_without_tabs_style() {
		// fake a 4th view by reusing month view to force Manager to return more than 3 publicly visible views
		add_filter( 'tribe_events_views', function( $views ) {
			return array_merge( $views, [ 'month2' => Month_View::class ] );
		} );
		$views = [
			'list'   => 'Tribe\Events\Views\V2\Views\List_View',
			'month'  => 'Tribe\Events\Views\V2\Views\Month_View',
			'day'    => 'Tribe\Events\Views\V2\Views\Day_View',
			'month2' => 'Tribe\Events\Views\V2\Views\Month_View',
		];
		$view  = View::make( 'Tribe\Events\Views\V2\Views\List_View' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'views' => $views,
			'view'  => $view,
		] ) );
	}
}
