<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Generator;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe\Tests\Traits\With_Uopz;

class Content_TitleTest extends HtmlPartialTestCase {
	use With_Uopz;

	protected $partial_path = 'components/content-title';

	/**
	 * Data provider for content title rendering tests.
	 *
	 * @since TBD
	 *
	 * @return Generator
	 */
	public function content_title_data_provider() {
		// Valid heading tags h1-h6 with content title.
		yield 'h1 with content title' => [ 'h1', 'Upcoming Events' ];
		yield 'h2 with content title' => [ 'h2', 'Upcoming Events' ];
		yield 'h3 with content title' => [ 'h3', 'Upcoming Events' ];
		yield 'h4 with content title' => [ 'h4', 'Upcoming Events' ];
		yield 'h5 with content title' => [ 'h5', 'Upcoming Events' ];
		yield 'h6 with content title' => [ 'h6', 'Upcoming Events' ];

		// Valid heading tags without content title (screen reader text).
		yield 'h1 without content title' => [ 'h1', '' ];
		yield 'h2 without content title' => [ 'h2', '' ];
		yield 'h3 without content title' => [ 'h3', '' ];
		yield 'h4 without content title' => [ 'h4', '' ];
		yield 'h5 without content title' => [ 'h5', '' ];
		yield 'h6 without content title' => [ 'h6', '' ];

		// Invalid tags that fall back to h1.
		yield 'h0 falls back to h1' => [ 'h0', 'Upcoming Events' ];
		yield 'h7 falls back to h1' => [ 'h7', 'Upcoming Events' ];
		yield 'empty string falls back to h1' => [ '', 'Upcoming Events' ];
		yield 'div tag falls back to h1' => [ 'div', 'Upcoming Events' ];
		yield 'span tag falls back to h1' => [ 'span', 'Upcoming Events' ];
		yield 'p tag falls back to h1' => [ 'p', 'Upcoming Events' ];
		yield 'number only falls back to h1' => [ '1', 'Upcoming Events' ];
		yield 'uppercase H1 falls back to h1' => [ 'H1', 'Upcoming Events' ];
		yield 'h1 with space falls back to h1' => [ 'h1 ', 'Upcoming Events' ];
		yield 'h1 with extra text falls back to h1' => [ 'h1 class', 'Upcoming Events' ];

		// Special content title scenarios.
		yield 'h1 with XSS attempt' => [ 'h1', '<script>alert("XSS")</script>' ];
		yield 'h1 with HTML tags' => [ 'h1', '<strong>Bold Title</strong>' ];
		yield 'h1 with special characters' => [ 'h1', 'Events & "Things"' ];
	}

	/**
	 * Test render with various heading tags and content titles.
	 *
	 * @dataProvider content_title_data_provider
	 * @since TBD
	 *
	 * @param string $heading_tag   The heading tag to use.
	 * @param string $content_title The content title to render.
	 */
	public function test_render( $heading_tag, $content_title ) {
		// Override the get_content_title_heading_tag method to return our test heading tag.
		$this->set_class_fn_return(
			View::class,
			'get_content_title_heading_tag',
			function ( $default_tag ) use ( $heading_tag ) {
				// Simulate the actual validation logic from the View class.
				if ( empty( $heading_tag ) || 1 !== preg_match( '/^h[1-6]$/', $heading_tag ) ) {
					return $default_tag;
				}
				return $heading_tag;
			},
			true
		);

		$view = View::make( View::class );

		$result = $this->get_partial_html( [
			'view'          => $view,
			'content_title' => $content_title,
		] );

		$this->assertMatchesSnapshot( $result );
	}
}

