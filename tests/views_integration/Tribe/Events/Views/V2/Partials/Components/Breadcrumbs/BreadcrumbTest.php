<?php

namespace Tribe\Events\Views\V2\Partials\Components\Breadcrumb;

use Generator;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class BreadcrumbTest extends HtmlPartialTestCase {

	protected $partial_path = 'components/breadcrumbs';

	/**
	 * Data provider for breadcrumb rendering tests.
	 *
	 * @since 6.15.15
	 *
	 * @return Generator
	 */
	public function breadcrumb_data_provider() {
		yield 'empty breadcrumbs array' => [
			[],
			false,
		];

		yield 'single linked breadcrumb' => [
			[
				[
					'link'    => 'https://test.tri.be/events/list',
					'label'   => 'Events',
					'is_last' => true,
				],
			],
			true,
		];

		yield 'single unlinked breadcrumb' => [
			[
				[
					'label'   => 'Current Page',
					'is_last' => true,
				],
			],
			true,
		];

		yield 'mixed breadcrumbs with last item unlinked' => [
			[
				[
					'link'    => 'https://test.tri.be/events/list',
					'label'   => 'Events',
					'is_last' => false,
				],
				[
					'link'    => 'https://test.tri.be/events/category/virtual',
					'label'   => 'Virtual Events',
					'is_last' => false,
				],
				[
					'label'   => 'Current Event',
					'is_last' => true,
				],
			],
			true,
		];

		yield 'all linked breadcrumbs with last item marked' => [
			[
				[
					'link'    => 'https://test.tri.be/events/list',
					'label'   => 'Events',
					'is_last' => false,
				],
				[
					'link'    => 'https://test.tri.be/events/category/virtual',
					'label'   => 'Virtual Events',
					'is_last' => false,
				],
				[
					'link'    => 'https://test.tri.be/events/123',
					'label'   => 'Current Event',
					'is_last' => true,
				],
			],
			true,
		];

		yield 'breadcrumbs with title attributes' => [
			[
				[
					'link'    => 'https://test.tri.be/events/list',
					'label'   => 'Events',
					'title'   => 'View all events',
					'is_last' => false,
				],
				[
					'label'   => 'Current Page',
					'is_last' => true,
				],
			],
			true,
		];

		yield 'breadcrumbs with empty label (should skip)' => [
			[
				[
					'link'    => 'https://test.tri.be/events/list',
					'label'   => 'Events',
					'is_last' => false,
				],
				[
					'label'   => '',
					'is_last' => false,
				],
				[
					'label'   => 'Current Page',
					'is_last' => true,
				],
			],
			true,
		];

		yield 'breadcrumbs with empty link (should render as unlinked)' => [
			[
				[
					'link'    => 'https://test.tri.be/events/list',
					'label'   => 'Events',
					'is_last' => false,
				],
				[
					'link'    => '',
					'label'   => 'Virtual Events',
					'is_last' => false,
				],
				[
					'label'   => 'Current Page',
					'is_last' => true,
				],
			],
			true,
		];

		yield 'breadcrumbs with whitespace labels' => [
			[
				[
					'link'    => 'https://test.tri.be/events/list',
					'label'   => 'Events',
					'is_last' => false,
				],
				[
					'label'   => '   ',
					'is_last' => false,
				],
				[
					'label'   => 'Current Page',
					'is_last' => true,
				],
			],
			true,
		];

		yield 'single breadcrumb without is_last flag' => [
			[
				[
					'link'  => 'https://test.tri.be/events/list',
					'label' => 'Events',
				],
			],
			true,
		];
	}

	/**
	 * Test render with various breadcrumb data scenarios.
	 *
	 * @dataProvider breadcrumb_data_provider
	 * @since 6.15.15
	 *
	 * @param array $breadcrumb_data The breadcrumb data to test.
	 * @param bool  $should_render   Whether the breadcrumbs should render.
	 */
	public function test_render_with_breadcrumb_data( $breadcrumb_data, $should_render ) {
		$result = $this->get_partial_html( [ 'breadcrumbs' => $breadcrumb_data ] );

		if ( $should_render ) {
			$this->assertMatchesSnapshot( $result );
		} else {
			$this->assertSame( '', $result );
		}
	}

	/**
	 * Test that the last breadcrumb item is properly marked as current.
	 *
	 * @since 6.15.15
	 */
	public function test_last_breadcrumb_is_current() {
		$breadcrumbs = [
			[
				'link'    => 'https://test.tri.be/events/list',
				'label'   => 'Events',
				'is_last' => false,
			],
			[
				'link'    => 'https://test.tri.be/events/category/virtual',
				'label'   => 'Virtual Events',
				'is_last' => false,
			],
			[
				'label'   => 'Current Page',
				'is_last' => true,
			],
		];

		$result = $this->get_partial_html( [ 'breadcrumbs' => $breadcrumbs ] );

		// Verify the last item has aria-current="page".
		$this->assertStringContainsString( 'aria-current="page"', $result );

		// Verify only the last item has aria-current="page".
		$this->assertEquals( 1, substr_count( $result, 'aria-current="page"' ) );

		// Verify there are caret icons for non-last items (should be 2 for the first two items).
		$this->assertEquals( 2, substr_count( $result, 'tribe-events-c-breadcrumbs__list-item-icon-svg' ) );
	}

	/**
	 * Test that the last breadcrumb is automatically marked as current even when explicitly set to false.
	 *
	 * @since 6.15.15
	 */
	public function test_last_breadcrumb_automatically_marked_current() {
		$breadcrumbs = [
			[
				'link'    => 'https://test.tri.be/events/list',
				'label'   => 'Events',
				'is_last' => false,
			],
			[
				'label'   => 'Category',
				'is_last' => false, // This will be overridden to true by the template.
			],
		];

		$result = $this->get_partial_html( [ 'breadcrumbs' => $breadcrumbs ] );

		// Verify the last breadcrumb has aria-current="page" (automatically set).
		$this->assertStringContainsString( 'aria-current="page"', $result );

		// Verify only one breadcrumb has aria-current="page".
		$this->assertEquals( 1, substr_count( $result, 'aria-current="page"' ) );

		// Verify only the first item has a caret icon (last item won't have one).
		$this->assertEquals( 1, substr_count( $result, 'tribe-events-c-breadcrumbs__list-item-icon-svg' ) );
	}

	/**
	 * Test that the last breadcrumb is automatically marked as current when no is_last flags are set.
	 *
	 * @since 6.15.15
	 */
	public function test_breadcrumb_without_is_last_flag_automatically_marked() {
		$breadcrumbs = [
			[
				'link'  => 'https://test.tri.be/events/list',
				'label' => 'Events',
			],
			[
				'label' => 'Category', // This will be automatically marked as last.
			],
		];

		$result = $this->get_partial_html( [ 'breadcrumbs' => $breadcrumbs ] );

		// Verify the last breadcrumb has aria-current="page" (automatically set).
		$this->assertStringContainsString( 'aria-current="page"', $result );

		// Verify only one breadcrumb has aria-current="page".
		$this->assertEquals( 1, substr_count( $result, 'aria-current="page"' ) );

		// Verify only the first item has a caret icon (last item won't have one).
		$this->assertEquals( 1, substr_count( $result, 'tribe-events-c-breadcrumbs__list-item-icon-svg' ) );
	}

	/**
	 * Test that the last breadcrumb is always marked as current with multiple breadcrumbs.
	 *
	 * @since 6.15.15
	 */
	public function test_last_breadcrumb_always_current_with_multiple_items() {
		$breadcrumbs = [
			[
				'link'    => 'https://test.tri.be/events/list',
				'label'   => 'Events',
				'is_last' => false,
			],
			[
				'link'    => 'https://test.tri.be/events/category/virtual',
				'label'   => 'Virtual Events',
				'is_last' => false,
			],
			[
				'link'    => 'https://test.tri.be/events/category/virtual/workshops',
				'label'   => 'Workshops',
				'is_last' => false,
			],
			[
				'link'    => 'https://test.tri.be/events/123',
				'label'   => 'Current Event',
				'is_last' => true,
			],
		];

		$result = $this->get_partial_html( [ 'breadcrumbs' => $breadcrumbs ] );

		// Verify only one breadcrumb has aria-current="page".
		$this->assertEquals( 1, substr_count( $result, 'aria-current="page"' ) );

		// Verify caret icons appear exactly 3 times (one less than total breadcrumbs).
		$this->assertEquals( 3, substr_count( $result, 'tribe-events-c-breadcrumbs__list-item-icon-svg' ) );

		// Verify the last item specifically has aria-current="page".
		$this->assertStringContainsString( 'aria-current="page"', $result );
	}

	/**
	 * Test that the last breadcrumb is always marked as current even when it's unlinked.
	 *
	 * @since 6.15.15
	 */
	public function test_last_breadcrumb_always_current_when_unlinked() {
		$breadcrumbs = [
			[
				'link'    => 'https://test.tri.be/events/list',
				'label'   => 'Events',
				'is_last' => false,
			],
			[
				'link'    => 'https://test.tri.be/events/category/virtual',
				'label'   => 'Virtual Events',
				'is_last' => false,
			],
			[
				'link'    => 'https://test.tri.be/events/category/virtual/workshops',
				'label'   => 'Workshops',
				'is_last' => false,
			],
			[
				'label'   => 'Current Page (No Link)',
				'is_last' => true,
			],
		];

		$result = $this->get_partial_html( [ 'breadcrumbs' => $breadcrumbs ] );

		// Verify only one breadcrumb has aria-current="page".
		$this->assertEquals( 1, substr_count( $result, 'aria-current="page"' ) );

		// Verify caret icons appear exactly 3 times (one less than total breadcrumbs).
		$this->assertEquals( 3, substr_count( $result, 'tribe-events-c-breadcrumbs__list-item-icon-svg' ) );

		// Verify the last item specifically has aria-current="page".
		$this->assertStringContainsString( 'aria-current="page"', $result );
	}

	/**
	 * Test that the last breadcrumb is always marked as current with mixed link types.
	 *
	 * @since 6.15.15
	 */
	public function test_last_breadcrumb_always_current_with_mixed_links() {
		$breadcrumbs = [
			[
				'link'    => 'https://test.tri.be/events/list',
				'label'   => 'Events',
				'is_last' => false,
			],
			[
				'link'    => '',
				'label'   => 'Virtual Events (Empty Link)',
				'is_last' => false,
			],
			[
				'link'    => 'https://test.tri.be/events/category/virtual/workshops',
				'label'   => 'Workshops',
				'is_last' => false,
			],
			[
				'label'   => 'Current Page (No Link)',
				'is_last' => true,
			],
		];

		$result = $this->get_partial_html( [ 'breadcrumbs' => $breadcrumbs ] );

		// Verify only one breadcrumb has aria-current="page".
		$this->assertEquals( 1, substr_count( $result, 'aria-current="page"' ) );

		// Verify caret icons appear exactly 3 times (one less than total breadcrumbs).
		$this->assertEquals( 3, substr_count( $result, 'tribe-events-c-breadcrumbs__list-item-icon-svg' ) );

		// Verify the last item specifically has aria-current="page".
		$this->assertStringContainsString( 'aria-current="page"', $result );
	}
}
