<?php

namespace Tribe\Events\Views\V2\Partials\Month;

use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Calendar_BodyTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-body';

	/**
	 * Test static render
	 */
	public function test_static_render() {
		// Mock the `time` function to return 2019-6-21 as the current day.
		Test::replace( 'time', function () {
			return ( new \DateTime( '2019-06-21 12:00:00', new \DateTimeZone( 'UTC' ) ) )
				->getTimestamp();
		} );
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}

	public function setUp() {
		parent::setUp();
		// Start Function Mocker.
		Test::setUp();
		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );
	}
}
