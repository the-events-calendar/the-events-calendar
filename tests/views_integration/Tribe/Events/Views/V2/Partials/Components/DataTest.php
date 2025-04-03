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
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'view'      => View::make( Month_View::class ),
			'view_slug' => Month_View::get_view_slug(),
		] ) );
	}
}
