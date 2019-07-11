<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Month_ViewTest extends ViewTestCase {
	use MatchesSnapshots;

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$month_view = View::make( Month_View::class );
		$html       = $month_view->get_html();

		$this->assertEmpty( $month_view->found_post_ids() );

		$this->assertMatchesSnapshot( $html );
	}
}
