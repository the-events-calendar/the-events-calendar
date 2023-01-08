<?php

namespace Tribe\Events\Views\Modules;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

/**
 * Class Map_BasicTest.
 *
 * @package Tribe\Events\Views\Modules
 */
class Map_BasicTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * Test render google maps iframe with mock data.
	 */
	public function test_render_google_maps_iframe_with_mock_data() {
		ob_start();
		tribe_get_template_part(
			'modules/map-basic',
			null,
			[
				'venue'     => 'My test venue',
				'embed_url' => 'http://example.com',
				'width'     => 100,
				'height'    => 200,
			]
		);
		$html = ob_get_clean();

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * Test render google maps iframe when a valid venue ID is passed and no venue title passed.
	 */
	public function test_render_google_maps_iframe_venue_id_passed_no_venue_title_passed() {

	}

	/**
	 * Test render google maps iframe when no venue ID and title are passed.
	 */
	public function test_render_google_maps_iframe_no_venue_id_passed_no_venue_title_passed() {

	}

	/**
	 * Test render google maps iframe when no venue ID is passed but a valid venue title is.
	 */
	public function test_render_google_maps_iframe_no_venue_id_passed_valid_venue_title_passed() {

	}

	/**
	 * Test render google maps iframe when an incorrect venue ID is passed and no venue title is.
	 */
	public function test_render_google_maps_iframe_incorrect_venue_id_passed_no_venue_title_passed() {

	}
}
