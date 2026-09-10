<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Tables\Events;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe\Tests\Traits\With_Uopz;

class Pro_HistoryTest extends WPTestCase {
	use With_Recurrence_Engine;
	use With_Uopz;

	/**
	 * Removes the Series relationships table another test class may have left, as real DDL.
	 *
	 * The test case rewrites DROP TABLE into a temporary-table drop: the filters are lifted
	 * for the statement. Run first thing in the test, the implicit commit of the DDL has
	 * nothing of the test to commit.
	 */
	private function drop_leaked_series_table(): void {
		global $wpdb;
		$table = Pro_History::series_relationships_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) !== $table ) {
			return;
		}

		remove_filter( 'query', [ $this, '_create_temporary_tables' ] );
		remove_filter( 'query', [ $this, '_drop_temporary_tables' ] );

		try {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		} finally {
			add_filter( 'query', [ $this, '_create_temporary_tables' ] );
			add_filter( 'query', [ $this, '_drop_temporary_tables' ] );
		}
	}

	/**
	 * @before
	 */
	public function clean_the_site(): void {
		global $wpdb;

		$this->drop_leaked_series_table();

		/*
		 * Rows other test classes committed (their DDL commits their transaction) read as
		 * Pro history: swept inside this test's transaction, so the sweep rolls back too.
		 */
		$events = Events::table_name( true );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DELETE FROM {$events} WHERE rset LIKE '%RRULE%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_type = %s", Pro_History::SERIES_POST_TYPE ) );
	}

	/**
	 * @before
	 * @after
	 */
	public function reset_history(): void {
		tribe( Pro_History::class )->reset();
		delete_option( Pro_History::SERIES_SCHEMA_OPTION );
		delete_option( 'pue_install_key_events_calendar_pro' );
		tribe_remove_option( 'pro-schema-version' );
		// The per-test rollback runs before this: drop the settings cache so the next read reloads the restored DB.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, [] );
		remove_all_filters( 'tec_events_recurrence_pro_history_detected' );
	}

	/**
	 * It should report no history on a clean site
	 *
	 * @test
	 */
	public function should_report_no_history_on_a_clean_site(): void {
		$this->assertFalse( tribe( Pro_History::class )->has_pro_history(), 'Detected through: ' . get_option( Pro_History::MEMO_OPTION, '' ) );
		$this->assertNotEmpty( get_transient( Pro_History::NEGATIVE_TRANSIENT ), 'A clean site memoizes the negative result.' );
		$this->assertEmpty( get_option( Pro_History::MEMO_OPTION ) );
	}

	/**
	 * It should detect the series schema option
	 *
	 * @test
	 */
	public function should_detect_the_series_schema_option(): void {
		update_option( Pro_History::SERIES_SCHEMA_OPTION, '1.0.0' );

		$this->assertTrue( tribe( Pro_History::class )->has_pro_history() );
		$this->assertEquals( 'series_schema_option', get_option( Pro_History::MEMO_OPTION ) );
	}

	/**
	 * It should detect the pro schema version tribe option
	 *
	 * @test
	 */
	public function should_detect_the_pro_schema_version_tribe_option(): void {
		tribe_update_option( 'pro-schema-version', '7.8.0' );

		$this->assertTrue( tribe( Pro_History::class )->has_pro_history() );
		$this->assertEquals( 'pro_schema_version', get_option( Pro_History::MEMO_OPTION ) );
	}

	/**
	 * It should detect the pro license option
	 *
	 * @test
	 */
	public function should_detect_the_pro_license_option(): void {
		update_option( 'pue_install_key_events_calendar_pro', 'abc123' );

		$this->assertTrue( tribe( Pro_History::class )->has_pro_history() );
		$this->assertEquals( 'pro_license', get_option( Pro_History::MEMO_OPTION ) );
	}

	/**
	 * It should detect series posts
	 *
	 * @test
	 */
	public function should_detect_series_posts(): void {
		$series_id = wp_insert_post(
			[
				'post_type'   => Pro_History::SERIES_POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => 'Orphan Series',
			]
		);
		$this->assertGreaterThan( 0, $series_id );

		$this->assertTrue( tribe( Pro_History::class )->has_pro_history() );
		$this->assertEquals( 'series_posts', get_option( Pro_History::MEMO_OPTION ) );
	}

	/**
	 * It should detect the series relationships table
	 *
	 * @test
	 */
	public function should_detect_the_series_relationships_table(): void {
		// The table check is a `SHOW TABLES`: the table itself is covered by the conversion tests.
		$this->set_class_fn_return( Pro_History::class, 'series_relationships_table_exists', true );

		$this->assertTrue( tribe( Pro_History::class )->has_pro_history() );
		$this->assertEquals( 'series_relationships_table', get_option( Pro_History::MEMO_OPTION ) );
	}

	/**
	 * It should detect rule based events
	 *
	 * @test
	 */
	public function should_detect_rule_based_events(): void {
		$post = $this->given_a_multi_date_event();
		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => "DTSTART;TZID=UTC:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=3" ] );

		$this->assertTrue( tribe( Pro_History::class )->has_pro_history() );
		$this->assertEquals( 'rule_based_events', get_option( Pro_History::MEMO_OPTION ) );
	}

	/**
	 * It should memoize a positive signal in the option
	 *
	 * @test
	 */
	public function should_memoize_a_positive_signal_in_the_option(): void {
		update_option( Pro_History::SERIES_SCHEMA_OPTION, '1.0.0' );
		$this->assertTrue( tribe( Pro_History::class )->has_pro_history() );

		// The source is gone, the memory stays: having used Pro is a fact.
		delete_option( Pro_History::SERIES_SCHEMA_OPTION );
		$history = new Pro_History();
		$this->assertTrue( $history->has_pro_history() );
	}

	/**
	 * It should mark history when rules are frozen
	 *
	 * @test
	 */
	public function should_mark_history_when_rules_are_frozen(): void {
		$this->assertFalse( tribe( Pro_History::class )->has_pro_history() );

		do_action( 'tec_events_recurrence_rules_frozen', 23 );

		$this->assertEquals( 'frozen_rules', get_option( Pro_History::MEMO_OPTION ) );
		$this->assertEmpty( get_transient( Pro_History::NEGATIVE_TRANSIENT ) );
		$this->assertTrue( ( new Pro_History() )->has_pro_history() );
	}

	/**
	 * It should honor the detection filter
	 *
	 * @test
	 */
	public function should_honor_the_detection_filter(): void {
		update_option( Pro_History::SERIES_SCHEMA_OPTION, '1.0.0' );
		add_filter( 'tec_events_recurrence_pro_history_detected', '__return_false' );

		$this->assertFalse( tribe( Pro_History::class )->has_pro_history() );
	}
}
