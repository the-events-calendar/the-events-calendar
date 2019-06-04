<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Events\Views\V2\Views\ViewTestCase;
use Tribe\Events\Views\V2\View;

class Month_ViewTest extends ViewTestCase {
	use MatchesSnapshots;

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$month_view = View::make( Month_View::class );
		$html       = $month_view->get_html();

		$this->assertMatchesSnapshot( $html );
	}
}
