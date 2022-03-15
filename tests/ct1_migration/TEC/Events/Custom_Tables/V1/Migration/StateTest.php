<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

class StateTest extends \CT1_Migration_Test_Case {
	public function bad_option_values() {
		return [
			'null'         => [ null ],
			'empty string' => [ '' ],
			'number'       => [ 23 ],
			'random array' => [ [ 'foo' => 'bar', 'baz' => 89 ] ],
		];
	}

	/**
	 * It should correctly initialize on bad option values
	 *
	 * Since there's a bit of DB cleanup between tests, let's speed them up a bit
	 * by not calling separate test methods.
	 *
	 * @dataProvider bad_option_values
	 * @test
	 */
	public function should_correctly_initialize_on_bad_option_values( $option_value ) {
		tribe_update_option( State::STATE_OPTION_KEY, $option_value );

		new State();
	}
}