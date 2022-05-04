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
		global $wpdb;
		$this->given_a_non_migrated_single_event();
		// Reset state.
		$this->given_a_reset_activation();

		// Activate.
		Activation::init();

		// Validate expected state.
		$state = tribe( State::class );
		$this->assertNotEquals( State::PHASE_MIGRATION_COMPLETE, $state->get_phase() );
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertNotContains( OccurrencesSchema::table_name( true ), $tables );
		$this->assertNotContains( EventsSchema::table_name( true ), $tables );
	}

	/**
	 * Should skip to a completed migration state when a fresh install.
	 *
	 * @test
	 */
	public function should_init_schema_with_no_events() {
		global $wpdb;
		$this->given_a_site_with_no_events();
		// Reset state.
		$this->given_a_reset_activation();

		// Activate.
		Activation::init();

		// Validate expected state.
		$state = tribe( State::class );
		$this->assertEquals( State::PHASE_MIGRATION_NOT_REQUIRED, $state->get_phase() );
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertContains( OccurrencesSchema::table_name( true ), $tables );
		$this->assertContains( EventsSchema::table_name( true ), $tables );
	}

	/**
	 * We should be able to force activation of the tables.
	 * @test
	 */
	public function should_activate_schema() {
		global $wpdb;
		$this->given_a_site_with_no_events();
		// Reset state.
		$this->given_a_reset_activation();

		// Activate.
		Activation::activate();

		// Validate expected state.
		$state = tribe( State::class );
		$this->assertEquals( null, $state->get_phase() );
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertContains( OccurrencesSchema::table_name( true ), $tables );
		$this->assertContains( EventsSchema::table_name( true ), $tables );
	}
}