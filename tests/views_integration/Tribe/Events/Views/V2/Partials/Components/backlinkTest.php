<?php

namespace Tribe\Events\Views\V2\Partials\Components\Backlink;

use Generator;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class BacklinkTest extends HtmlPartialTestCase {
	/**
	 * Path to the backlink partial under test.
	 *
	 * @var string
	 */
	protected $partial_path = 'components/backlink';

	/**
	 * Data provider for backlink rendering tests.
	 *
	 * @since TBD
	 *
	 * @return Generator
	 */
	public function backlink_data_provider() {
		yield 'complete backlink data' => [
			[
				'url'   => 'https://example.com/events',
				'label' => 'Back to Events',
			],
			true, // Should render
		];

		yield 'backlink with empty url' => [
			[
				'url'   => '',
				'label' => 'Back to Events',
			],
			false, // Should not render
		];

		yield 'backlink with empty label' => [
			[
				'url'   => 'https://example.com/events',
				'label' => '',
			],
			false,
		];

		yield 'backlink with missing url' => [
			[
				'label' => 'Back to Events',
			],
			false,
		];

		yield 'backlink with missing label' => [
			[
				'url' => 'https://example.com/events',
			],
			false,
		];

		yield 'empty backlink array' => [
			[],
			false,
		];

		yield 'backlink with null values' => [
			[
				'url'   => null,
				'label' => null,
			],
			false,
		];

		yield 'backlink with whitespace-only values' => [
			[
				'url'   => '   ',
				'label' => '   ',
			],
			true,
		];
	}

	/**
	 * Test render with various backlink data scenarios.
	 *
	 * @dataProvider backlink_data_provider
	 * @since TBD
	 *
	 * @param array $backlink_data The backlink data to test.
	 * @param bool  $should_render Whether the backlink should render.
	 */
	public function test_render_with_backlink_data( $backlink_data, $should_render ) {
		$result = $this->get_partial_html( [ 'backlink' => $backlink_data ] );

		if ( $should_render ) {
			$this->assertMatchesSnapshot( $result );
		} else {
			$this->assertSame( '', $result );
		}
	}
}
