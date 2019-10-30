<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Read_MoreTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/read-more';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'permalink' => 'http://theeventscalendar.com' ] ) );
	}

}
