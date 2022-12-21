<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DataTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/data';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$view      = View::make( Month_View::class );
		$view_slug = $view::get_view_slug();

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'view'      => $view,
			'view_slug' => $view_slug,
		] ) );
	}
}
