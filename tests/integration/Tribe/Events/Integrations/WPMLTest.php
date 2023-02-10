<?php

namespace Tribe\Events\Integrations;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Integrations__WPML__Rewrites;

class WPMLTest extends WPTestCase {

	public function rewrite_rules_data_provider() {
		return [
			// Expected, Faux Rules
			'string'      =>
				[ 'bob', 'bob' ]
			,
			'int'         => [ 1, 1 ],
			'bool'        =>
				[ false, false ],
			'null'        =>
				[ null, null ],
			'empty array' => [ [], [] ]

		];
	}

	/**
	 * @test
	 * @dataProvider rewrite_rules_data_provider
	 */
	public function should_handle_filter_rewrite_rules_ok( $expected, $faux_rewrite ) {
		$wpml_rewrite = Tribe__Events__Integrations__WPML__Rewrites::instance();

		// Ensure no errors and the data is translated as expected.
		$this->assertEquals( $expected, $wpml_rewrite->filter_rewrite_rules_array( $faux_rewrite ) );
	}
}
