<?php

namespace Tribe\Events\Views\V2\Partials\Widgets\Widget_Events_List;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class View_MoreTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'widgets/widget-events-list/view-more';

	/**
	 * Test render with link
	 */
	public function test_render_with_link() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'view_more_link' => 'https://test.tri.be/' ] ) );
	}

	/**
	 * Test render without link
	 */
	public function test_render_without_link() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'view_more_link' => '' ] ) );
	}
}
