<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar;

use Tribe\Tests\Traits\With_Uopz;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class SearchTest extends HtmlPartialTestCase {
	use With_Uopz;

	protected $partial_path = 'components/events-bar/search';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->set_fn_return( 'wp_create_nonce', '2ab7cc6b39' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'url' => 'http://test.tri.be' ] ) );
	}
}
