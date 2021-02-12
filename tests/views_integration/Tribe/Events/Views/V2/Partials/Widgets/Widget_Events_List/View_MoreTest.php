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
		$context = [
			'view_more_url' => 'https://test.tri.be/',
			'view_more_text' => 'View More',
			'view_more_title' => 'View more events.',
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render without link
	 */
	public function test_render_without_link() {
		$context = [
			'view_more_url' => '',
			'view_more_text' => 'View More',
			'view_more_title' => 'View more events.',
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}
}
