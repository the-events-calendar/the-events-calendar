<?php

namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\Tables\Provider as Tables;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Health_CheckTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * @before
	 */
	public function db_state(): void {
		tribe()->register( Tables::class );
		// These tests all start with an unmigrated state, ensure the state does not bleed over / mix.
		$this->given_a_reset_activation();
	}

	/**
	 * @after each test make sure the custom tables will be there for the following ones.
	 */
	public function recreate_custom_tables(): void {
		$events_updated = ( new Events )->update();
		if ( ! $events_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
		$occurrences_updated = ( new Occurrences )->update();
		if ( ! $occurrences_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
	}

	/**
	 * Should not init schema with non migrated events.
	 *
	 * @test
	 */
	public function should_report_healthy_pre_migration(): void {
		$this->given_a_non_migrated_single_event();

		$check = tribe( Health_Check::class );
		$this->assertTrue( $check->is_event_data_healthy() );
		$this->assertTrue( $check->is_occurrence_data_healthy() );
		$this->assertFalse( $check->is_event_table_missing() );
		$this->assertFalse( $check->is_occurrence_table_missing() );
	}

	/**
	 * @test
	 */
	public function should_report_healthy_with_no_posts(): void {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->posts}" );
		Activation::init();

		$check = tribe( Health_Check::class );
		$this->assertTrue( $check->is_event_data_healthy() );
		$this->assertTrue( $check->is_occurrence_data_healthy() );
		$this->assertFalse( $check->is_event_table_missing() );
		$this->assertFalse( $check->is_occurrence_table_missing() );
	}

	/**
	 * @test
	 */
	public function should_report_healthy_after_reset() {
		$this->given_a_non_migrated_single_event();
		Activation::init();
		$this->given_a_reset_activation();

		$check = tribe( Health_Check::class );
		$this->assertTrue( $check->is_event_data_healthy() );
		$this->assertTrue( $check->is_occurrence_data_healthy() );
		$this->assertFalse( $check->is_event_table_missing() );
		$this->assertFalse( $check->is_occurrence_table_missing() );
	}

	/**
	 * @test
	 */
	public function should_report_unhealthy_when_tables_missing() {
		global $wpdb;
		Activation::init();

		// Lost them somehow?
		$wpdb->query( "DROP TABLE `" . Occurrences::table_name() . "`" );
		$wpdb->query( "DROP TABLE `" . Events::table_name() . "`" );

		$check = tribe( Health_Check::class );
		$this->assertFalse( $check->is_event_data_healthy() );
		$this->assertFalse( $check->is_occurrence_data_healthy() );
		$this->assertTrue( $check->is_event_table_missing() );
		$this->assertTrue( $check->is_occurrence_table_missing() );
	}

	/**
	 * @test
	 */
	public function should_report_unhealthy_when_data_missing() {
		global $wpdb;
		Activation::init();
		$this->given_a_migrated_single_event();

		// Lost data somehow?
		$wpdb->query( "TRUNCATE TABLE `" . Occurrences::table_name() . "`" );
		$wpdb->query( "TRUNCATE TABLE `" . Events::table_name() . "`" );

		$check = tribe( Health_Check::class );
		$this->assertFalse( $check->is_event_data_healthy() );
		$this->assertFalse( $check->is_occurrence_data_healthy() );
		$this->assertFalse( $check->is_event_table_missing() );
		$this->assertFalse( $check->is_occurrence_table_missing() );
	}

}