<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Read_MoreTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'components/read-more';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$event = $this->get_mock_event( 'events/single/1.json' );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}