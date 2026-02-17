<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Generator;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe\Tests\Traits\With_Uopz;

/**
 * Test suite for the header component template ordering.
 *
 * @since TBD
 */
class HeaderTest extends HtmlPartialTestCase {
	use With_Uopz;

	/**
	 * Path to the header partial under test.
	 *
	 * @var string
	 */
	protected $partial_path = 'components/header';

	/**
	 * Data provider for header template ordering tests.
	 *
	 * @since TBD
	 *
	 * @return Generator
	 */
	public function header_ordering_data_provider() {
		// When header_title exists, header-title should render first, then content-title.
		yield 'header_title exists - header-title first' => [
			[
				'header_title'  => 'Test Organizer',
				'content_title' => 'Events from this organizer',
				'show_content_title' => true,
			],
			'header-title',
			'content-title',
		];

		// When header_title doesn't exist, content-title should render first.
		yield 'no header_title - content-title first' => [
			[
				'header_title'  => '',
				'content_title' => 'Upcoming Events',
				'show_content_title' => true,
			],
			'content-title',
			null, // No second template should appear before content-title.
		];

		// Edge case: header_title exists but content_title is empty.
		yield 'header_title exists, no content_title' => [
			[
				'header_title'  => 'Test Venue',
				'content_title' => '',
				'show_content_title' => false,
			],
			'header-title',
			'content-title', // Still renders but as screen-reader-text.
		];

		// Edge case: neither exists.
		yield 'no header_title, no content_title' => [
			[
				'header_title'  => '',
				'content_title' => '',
				'show_content_title' => false,
			],
			'content-title', // Still renders as screen-reader-text.
			null,
		];
	}

	/**
	 * Test that title templates render in the correct order.
	 *
	 * @dataProvider header_ordering_data_provider
	 * @since TBD
	 *
	 * @param array       $template_vars Template variables to pass to the partial.
	 * @param string      $first_expected The first template that should appear.
	 * @param string|null $second_expected The second template that should appear, or null if none.
	 */
	public function test_title_templates_order( $template_vars, $first_expected, $second_expected ) {
		$view = View::make( View::class );

		// Override get_content_title_heading_tag to return h1 for content-title.
		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) use ( $template_vars ) {
				// If header_title exists, content-title should be h2, otherwise h1.
				if ( ! empty( $template_vars['header_title'] ) ) {
					return 'h2';
				}
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( array_merge(
			[
				'view' => $view,
			],
			$template_vars
		) );

		// Extract the positions of the title templates in the HTML.
		$header_title_pos = strpos( $result, 'tribe-events-header__title' );
		$content_title_pos = strpos( $result, 'tribe-events-header__content-title' );

		// Verify the first expected template appears.
		if ( 'header-title' === $first_expected ) {
			$this->assertNotFalse(
				$header_title_pos,
				'header-title template should be present in the output.'
			);
		} elseif ( 'content-title' === $first_expected ) {
			$this->assertNotFalse(
				$content_title_pos,
				'content-title template should be present in the output.'
			);
		}

		// Verify the ordering: first template should appear before second template.
		if ( 'header-title' === $first_expected && 'content-title' === $second_expected ) {
			// Both templates should be present when header_title exists.
			$this->assertNotFalse(
				$header_title_pos,
				'header-title template should be present when header_title exists.'
			);
			$this->assertNotFalse(
				$content_title_pos,
				'content-title template should be present when header_title exists.'
			);
			// header-title should appear before content-title.
			$this->assertLessThan(
				$content_title_pos,
				$header_title_pos,
				'header-title should appear before content-title when header_title exists.'
			);
		} elseif ( 'content-title' === $first_expected && null === $second_expected ) {
			// When no header_title, content-title should be first (and only title).
			$this->assertNotFalse(
				$content_title_pos,
				'content-title template should be present when header_title is empty.'
			);
			// header-title should not appear when header_title is empty.
			$this->assertFalse(
				$header_title_pos,
				'header-title should not appear when header_title is empty.'
			);
		}

		// Also verify the snapshot matches for visual inspection.
		$this->assertMatchesSnapshot( $result );
	}

	/**
	 * Test that heading tags are correct when both titles exist.
	 *
	 * @since TBD
	 */
	public function test_heading_tags_when_both_titles_exist() {
		$view = View::make( View::class );

		// Override get_content_title_heading_tag to return h1 initially.
		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( [
			'view'                => $view,
			'header_title'      => 'Test Organizer',
			'content_title'     => 'Events from this organizer',
			'show_content_title' => true,
		] );

		// Verify header-title uses h1.
		$this->assertStringContainsString(
			'<h1 class="tribe-events-header__title-text">',
			$result,
			'header-title should use h1 tag when header_title exists.'
		);

		// Verify content-title uses h2 (downgraded from h1).
		$this->assertStringContainsString(
			'<h2 class="',
			$result,
			'content-title should use h2 tag when header_title exists.'
		);
		$this->assertStringContainsString(
			'tribe-events-header__content-title',
			$result,
			'content-title should be present.'
		);
	}

	/**
	 * Test that heading tags are correct when only content-title exists.
	 *
	 * @since TBD
	 */
	public function test_heading_tags_when_only_content_title_exists() {
		$view = View::make( View::class );

		// Override get_content_title_heading_tag to return h1.
		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => '',
			'content_title'       => 'Upcoming Events',
			'show_content_title'  => true,
		] );

		// Verify content-title uses h1 (not downgraded).
		$this->assertStringContainsString(
			'<h1 class="',
			$result,
			'content-title should use h1 tag when header_title is empty.'
		);
		$this->assertStringContainsString(
			'tribe-events-header__content-title',
			$result,
			'content-title should be present.'
		);

		// Verify header-title does not appear.
		$this->assertStringNotContainsString(
			'tribe-events-header__title',
			$result,
			'header-title should not appear when header_title is empty.'
		);
	}

	/**
	 * Test that backlink/breadcrumb appears directly after the h1 when header_title exists.
	 *
	 * @since TBD
	 */
	public function test_backlink_after_h1_when_header_title_exists() {
		$view = View::make( View::class );

		// Override get_content_title_heading_tag to return h1 initially.
		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => 'Test Organizer',
			'content_title'       => 'Events from this organizer',
			'show_content_title'  => true,
			'backlink'            => [
				'url'   => 'https://example.com/events',
				'label' => 'Back to Events',
			],
		] );

		// Find positions of key elements.
		$h1_pos = strpos( $result, '<h1 class="tribe-events-header__title-text">' );
		$backlink_pos = strpos( $result, 'tribe-events-back' );
		$h2_pos = strpos( $result, '<h2 class="' );

		// Verify h1 appears first.
		$this->assertNotFalse( $h1_pos, 'h1 should be present.' );

		// Verify backlink appears after h1.
		if ( false !== $backlink_pos ) {
			$this->assertGreaterThan(
				$h1_pos,
				$backlink_pos,
				'backlink should appear after the h1 when header_title exists.'
			);
		}

		// Verify h2 (content-title) appears after backlink.
		if ( false !== $h2_pos && false !== $backlink_pos ) {
			$this->assertGreaterThan(
				$backlink_pos,
				$h2_pos,
				'content-title (h2) should appear after backlink when header_title exists.'
			);
		}

		$this->assertMatchesSnapshot( $result );
	}

	/**
	 * Test that breadcrumb appears directly after the h1 when header_title exists.
	 *
	 * @since TBD
	 */
	public function test_breadcrumb_after_h1_when_header_title_exists() {
		$view = View::make( View::class );

		// Override get_content_title_heading_tag to return h1 initially.
		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => 'Test Venue',
			'content_title'       => 'Events at this venue',
			'show_content_title'  => true,
			'breadcrumbs'         => [
				[
					'name' => 'Events',
					'link' => 'https://example.com/events',
				],
				[
					'name' => 'Venue',
					'link' => '',
				],
			],
		] );

		// Find positions of key elements.
		$h1_pos = strpos( $result, '<h1 class="tribe-events-header__title-text">' );
		$breadcrumb_pos = strpos( $result, 'tribe-events-header__breadcrumbs' );
		$h2_pos = strpos( $result, '<h2 class="' );

		// Verify h1 appears first.
		$this->assertNotFalse( $h1_pos, 'h1 should be present.' );

		// Verify breadcrumb appears after h1.
		if ( false !== $breadcrumb_pos ) {
			$this->assertGreaterThan(
				$h1_pos,
				$breadcrumb_pos,
				'breadcrumb should appear after the h1 when header_title exists.'
			);
		}

		// Verify h2 (content-title) appears after breadcrumb.
		if ( false !== $h2_pos && false !== $breadcrumb_pos ) {
			$this->assertGreaterThan(
				$breadcrumb_pos,
				$h2_pos,
				'content-title (h2) should appear after breadcrumb when header_title exists.'
			);
		}

		$this->assertMatchesSnapshot( $result );
	}

	/**
	 * Test that backlink/breadcrumb appears directly after the h1 when only content-title exists.
	 *
	 * @since TBD
	 */
	public function test_backlink_after_h1_when_only_content_title_exists() {
		$view = View::make( View::class );

		// Override get_content_title_heading_tag to return h1.
		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => '',
			'content_title'       => 'Upcoming Events',
			'show_content_title'  => true,
			'backlink'            => [
				'url'   => 'https://example.com',
				'label' => 'Back',
			],
		] );

		// Find positions of key elements.
		$h1_pos = strpos( $result, '<h1 class="' );
		$backlink_pos = strpos( $result, 'tribe-events-back' );

		// Verify h1 (content-title) appears first.
		$this->assertNotFalse( $h1_pos, 'h1 should be present.' );

		// Verify backlink appears after h1.
		if ( false !== $backlink_pos ) {
			$this->assertGreaterThan(
				$h1_pos,
				$backlink_pos,
				'backlink should appear after the h1 (content-title) when header_title is empty.'
			);
		}

		$this->assertMatchesSnapshot( $result );
	}

	/**
	 * Test complete heading order when header_title exists with backlink.
	 * Expected order: h1 (header-title) -> backlink -> h2 (content-title).
	 *
	 * @since TBD
	 */
	public function test_complete_heading_order_with_header_title_and_backlink() {
		$view = View::make( View::class );

		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => 'Test Organizer',
			'content_title'       => 'Events from this organizer',
			'show_content_title'  => true,
			'backlink'            => [
				'url'   => 'https://example.com/events',
				'label' => 'Back to Events',
			],
		] );

		// Find positions of all key elements.
		$h1_pos = strpos( $result, '<h1 class="tribe-events-header__title-text">' );
		$backlink_pos = strpos( $result, 'tribe-events-back' );
		$h2_pos = strpos( $result, '<h2 class="' );

		// Verify all elements are present.
		$this->assertNotFalse( $h1_pos, 'h1 (header-title) should be present.' );
		$this->assertNotFalse( $backlink_pos, 'backlink should be present.' );
		$this->assertNotFalse( $h2_pos, 'h2 (content-title) should be present.' );

		// Verify order: h1 -> backlink -> h2.
		$this->assertLessThan( $backlink_pos, $h1_pos, 'h1 should appear before backlink.' );
		$this->assertLessThan( $h2_pos, $backlink_pos, 'backlink should appear before h2.' );

		// Verify only one h1 exists.
		$h1_count = substr_count( $result, '<h1' );
		$this->assertEquals( 1, $h1_count, 'There should be exactly one h1 tag.' );

		$this->assertMatchesSnapshot( $result );
	}

	/**
	 * Test complete heading order when only content-title exists with breadcrumb.
	 * Expected order: h1 (content-title) -> breadcrumb.
	 *
	 * @since TBD
	 */
	public function test_complete_heading_order_with_content_title_and_breadcrumb() {
		$view = View::make( View::class );

		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => '',
			'content_title'       => 'Upcoming Events',
			'show_content_title'  => true,
			'breadcrumbs'         => [
				[
					'name' => 'Home',
					'link' => 'https://example.com',
				],
				[
					'name' => 'Events',
					'link' => '',
				],
			],
		] );

		// Find positions of key elements.
		$h1_pos = strpos( $result, '<h1 class="' );
		$breadcrumb_pos = strpos( $result, 'tribe-events-header__breadcrumbs' );

		// Verify elements are present.
		$this->assertNotFalse( $h1_pos, 'h1 (content-title) should be present.' );
		$this->assertNotFalse( $breadcrumb_pos, 'breadcrumb should be present.' );

		// Verify order: h1 -> breadcrumb.
		$this->assertLessThan( $breadcrumb_pos, $h1_pos, 'h1 should appear before breadcrumb.' );

		// Verify only one h1 exists and no h2.
		$h1_count = substr_count( $result, '<h1' );
		$h2_count = substr_count( $result, '<h2' );
		$this->assertEquals( 1, $h1_count, 'There should be exactly one h1 tag.' );
		$this->assertEquals( 0, $h2_count, 'There should be no h2 tags when only content-title exists.' );

		// Verify header-title does not appear.
		$this->assertStringNotContainsString(
			'tribe-events-header__title',
			$result,
			'header-title should not appear when header_title is empty.'
		);

		$this->assertMatchesSnapshot( $result );
	}

	/**
	 * Test heading order when header_title exists without navigation.
	 * Expected order: h1 (header-title) -> h2 (content-title).
	 *
	 * @since TBD
	 */
	public function test_heading_order_with_header_title_no_navigation() {
		$view = View::make( View::class );

		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		$result = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => 'Test Venue',
			'content_title'       => 'Events at this venue',
			'show_content_title'  => true,
			'backlink'            => null,
			'breadcrumbs'          => null,
		] );

		// Find positions of key elements.
		$h1_pos = strpos( $result, '<h1 class="tribe-events-header__title-text">' );
		$h2_pos = strpos( $result, '<h2 class="' );

		// Verify both headings are present.
		$this->assertNotFalse( $h1_pos, 'h1 (header-title) should be present.' );
		$this->assertNotFalse( $h2_pos, 'h2 (content-title) should be present.' );

		// Verify order: h1 -> h2.
		$this->assertLessThan( $h2_pos, $h1_pos, 'h1 should appear before h2.' );

		// Verify only one h1 exists.
		$h1_count = substr_count( $result, '<h1' );
		$this->assertEquals( 1, $h1_count, 'There should be exactly one h1 tag.' );

		// Verify no navigation appears.
		$this->assertStringNotContainsString(
			'tribe-events-back',
			$result,
			'backlink should not appear when not provided.'
		);
		$this->assertStringNotContainsString(
			'tribe-events-header__breadcrumbs',
			$result,
			'breadcrumb should not appear when not provided.'
		);

		$this->assertMatchesSnapshot( $result );
	}

	/**
	 * Test heading hierarchy - ensures no duplicate h1 tags.
	 *
	 * @since TBD
	 */
	public function test_heading_hierarchy_no_duplicate_h1() {
		$view = View::make( View::class );

		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) {
				return 'h1';
			},
			true
		);

		// Test with header_title (should have h1 from header-title, h2 from content-title).
		$result_with_header = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => 'Test Organizer',
			'content_title'       => 'Events from this organizer',
			'show_content_title'  => true,
		] );

		$h1_count = substr_count( $result_with_header, '<h1' );
		$h2_count = substr_count( $result_with_header, '<h2' );

		$this->assertEquals( 1, $h1_count, 'Should have exactly one h1 when header_title exists.' );
		$this->assertGreaterThan( 0, $h2_count, 'Should have at least one h2 when header_title exists.' );

		// Test without header_title (should have h1 from content-title only).
		$result_without_header = $this->get_partial_html( [
			'view'                => $view,
			'header_title'        => '',
			'content_title'       => 'Upcoming Events',
			'show_content_title'  => true,
		] );

		$h1_count_no_header = substr_count( $result_without_header, '<h1' );
		$h2_count_no_header = substr_count( $result_without_header, '<h2' );

		$this->assertEquals( 1, $h1_count_no_header, 'Should have exactly one h1 when only content-title exists.' );
		$this->assertEquals( 0, $h2_count_no_header, 'Should have no h2 when only content-title exists.' );
	}
}
