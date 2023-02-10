<?php

namespace Tribe\Events\Views\Modules;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

/**
 * Class Map_BasicTest.
 *
 * @package Tribe\Events\Views\Modules
 */
class Map_BasicTest extends HtmlTestCase {

	/**
	 * Test rendering of Google Maps iframe when a venue is passed.
	 * 
	 * @test
	 */
	public function it_renders_google_maps_iframe_with_passed_venue() {
		ob_start();

		tribe_get_template_part(
			'modules/map-basic',
			null,
			[
				'venue'     => 'Jazz Club',
				'embed_url' => 'https://example.com/',
				'width'     => 100,
				'height'    => 200,
			]
		);

		$html = ob_get_clean();

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * Test rendering of Google Maps iframe using mocked global.
	 * 
	 * @test
	 */
	public function it_renders_google_maps_iframe_with_mocked_global() {
		ob_start();

		// Filter the output of tribe_get_venue to mock the global objects.
		add_filter(
			'tribe_get_venue',
			function ( $venue ) {
				return 'Disco Club';
			}
		);

		tribe_get_template_part(
			'modules/map-basic',
			null,
			[
				'venue'     => '',
				'embed_url' => 'https://example.com/',
				'width'     => 100,
				'height'    => 200,
			]
		);

		$html = ob_get_clean();

		remove_filter(
			'tribe_get_venue',
			function ( $venue ) {
				return 'Disco Club';
			}
		);

		$this->assertMatchesSnapshot( $html );
	}
}
