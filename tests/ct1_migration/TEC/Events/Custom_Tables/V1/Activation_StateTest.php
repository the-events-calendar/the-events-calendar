<?php

namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use TEC\Events\Custom_Tables\V1\Tables\Provider as Tables;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Activation_StateTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * @before
	 */
	public function register_tables_schema() {
		tribe()->register( Tables::class );
	}

	/**
	 * @after each test make sure the custom tables will be there for the following ones.
	 */
	public function recreate_custom_tables() {
		$events_updated = ( new EventsSchema )->update();
		if ( ! $events_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
		$occurrences_updated = ( new OccurrencesSchema() )->update();
		if ( ! $occurrences_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
	}

	/**
	 * Should not create schema.
	 *
	 * @test
	 */
	public function should_not_init_schema_with_events() {
		$this->given_a_non_migrated_single_event();
		// Reset state.
		$this->given_a_reset_activation();

		// Activate.
		Activation::init();

		// Validate expected state.
		$state = tribe( State::class );
		$this->assertNotEquals( State::PHASE_MIGRATION_COMPLETE, $state->get_phase() );
		$this->assert_custom_tables_not_exist();
	}

	/**
	 * Should skip to a completed migration state when a fresh install.
	 *
	 * @test
	 */
	public function should_init_schema_with_no_events() {
		$this->given_a_site_with_no_events();
		// Reset state.
		$this->given_a_reset_activation();

		// Activate.
		Activation::init();

		// Validate expected state.
		$state = tribe( State::class );
		$this->assertEquals( State::PHASE_MIGRATION_NOT_REQUIRED, $state->get_phase() );
		$this->assert_custom_tables_exist();
	}

	/**
	 * We should be able to force activation of the tables.
	 * @test
	 */
	public function should_activate_schema() {
		$this->given_a_site_with_no_events();
		// Reset state.
		$this->given_a_reset_activation();

		// Activate.
		Activation::activate();

		// Validate expected state.
		$state = tribe( State::class );
		$this->assertEquals( null, $state->get_phase() );
		$this->assert_custom_tables_exist();
	}

	/**
	 * It should set state correctly when initialising with already existing tables
	 *
	 * @test
	 */
	public function should_set_state_correctly_when_initialising_with_already_existing_tables() {
		$this->given_a_site_with_no_events();
		$this->given_the_custom_tables_do_exist();
		$this->given_the_current_migration_phase_is( null );
		$this->given_custom_tables_are_not_initialized();

		// Activate.
		Activation::init();

		$state = tribe( State::class );
		$this->assertEquals( State::PHASE_MIGRATION_NOT_REQUIRED, $state->get_phase() );
		$this->assert_custom_tables_exist();
	}

	/**
	 * @test
	 */
	public function should_correctly_include_migrated_status_support_data() {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_COMPLETE );

		$support_data = Activation::filter_include_migration_in_system_info( [] );

		$this->assertArrayHasKey( 'Custom Tables Migration Status', $support_data, 'It should contain the migration status key' );
		$this->assertEquals( $support_data['Custom Tables Migration Status'], 'Completed', 'Migration status should be Complete' );
	}

	/**
	 * @test
	 */
	public function should_correctly_include_incomplete_status_support_data() {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_IN_PROGRESS );

		$support_data = Activation::filter_include_migration_in_system_info( [] );

		$this->assertArrayHasKey( 'Custom Tables Migration Status', $support_data, 'It should contain the migration status key' );
		$this->assertEquals( $support_data['Custom Tables Migration Status'], 'Incomplete', 'Migration status should be Complete' );
	}

	/**
	 * @test
	 */
	public function should_correctly_include_migration_not_required_status_support_data() {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );

		$support_data = Activation::filter_include_migration_in_system_info( [] );

		$this->assertArrayHasKey( 'Custom Tables Migration Status', $support_data, 'It should contain the migration status key' );
		$this->assertEquals( $support_data['Custom Tables Migration Status'], 'Not Required', 'Migration status should be Complete' );
	}
}