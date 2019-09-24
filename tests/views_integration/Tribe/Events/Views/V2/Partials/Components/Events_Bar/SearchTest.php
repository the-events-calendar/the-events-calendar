<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar;

use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class SearchTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/search';

	public function setUp() {
		parent::setUp();
		// Start Function Mocker.
		Test::setUp();
		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );
	}

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'url' => 'http://test.tri.be' ] ) );
	}

	public function tearDown(){
		Test::tearDown();
		parent::tearDown();
	}
}
