<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;

class Month_ViewTest extends ViewTestCase {
	use MatchesSnapshots;

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$this->markTestSkipped( 'To be reviewed after snapshot testing review.' );

		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$month_view = View::make( Month_View::class );
		$html       = $month_view->get_html();

		$this->assertMatchesSnapshot( $html );
	}
}
