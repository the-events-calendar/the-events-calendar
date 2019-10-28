<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class BreadcrumbsTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/breadcrumbs';

	/**
	 * Test render empty.
	 */
	public function test_render_empty() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [] ) );
	}

	/**
	 * Test render with category.
	 */
	public function test_render_with_category() {
		$this->markTestSkipped( 'Finish test when we can mock categories' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [] ) );
	}
}
