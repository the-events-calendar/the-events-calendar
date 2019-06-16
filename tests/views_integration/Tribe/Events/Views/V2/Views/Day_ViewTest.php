<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Events\Views\V2\TestCase;
use Tribe\Events\Views\V2\View;

class Day_ViewTest extends TestCase {

	use MatchesSnapshots;

	public function setUp()
	{
		parent::setUp();
		Test::setUp();
		Test::replace( 'date', function ( $format ) {
			return ( new \DateTime( '2019-01-01 09:00:00', new \DateTimeZone( 'UTC' ) ) )
				->format( $format );
		} );
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$this->markTestSkipped( 'Due to an issue with caching in CI.' );
		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$day_view = View::make( Day_View::class );
		$html     = $day_view->get_html();

		$this->assertMatchesSnapshot( $html );
	}


	public function tearDown(  ) {
		Test::tearDown();
	}
}
