<?php

namespace Tribe\Events\Views\Modules;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Map_BasicTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * Test render google maps iframe.
	 */
	public function test_render_google_maps_iframe() {
		$venue     = "My Test Venue";
		$width     = "100";
		$height    = "200";
		$embed_url = "http://example.com";
	  
		ob_start();
		include "src/views/modules/map-basic.php";
		$html = ob_get_clean();

		$this->assertMatchesSnapshot( $html );
	}
}
