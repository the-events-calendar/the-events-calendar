<?php
namespace TEC\Events;

use Tribe__Events__Main;

class MigrationsTest extends \Codeception\TestCase\WPTestCase {


	/**
	 *
	 * @test
	 */
	public function should_detect_version_change() {
		$updater         = Tribe__Events__Main::instance()->updater();
		$current_version = $updater->get_version_from_db();
		$past_version    = $this->get_past_version( $current_version );
		$future_version  = $this->get_future_version( $current_version );

		// We rely on this to validate when to run migrations.
		$this->assertFalse( $updater->is_version_in_db_less_than( $past_version ) );
		$this->assertFalse( $updater->is_version_in_db_less_than( $current_version ) );
		$this->assertTrue( $updater->is_version_in_db_less_than( $future_version ) );

		// Migration should run - but only once.
		$did_schedule_event = false;
		$allday_hook        = 'tec_events_migrate_all_day_eod_times'; // Hook for one of our first migrations in this setup.
		add_filter( 'pre_schedule_event', function ( $pre, $event, $wp_error ) use ( &$did_schedule_event, $allday_hook ) {
			if ( $event->hook === $allday_hook ) {
				$did_schedule_event = true;
			}

			return $pre;
		}, 10, 3 );

		// Won't do anything - this would have run already.
		tribe( Migrations\Provider::class )->schedule_pending_migrations( $updater->get_version_option_key() );
		// This should have already run during init hook.
		$this->assertFalse( $did_schedule_event );
		// Set way back in time, so that we can validate a migration check passed to a schedule vent hook.
		tribe_update_option( $updater->get_version_option_key(), '1.0.0' );
		tribe( Migrations\Provider::class )->schedule_pending_migrations( $updater->get_version_option_key() );
		$this->assertTrue( $did_schedule_event );
	}

	/**
	 * @test
	 */
	public function should_migrate_all_day_events() {
		tribe_update_option( 'multiDayCutoff', '02:00' );
		$wrong_date        = '2018-01-01 02:00:00';
		$expected_date     = '2018-01-01 00:00:00';
		$expected_end_date = '2018-01-01 23:59:59';
		$args              = [
			'start_date'   => $expected_date,
			'duration'     => ( 24 * HOUR_IN_SECONDS ) - 1,
			'timezone'     => 'Europe/Paris',
			'title'        => 'A test event',
			'_EventAllDay' => 'yes'
		];
		$event             = tribe_events()->set_args( $args )->create();
		update_post_meta( $event->ID, '_EventStartDate', $wrong_date );

		// Sanity check
		$this->assertEquals( $wrong_date, get_post_meta( $event->ID, '_EventStartDate', true ) );
		$this->assertTrue( tribe_is_truthy( get_post_meta( $event->ID, '_EventAllDay', true ) ) );
		$this->assertEquals( $expected_end_date, get_post_meta( $event->ID, '_EventEndDate', true ) );

		// Now validate migration.
		tribe( Migrations\Provider::class )->migrate_all_day_eod_times();
		wp_cache_flush();
		$this->assertEquals( $expected_date, get_post_meta( $event->ID, '_EventStartDate', true ) );
		$this->assertEquals( $expected_end_date, get_post_meta( $event->ID, '_EventEndDate', true ) );
		tribe_update_option( 'multiDayCutoff', '00:00' );
	}

	protected function get_future_version( $version ) {
		// Easy, just add one in front.
		return '1' . $version;
	}

	protected function get_past_version( $version ) {
		$exploded_versions = explode( '.', $version );
		$major_version     = array_shift( $exploded_versions );

		// Decrement and smush back together.
		return ( $major_version - 1 ) . '.' . implode( '.', $exploded_versions );
	}
}
