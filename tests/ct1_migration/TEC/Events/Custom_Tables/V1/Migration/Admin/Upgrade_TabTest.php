<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\State;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Upgrade_TabTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * It should show if migration required
	 *
	 * @test
	 */
	public function should_show_if_migration_required() {
		$this->given_the_current_migration_phase_is( null );
		$this->given_a_non_migrated_single_event();
		$state = new State( new Events() );

		$tab         = new Upgrade_Tab( $state );
		$should_show = $tab->should_show();

		$this->assertTrue( $should_show );
	}

	/**
	 * It should show if migration preview is in progress
	 *
	 * @test
	 */
	public function should_show_if_migration_preview_is_in_progress() {
		$this->given_the_current_migration_phase_is( State::PHASE_PREVIEW_IN_PROGRESS );
		$this->given_a_non_migrated_single_event();
		$state = new State( new Events() );

		$tab         = new Upgrade_Tab( $state );
		$should_show = $tab->should_show();

		$this->assertTrue( $should_show );
	}

	/**
	 * It should show if the migration is running
	 *
	 * @test
	 */
	public function should_show_if_the_migration_is_running() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$this->given_a_migrated_single_event();
		$this->given_a_non_migrated_single_event();
		$state = new State( new Events() );

		$tab         = new Upgrade_Tab( $state );
		$should_show = $tab->should_show();

		$this->assertTrue( $should_show );
	}

	/**
	 * It should show if migration completed
	 *
	 * @test
	 */
	public function should_show_if_migration_completed() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_COMPLETE );
		$this->given_a_migrated_single_event();
		$state = new State( new Events() );

		$tab         = new Upgrade_Tab( $state );
		$should_show = $tab->should_show();

		$this->assertTrue( $should_show );
	}

	/**
	 * It should not show if migration is not required on first check
	 *
	 * @test
	 */
	public function should_show_if_migration_is_not_required_on_first_check() {
		$this->given_the_current_migration_phase_is( null );
		$events = new Events();
		$this->assertEquals( 0, $events->get_total_events() );
		$state = new State( $events );

		$tab         = new Upgrade_Tab( $state );
		$should_show = $tab->should_show();

		$this->assertTrue( $should_show );
	}

	/**
	 * It should not show if migration not required on second check
	 *
	 * @test
	 */
	public function should_not_show_if_migration_is_not_required_on_second_check() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_NOT_REQUIRED );
		$state = new State( new Events() );

		$tab         = new Upgrade_Tab( $state );
		$should_show = $tab->should_show();

		$this->assertFalse( $should_show );
	}

	/**
	 * It should not show if migration complete and the tab duration has expired.
	 *
	 * @test
	 */
	public function should_not_show_if_migration_complete_and_expired() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_COMPLETE );
		$state = new State( new Events() );

		// Should show if 30 days + 1 minute (just over 30 days) have passed.
		$days_in_seconds = ( 30 * 24 * 60 * 60 ) - 60;
		$state->set( 'complete_timestamp', time() - $days_in_seconds );
		$state->save();

		$tab         = new Upgrade_Tab( $state );
		$should_show = $tab->should_show();
		$this->assertTrue( $should_show );

		// Should not hide if 30 days - 1 minute (just under 30 days) have passed.
		$days_in_seconds = ( 30 * 24 * 60 * 60 ) + 60;
		$state->set( 'complete_timestamp', time() - $days_in_seconds );
		$state->save();

		$tab         = new Upgrade_Tab( $state );
		$should_show = $tab->should_show();

		$this->assertFalse( $should_show );
	}
}