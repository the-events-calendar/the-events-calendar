<?php

namespace Tribe\Events\Views\V2\Partials\Components\Breadcrumb;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Linked_BreadcrumbTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/breadcrumbs/linked-breadcrumb';

	/**
	 * Test render with breadcrumb.
	 */
	public function test_render_with_breadcrumb() {
		$breadcrumb = [
			'link'  => 'https://test.tri.be/events/list',
			'label' => 'Events',
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'breadcrumb' => $breadcrumb ] ) );
	}
}
