<?php

namespace Tribe\Events\Views\V2\Partials\Components\Backlink;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class BacklinkTest extends HtmlPartialTestCase
{
	/**
	 * Path to the backlink partial under test.
	 *
	 * @var string
	 */
	protected $partial_path = 'components/backlink';

	/**
	 * Test render with a backlink.
	 *
	 * @since TBD
	 */
	public function test_render_with_backlink() {
		$backlink = [
			'url'   => 'https://example.com/events',
			'label' => 'Back to Events',
		];

		$this->assertMatchesSnapshot(
			$this->get_partial_html( [ 'backlink' => $backlink ] )
		);
	}

	/**
	 * Test render without a backlink (empty array should return nothing).
	 *
	 * @since TBD
	 */
	public function test_render_without_backlink() {
		$backlink = [];

		$this->assertSame(
			'',
			$this->get_partial_html( [ 'backlink' => $backlink ] )
		);
	}
}
