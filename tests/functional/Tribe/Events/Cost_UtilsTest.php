<?php
namespace Tribe\Events;

class Cost_UtilsTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( 'Tribe__Events__Cost_Utils', $sut );
	}

	public function merge_cost_ranges_into_ranges() {
		// $expected, $original_string_cost, $merging_cost, $with_currency_symbol, $sorted_mins, $sorted_maxs
		return [
			[ 'Free', 'Free', '' ],
			[ 'Free', 'Free', 'Free' ],
			[ 'Free', 'Free', [ 'Free' ] ],
			[
				[ 'Free', 'Not free' ],
				'Free',
				[ 'Free', 'Not free' ],
				false,
				[ 'Free', 'Not free' ],
				[ 'Free', 'Not free' ]
			],
			[ [ 'Free', 20 ], 'Free', [ 'Free', 20 ], false, [ 'Free', 'Not free' ] ],
			[ [ 'Free', 20 ], 10, [ 'Free', 20 ], false, [ 'Free', 'Not free' ] ],
			[ [ 'Free', 30 ], 30, [ 'Free', 20 ], false, [ 'Free', 'Not free' ] ],
			[ [ 'Free', 20 ], 'Donation - $10', [ 'Free', 20 ], false, [ 'Free', 'Not free' ] ],
			[ [ 'Free', 30 ], 'Donation - $30', [ 'Free', 20 ], false, [ 'Free', 'Not free' ] ],
			[ [ 'Free', 30 ], 'Donation - $30', [ 'Free', 20 ], false, [ 'Donation', 'Free' ] ],
			// Cannot parse text values without separator and no way to know separator.
			[ [ 'Donation', 20 ], 'Donation', [ 'Free', 20 ], false, [ 'Donation', 'Free' ] ],
			[
				[ 'Donation', 'Private deal' ],
				'Donation',
				[ 'Free', 'Private deal' ],
				false,
				[ 'Donation', 'Free' ],
				[ 'Private deal' ]
			],
			[
				[ 'Donation', 'Private deal' ],
				'Donation',
				[ 'Free', '$1000', 'Private deal' ],
				false,
				[ 'Donation', 'Free' ],
				[ 'Private deal' ]
			],
			[
				[ 'Donation', 'Private deal' ],
				'Free',
				[ 'Donation', '$1000', 'Private deal' ],
				false,
				[ 'Donation', 'Free' ],
				[ 'Private deal' ]
			],
			[ [ 'Free', '$20' ], 'Free', [ '10', '15', '20' ], true, [ 'Donation', 'Free' ], [ 'Private deal' ] ],
			[ [ '$8', '$20' ], '$8 - $12', [ '10', '15', '20' ], true, [ 'Donation', 'Free' ], [ 'Private deal' ] ],
			[ [ '$5', '$20' ], '$8 - $12', [ '5', '15', '20' ], true, [ 'Donation', 'Free' ], [ 'Private deal' ] ],
		];
	}

	/**
	 * merge_cost_ranges_into
	 *
	 * @dataProvider merge_cost_ranges_into_ranges
	 */
	public function test_merge_cost_ranges_into( $expected, $original_string_cost = '', $merging_cost = '', $with_currency_symbol = false, $sorted_mins = array(), $sorted_maxs = array() ) {

		add_filter( 'tribe_currency_symbol',
			function () {
				return '$';
			},
			100 );
		add_filter( 'tribe_reverse_currency_position', '__return_false');

		$sut = $this->make_instance();

		$out = $sut->merge_cost_ranges( $original_string_cost,
			$merging_cost,
			$with_currency_symbol,
			$sorted_mins,
			$sorted_maxs );

		$this->assertEquals( $expected, $out );
	}

	private function make_instance() {
		return new \Tribe__Events__Cost_Utils();
	}
}