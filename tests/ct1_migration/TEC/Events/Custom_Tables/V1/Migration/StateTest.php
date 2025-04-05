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
		update_option( State::STATE_OPTION_KEY, $option_value );

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

	public function map_phase_to_dry_run(): array {
		return [
			'Given Migration Failure In Progress, should not be in dry run.' => [
				State::PHASE_MIGRATION_FAILURE_IN_PROGRESS,
				false
			],
			'Given Migration Failure Complete, should be in dry run.'        => [
				State::PHASE_MIGRATION_FAILURE_COMPLETE,
				true
			],
			'Given Migration Not Required, should not be in dry run.'        => [
				State::PHASE_MIGRATION_NOT_REQUIRED,
				false
			],
			'Given Migration Cancel Complete, should be in dry run.'         => [ State::PHASE_CANCEL_COMPLETE, true ],
			'Given Migration Revert Complete, should be in dry run.'         => [ State::PHASE_REVERT_COMPLETE, true ],
			'Given Preview Prompt, should be in dry run.'                    => [ State::PHASE_PREVIEW_PROMPT, true ],
			'Given Preview In Progress, should be in dry run.'               => [
				State::PHASE_PREVIEW_IN_PROGRESS,
				true
			],
			'Given Migration Prompt, should not be in dry run.'              => [
				State::PHASE_MIGRATION_PROMPT,
				false
			],
			'Given Migration In Progress, should not be in dry run.'         => [
				State::PHASE_MIGRATION_IN_PROGRESS,
				false
			],
			'Given Migration Complete, should not be in dry run.'            => [
				State::PHASE_MIGRATION_COMPLETE,
				false
			],
			'Given Migration Cancel In Progress, should not be in dry run.'  => [
				State::PHASE_CANCEL_IN_PROGRESS,
				false
			],
			'Given Migration Revert In Progress, should not be in dry run.'  => [
				State::PHASE_REVERT_IN_PROGRESS,
				false
			],
		];
	}

	/**
	 * It should detect dry run for proper states.
	 *
	 * @dataProvider map_phase_to_dry_run
	 * @test
	 */
	public function should_detect_dry_run_from_phase( $phase, $expected_dry_run ) {
		$this->given_the_current_migration_phase_is( $phase );
		$this->given_a_migrated_single_event();

		$state   = new State( new Events );
		$dry_run = $state->is_dry_run();

		$this->assertEquals(
			$expected_dry_run,
			$dry_run,
			"Given $phase our migration strategy should be dry_run: $expected_dry_run"
		);
	}
}