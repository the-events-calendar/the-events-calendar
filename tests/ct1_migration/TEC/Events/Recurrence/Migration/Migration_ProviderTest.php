<?php

namespace TEC\Events\Recurrence\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\Process_Worker;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Null_Migration_Strategy;
use TEC\Events\Recurrence\Engine_Provider;
use TEC\Events\Recurrence\Controller;
use TEC\Events\Recurrence\Updates\Freeze_Guard;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe\Events\Test\Traits\CT1\CT1_Test_Utils;

class Migration_ProviderTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;
	use CT1_Test_Utils;
	use With_Uopz;

	/**
	 * The strategy a pre-registered filter provides; static: hook-capture callbacks in
	 * these suites must not be closures (leaked closures poison later tests).
	 *
	 * @var Null_Migration_Strategy|null
	 */
	public static $preset_strategy;

	/**
	 * Provides the preset strategy to the migration strategy filter.
	 *
	 * @param mixed $strategy The current strategy.
	 *
	 * @return mixed The preset strategy, or the input value when none is set.
	 */
	public static function provide_preset_strategy( $strategy ) {
		return self::$preset_strategy ?? $strategy;
	}

	/**
	 * @before
	 */
	public function set_migration_phase(): void {
		// Earlier suite classes drop the custom tables without restoring them.
		$this->given_the_custom_tables_do_exist();
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		self::$preset_strategy = null;
		// The suite bootstrap registered the provider once; make sure the loader is live.
		tribe( Migration_Provider::class )->register();
	}

	/**
	 * @after
	 */
	public function reset_preset_strategy(): void {
		self::$preset_strategy = null;
		remove_filter( 'tec_events_custom_tables_v1_migration_strategy', [ self::class, 'provide_preset_strategy' ], 5 );
	}

	/**
	 * It should return null for a plain single event
	 *
	 * @test
	 */
	public function should_return_null_for_a_plain_single_event(): void {
		$post = $this->given_a_non_migrated_single_event();

		$strategy = apply_filters( 'tec_events_custom_tables_v1_migration_strategy', null, $post->ID, false );

		$this->assertNull( $strategy );

		// Through the worker, the default Single Event strategy applies.
		$events       = new Events();
		$worker       = new Process_Worker( $events, new State( $events ) );
		$event_report = $worker->migrate_event( $post->ID, false );

		$this->assertEquals( 'success', $event_report->status, (string) $event_report->error );
		$this->assertEquals( [ 'tec-single-event-strategy' ], $event_report->strategies_applied );
	}

	/**
	 * It should provide the date rules strategy for a dates only event
	 *
	 * @test
	 */
	public function should_provide_the_date_rules_strategy_for_a_dates_only_event(): void {
		$post = $this->given_a_non_migrated_dates_only_event( 2 );

		$strategy = apply_filters( 'tec_events_custom_tables_v1_migration_strategy', null, $post->ID, false );

		$this->assertInstanceOf( Date_Rules_Migration_Strategy::class, $strategy );
	}

	/**
	 * It should respect an already set strategy
	 *
	 * @test
	 */
	public function should_respect_an_already_set_strategy(): void {
		$post = $this->given_a_non_migrated_dates_only_event( 2 );

		self::$preset_strategy = new Null_Migration_Strategy();
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', [ self::class, 'provide_preset_strategy' ], 5 );

		$strategy = apply_filters( 'tec_events_custom_tables_v1_migration_strategy', null, $post->ID, false );

		$this->assertSame( self::$preset_strategy, $strategy );
	}

	/**
	 * It should still fail rule based events with the ecp message
	 *
	 * @test
	 */
	public function should_still_fail_rule_based_events_with_the_ecp_message(): void {
		// A dry run is a preview: the worker requires the phase to match the mode.
		$this->given_the_current_migration_phase_is( State::PHASE_PREVIEW_IN_PROGRESS );
		$post = $this->given_a_non_migrated_rule_based_event();

		$events       = new Events();
		$worker       = new Process_Worker( $events, new State( $events ) );
		$event_report = $worker->migrate_event( $post->ID, true );

		$this->assertEquals( 'failure', $event_report->status );
		$this->assertStringContainsString(
			'Install and activate the latest version of Events Calendar PRO',
			(string) $event_report->error
		);
	}

	/**
	 * It should register the engine when providing the strategy
	 *
	 * @test
	 */
	public function should_register_the_engine_when_providing_the_strategy(): void {
		$post   = $this->given_a_non_migrated_dates_only_event( 2 );
		$engine = tribe( Engine_Provider::class );
		$engine->unregister();

		$this->assertFalse(
			has_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $engine, 'derive_dates_rset_from_meta' ] )
		);

		apply_filters( 'tec_events_custom_tables_v1_migration_strategy', null, $post->ID, false );

		$this->assertNotFalse(
			has_filter( 'tec_events_custom_tables_v1_event_data_from_post', [ $engine, 'derive_dates_rset_from_meta' ] )
		);
		$this->assertEquals(
			9,
			has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] )
		);
	}

	/**
	 * It should register the engine at boot during a migration phase
	 *
	 * @test
	 */
	public function should_register_the_engine_at_boot_during_a_migration_phase(): void {
		$engine = tribe( Engine_Provider::class );
		$engine->unregister();

		tribe( Migration_Provider::class )->register();

		$this->assertEquals(
			9,
			has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] )
		);
	}
	public function phases_and_owners(): array {
		$cases = [];
		foreach ( [ State::PHASE_PREVIEW_IN_PROGRESS, State::PHASE_MIGRATION_IN_PROGRESS ] as $phase ) {
			foreach ( [ 'free', 'released-pro', 'kill-switch' ] as $owner ) {
				$cases[ $phase . '-' . $owner ] = [ $phase, $owner ];
			}
		}
		return $cases;
	}

	/**
	 * @test
	 * @dataProvider phases_and_owners
	 */
	public function should_register_only_permitted_storage_before_full_activation( string $phase, string $owner ): void {
		$engine = tribe( Engine_Provider::class );
		$engine->unregister();
		$this->given_the_current_migration_phase_is( $phase );
		$this->set_class_fn_return( Controller::class, 'is_incompatible_pro_active', 'released-pro' === $owner );
		$previous = getenv( Controller::DISABLED );
		putenv( Controller::DISABLED . '=' . ( 'kill-switch' === $owner ? '1' : '' ) );
		try {
			$provider = tribe( Migration_Provider::class );
			$provider->register();
			$post = $this->given_a_non_migrated_dates_only_event( 2 );
			$strategy = $provider->provide_strategy( null, $post->ID, State::PHASE_PREVIEW_IN_PROGRESS === $phase );
			if ( 'free' === $owner ) {
				$this->assertInstanceOf( Date_Rules_Migration_Strategy::class, $strategy );
				$this->assertSame( 9, has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] ) );
			} else {
				$this->assertNull( $strategy );
				$this->assertFalse( has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] ) );
			}
			$this->assertFalse( has_filter( 'query', [ tribe( \TEC\Events\Custom_Tables\V1\WP_Query\Provisional\Provider::class ), 'hydrate_provisional_post' ] ) );
			$this->assertFalse( has_filter( 'update_post_metadata', [ tribe( Freeze_Guard::class ), 'refuse_update' ] ) );
			$this->assertFalse( tribe()->getVar( 'tec_events_recurrence_fully_activated', false ) );
		} finally {
			putenv( Controller::DISABLED . ( false === $previous ? '' : '=' . $previous ) );
			$engine->unregister_storage();
			\TEC\Events\Custom_Tables\V1\Models\Model::reset_extensions();
		}
	}

	/** @test */
	public function should_wait_for_companion_boot_before_registering_storage(): void {
		$engine = tribe( Engine_Provider::class );
		$engine->unregister();
		$provider = tribe( Migration_Provider::class );
		$this->set_fn_return( 'did_action', 0 );
		$provider->register();
		$this->assertFalse( $provider->ensure_engine() );
		$this->assertFalse( has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] ) );
		$this->assertSame( 5, has_action( 'tribe_plugins_loaded', [ $provider, 'register_migration_storage' ] ) );
		// Released Pro becomes visible later in the same boot. Re-evaluate ownership.
		$this->set_fn_return( 'did_action', 1 );
		$this->set_class_fn_return( Controller::class, 'is_incompatible_pro_active', true );
		$provider->register_migration_storage();
		$this->assertFalse( has_filter( 'tec_events_custom_tables_v1_occurrences_generator', [ $engine, 'get_dates_generator' ] ) );
		$provider->unregister();
		$this->assertFalse( has_action( 'tribe_plugins_loaded', [ $provider, 'register_migration_storage' ] ) );
	}

}
