<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class StateTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

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

		new State( new Events );
	}

	/**
	 * It should not require migration if there are no events
	 *
	 * @test
	 */
	public function should_not_require_migration_if_there_are_no_events() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_NOT_REQUIRED );
		$events = new Events;

		// Run a new fetch to make sure the status "sticks".
		$state       = new State( $events );
		$is_required = $state->is_required();

		$this->assertFalse( $is_required );
	}

	/**
	 * It should require migration if migration is running
	 *
	 * @test
	 */
	public function should_require_migration_if_migration_is_running() {
		$this->given_the_current_migration_phase_is( State::PHASE_PREVIEW_IN_PROGRESS );
		$this->given_a_non_migrated_single_event();

		$events      = new Events;
		$state       = new State( $events );
		$is_required = $state->is_required();

		$this->assertTrue( $is_required );
	}

	/**
	 * It should not require migration if
	 *
	 * @test
	 */
	public function should_not_require_migration_if_completed() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_COMPLETE );
		$this->given_a_migrated_single_event();

		$events      = new Events;
		$state       = new State( $events );
		$is_required = $state->is_required();

		$this->assertFalse( $is_required );
	}
}