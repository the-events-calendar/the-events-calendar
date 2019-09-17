<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar\Search;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class KeywordTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/search/keyword';

	/**
	 * Test render
	 */
	public function test_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}

	/**
	 * Test render with keyword
	 */
	public function test_render_with_keyword() {
		add_filter( 'tribe_events_template_var', function( $value, $key, $default, $view_slug ) {
			if ( 'bar-keyword' === implode( '-', $key ) ) {
				return 'keyword_value';
			}

			return $value;
		}, 10, 4 );
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
