<?php

namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Tables\Events as Events_Schema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as Occurrences_Schema;
use TEC\Events\Custom_Tables\V1\Tables\Provider as Tables;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class Activation_StateTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;
	use With_Uopz;

	/**
	 * @before
	 */
	public function register_tables_schema(): void {
		tribe()->register( Tables::class );
	}

	/**
	 * @after each test make sure the custom tables will be there for the following ones.
	 */
	public function recreate_custom_tables(): void {
		$events_updated = ( new Events_Schema )->update();
		if ( ! $events_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
		$occurrences_updated = ( new Occurrences_Schema() )->update();
		if ( ! $occurrences_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
	}

	/**
	 * Should not init schema with non migrated events.
	 *
	 * @test
	 */
	public function should_not_init_schema_with_non_migrated_events(): void {
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
	public function should_init_schema_with_no_events(): void {
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
	public function should_activate_schema(): void {
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
	public function should_set_state_correctly_when_initialising_with_already_existing_tables(): void {
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
	public function should_correctly_include_migrated_health_check(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_COMPLETE );

		$support_data = Activation::filter_include_migration_health_check_info( [] );

		$this->assertArrayHasKey( 'Custom Tables Health Check', $support_data, 'It should contain the custom tables health key' );
		$this->assertEquals( $support_data['Custom Tables Health Check'], 'Good!', 'Migration health should be good.' );
	}

	/**
	 * @test
	 */
	public function should_correctly_include_migrated_status_support_data(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_COMPLETE );

		$support_data = Activation::filter_include_migration_in_system_info( [] );

		$this->assertArrayHasKey( 'Custom Tables Migration Status', $support_data, 'It should contain the migration status key' );
		$this->assertEquals( $support_data['Custom Tables Migration Status'], 'Completed', 'Migration status should be Complete' );
	}

	/**
	 * @test
	 */
	public function should_correctly_include_incomplete_status_support_data(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_IN_PROGRESS );

		$support_data = Activation::filter_include_migration_in_system_info( [] );

		$this->assertArrayHasKey( 'Custom Tables Migration Status', $support_data, 'It should contain the migration status key' );
		$this->assertEquals( $support_data['Custom Tables Migration Status'], 'Incomplete', 'Migration status should be Complete' );
	}

	/**
	 * @test
	 */
	public function should_correctly_include_migration_not_required_status_support_data(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );

		$support_data = Activation::filter_include_migration_in_system_info( [] );

		$this->assertArrayHasKey( 'Custom Tables Migration Status', $support_data, 'It should contain the migration status key' );
		$this->assertEquals( $support_data['Custom Tables Migration Status'], 'Not Required', 'Migration status should be Complete' );
	}

	/**
	 * It should not rerun the activation early when using object cache
	 *
	 * @test
	 */
	public function should_not_rerun_the_activation_early_when_using_object_cache(): void {
		// We're using real object cache.
		$this->set_fn_return( 'wp_using_ext_object_cache', true );
		// The cached value is set to 1 hour ago.
		$last_run_time = time() - HOUR_IN_SECONDS;
		wp_cache_set( Activation::ACTIVATION_TRANSIENT, $last_run_time );
		// The transient is not set.
		delete_transient( Activation::ACTIVATION_TRANSIENT );
		// The Schema Builder up function should not be called.
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', function () {
			$this->fail( 'The Schema Builder up function should not be called' );
		} );

		// Activate.
		Activation::init();

		$this->assertEquals( $last_run_time, wp_cache_get( Activation::ACTIVATION_TRANSIENT ) );
		$this->assertEquals( '', get_transient( Activation::ACTIVATION_TRANSIENT ) );
	}

	/**
	 * It should not rerun the activation early when using transients
	 *
	 * @test
	 */
	public function should_not_rerun_the_activation_early_when_using_transients(): void {
		// We're not using real object cache.
		$this->set_fn_return( 'wp_using_ext_object_cache', false );
		// The transient value is set to 1 hour ago.
		$last_run_time = time() - HOUR_IN_SECONDS;
		set_transient( Activation::ACTIVATION_TRANSIENT, $last_run_time, DAY_IN_SECONDS );
		// The Schema Builder up function should not be called.
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', function () {
			$this->fail( 'The Schema Builder up function should not be called' );
		} );

		// Activate.
		Activation::init();

		$this->assertEquals( false, wp_cache_get( Activation::ACTIVATION_TRANSIENT ) );
		$this->assertEquals( $last_run_time, get_transient( Activation::ACTIVATION_TRANSIENT ) );
	}

	/**
	 * It should use cached value when using object cache over transient
	 *
	 * @test
	 * @skip
	 */
	public function should_use_cached_value_when_using_object_cache_over_transient(): void {
		// We're using real object cache.
		$this->set_fn_return( 'wp_using_ext_object_cache', true );
		// The cached value is set to 26 hours ago.
		$last_run_time = time() - 26 * HOUR_IN_SECONDS;
		wp_cache_set( Activation::ACTIVATION_TRANSIENT, $last_run_time );
		// The transient value is set to 1 hour ago.
		set_transient( Activation::ACTIVATION_TRANSIENT, time() - HOUR_IN_SECONDS, DAY_IN_SECONDS );
		// The Schema Builder up function should be called.
		$calls = 0;
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', static function () use ( &$calls ) {
			$calls ++;
		} );

		// Activate.
		Activation::init();

		$this->assertEquals( 1, $calls );
		$this->assertEqualsWithDelta( time(), wp_cache_get( Activation::ACTIVATION_TRANSIENT ), 5 );
		$this->assertEquals( false, get_transient( Activation::ACTIVATION_TRANSIENT ) );
	}

	/**
	 * It should use transient value over cache when using transients
	 *
	 * @test
	 */
	public function should_use_transient_value_over_cache_when_using_transients(): void {
		// We're not using real object cache.
		$this->set_fn_return( 'wp_using_ext_object_cache', false );
		// The transient value is set to 26 hours sago.
		$last_run_time = time() - 26 * HOUR_IN_SECONDS;
		set_transient( Activation::ACTIVATION_TRANSIENT, $last_run_time, DAY_IN_SECONDS );
		// The cached value is set to 1 hour ago.
		wp_cache_set( Activation::ACTIVATION_TRANSIENT, time() - HOUR_IN_SECONDS );
		// The Schema Builder up function should be called.
		$calls = 0;
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', static function () use ( &$calls ) {
			$calls ++;
		} );

		// Activate.
		Activation::init();

		$this->assertEquals( 1, $calls );
		$this->assertEquals( false, wp_cache_get( Activation::ACTIVATION_TRANSIENT ) );
		$this->assertEqualsWithDelta( time(), get_transient( Activation::ACTIVATION_TRANSIENT ), 5 );
	}

	/**
	 * It should correctly interact with Main clear state when using object cache
	 *
	 * @test
	 */
	public function should_correctly_interact_with_main_clear_state_when_using_object_cache(): void {
		// We're using real object cache.
		$this->set_fn_return( 'wp_using_ext_object_cache', true );
		// The cached value is set to 1 hour ago.
		$last_run_time = time() - HOUR_IN_SECONDS;
		wp_cache_set( Activation::ACTIVATION_TRANSIENT, $last_run_time );
		// The transient value is set to 1 hour ago.
		set_transient( Activation::ACTIVATION_TRANSIENT, time() - HOUR_IN_SECONDS, DAY_IN_SECONDS );
		// The Schema Builder up function should be called.
		$calls = 0;
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', static function () use ( &$calls ) {
			$calls ++;
		} );

		// Clear the state from Main, as it would happen on activation or deactivation.
		TEC::clear_ct1_activation_state();
		// Activate.
		Activation::init();

		$this->assertEquals( 1, $calls );
		$this->assertEqualsWithDelta( time(), wp_cache_get( Activation::ACTIVATION_TRANSIENT ), 5 );
		$this->assertEquals( false, get_transient( Activation::ACTIVATION_TRANSIENT ) );
	}

	/**
	 * It should correctly interact with Main clear state when using transients
	 *
	 * @test
	 */
	public function should_correctly_interact_with_main_clear_state_when_using_transients(): void {
		// We're not using real object cache.
		$this->set_fn_return( 'wp_using_ext_object_cache', false );
		// The transient value is set to 1 hour ago.
		$last_run_time = time() - HOUR_IN_SECONDS;
		set_transient( Activation::ACTIVATION_TRANSIENT, $last_run_time, DAY_IN_SECONDS );
		// The cached value is set to 1 hour ago.
		wp_cache_set( Activation::ACTIVATION_TRANSIENT, time() - HOUR_IN_SECONDS );
		// The Schema Builder up function should be called.
		$calls = 0;
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', static function () use ( &$calls ) {
			$calls ++;
		} );

		// Clear the state from Main, as it would happen on activation or deactivation.
		TEC::clear_ct1_activation_state();
		// Activate.
		Activation::init();

		$this->assertEquals( 1, $calls );
		$this->assertEquals( false, wp_cache_get( Activation::ACTIVATION_TRANSIENT ) );
		$this->assertEqualsWithDelta( time(), get_transient( Activation::ACTIVATION_TRANSIENT ), 5 );
	}

	/**
	 * It should recreate the tables if removed after activation
	 *
	 * @test
	 */
	public function should_recreate_the_tables_if_removed_after_activation(): void {
		// We're not using real object cache.
		$this->set_fn_return( 'wp_using_ext_object_cache', false );
		// The transient value is set to 26 hours ago.
		$last_run_time = time() - 26 * HOUR_IN_SECONDS;
		set_transient( Activation::ACTIVATION_TRANSIENT, $last_run_time, DAY_IN_SECONDS );
		// The Migration state is set to completed.
		tribe( State::class )->set( 'phase', State::PHASE_MIGRATION_COMPLETE );
		// But, in the meantime, the tables have been removed.
		global $wpdb;
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		$wpdb->query( "DROP TABLE IF EXISTS " . Events_Schema::table_name( true ) );
		$wpdb->query( "DROP TABLE IF EXISTS " . Occurrences_Schema::table_name( true ) );
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
		// The Schema Builder up function should be called.
		$calls = 0;
		add_action( 'tec_events_custom_tables_v1_schema_builder_after_up', static function () use ( &$calls ) {
			$calls ++;
		} );

		// Activate.
		Activation::init();

		$this->assertEquals( 1, $calls );
		$this->assertEquals( false, wp_cache_get( Activation::ACTIVATION_TRANSIENT ) );
		$this->assertEqualsWithDelta( time(), get_transient( Activation::ACTIVATION_TRANSIENT ), 5 );
		// The tables should be there.
		$this->assertEquals(
			Events_Schema::table_name( true ),
			$wpdb->get_var( "SHOW TABLES LIKE '" . Events_Schema::table_name( true ) . "'" )
		);
		$this->assertEquals(
			Occurrences_Schema::table_name( true ),
			$wpdb->get_var( "SHOW TABLES LIKE '" . Occurrences_Schema::table_name( true ) . "'" )
		);
	}
}