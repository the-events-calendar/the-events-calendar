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
	 * Test render Google Maps iframe with mock data.
	 */
	public function test_render_google_maps_iframe_with_mock_data() {
		ob_start();
		tribe_get_template_part(
			'modules/map-basic',
			null,
			[
				'venue'     => 'My test venue',
				'embed_url' => 'https://example.com',
				'width'     => 100,
				'height'    => 200,
			]
		);
		$html = ob_get_clean();

		$this->assertMatchesSnapshot( $html );
	}
}
