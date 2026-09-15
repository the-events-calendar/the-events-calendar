<?php

namespace Tribe\Events\Test\Traits\CT1;

use TEC\Events\Custom_Tables\V1\Activation;
use TEC\Events\Custom_Tables\V1\Migration\Provider;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Event as Event_Model;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Occurrence as Occurrence_Model;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use TEC\Events\Custom_Tables\V1\Tables\Provider as Tables;
use TEC\Events\Recurrence\Date_Rules;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;
use Tribe__Events__Main as TEC;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;

trait CT1_Fixtures {
	/**
	 * Utility to generate reports with various criteria.
	 *
	 * @param int     $count           How many events to create.
	 * @param boolean $upcoming        Whether the event is in the future or past.
	 * @param string  $report_category The report category based on success/failure grouping.
	 * @param boolean $is_failure      Whether the event report should be flagged as a failure or success.
	 *
	 * @return array<Event_Report>
	 * @throws \Exception
	 */
	protected function given_number_single_event_reports( $count, $upcoming, $report_category, $is_failure ) {

		$timezone = new \DateTimeZone( 'Europe/Paris' );
		$utc      = new \DateTimeZone( 'UTC' );
		if ( $upcoming ) {
			$now = new \DateTimeImmutable( 'next week', $timezone );
		} else {
			$now = new \DateTimeImmutable( 'last week', $timezone );
		}
		$two_hours  = new \DateInterval( 'PT2H' );
		$event_args = [
			'meta_input' => [
				'_EventStartDate'    => $now->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDate'      => $now->add( $two_hours )->format( Dates::DBDATETIMEFORMAT ),
				'_EventStartDateUTC' => $now->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDateUTC'   => $now->setTimezone( $utc )->add( $two_hours )->format( Dates::DBDATETIMEFORMAT ),
				'_EventDuration'     => 7200,
				'_EventTimezone'     => $timezone->getName(),
			],
		];
		$reports    = [];
		for ( $i = 0; $i < $count; $i ++ ) {
			$post         = $this->given_a_non_migrated_single_event( $event_args );
			$event_report = new Event_Report( $post );
			if ( $is_failure ) {
				$event_report->migration_failed( $report_category );
			} else {
				$event_report->add_strategy( $report_category );
				$event_report->migration_success();
			}
			$reports[] = $event_report;
		}

		return $reports;
	}

	/**
	 * Reset the activation flags, and remove CT1 tables. We want to simulate no activation having been done yet.
	 */
	public function given_a_reset_activation() {
		global $wpdb;
		// Ditch our CT1 schema.
		tribe( Schema_Builder::class )->down();

		// Reset state in the db.
		$this->given_custom_tables_are_not_initialized();
		$state = tribe( State::class );
		$state->set( 'phase', null );
		$state->save();

		// Sanity check.
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertNotContains( OccurrencesSchema::table_name( true ), $tables );
		$this->assertNotContains( EventsSchema::table_name( true ), $tables );
		tec_timed_option()->delete( Activation::ACTIVATION_TRANSIENT );
	}

	/**
	 * @return \WP_Post
	 */
	private function given_a_non_migrated_single_event( $override_event_args = [] ): \WP_Post {
		// Create an Event.
		$timezone   = new \DateTimeZone( 'Europe/Paris' );
		$utc        = new \DateTimeZone( 'UTC' );
		$now        = new \DateTimeImmutable( 'now', $timezone );
		$two_hours  = new \DateInterval( 'PT2H' );
		$event_args = [
			'post_type'   => TEC::POSTTYPE,
			'meta_input'  => [
				'_EventStartDate'    => $now->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDate'      => $now->add( $two_hours )->format( Dates::DBDATETIMEFORMAT ),
				'_EventStartDateUTC' => $now->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ),
				'_EventEndDateUTC'   => $now->setTimezone( $utc )->add( $two_hours )->format( Dates::DBDATETIMEFORMAT ),
				'_EventDuration'     => 7200,
				'_EventTimezone'     => $timezone->getName(),
				'_EventTimezoneAbbr' => Timezones::abbr( $now, $timezone ),
			],
			'post_status' => 'publish',
		];

		$post_id    = ( new \WP_UnitTest_Factory_For_Post() )->create( array_merge( $event_args, $override_event_args ) );

		// Make sure no models are present in the custom tables for it.
		Occurrence_Model::where( 'post_id', '=', $post_id )
		                ->delete();
		Event_Model::where( 'post_id', '=', $post_id )
		           ->delete();
		$this->assertNull( Event_Model::find( $post_id, 'post_id' ) );
		$this->assertNull( Occurrence_Model::find( $post_id, 'post_id' ) );
		// Just in case, remove any recurrence meta there might be.
		delete_post_meta( $post_id, '_tribe_blocks_recurrence_rules' );
		delete_post_meta( $post_id, '_tribe_blocks_recurrence_exclusions' );
		delete_post_meta( $post_id, '_tribe_blocks_recurrence_description' );

		return get_post( $post_id );
	}

	/**
	 * Creates a non-migrated legacy Event whose `_EventRecurrence` meta is a list of
	 * explicit dates — the shape `Date_Rules::to_meta()` writes and
	 * `Date_Rules::is_dates_only_meta()` accepts.
	 *
	 * @param int                 $extra_dates         How many additional dates, one week apart, to author.
	 * @param array<string,mixed> $override_event_args Overrides for the Event post creation arguments.
	 *
	 * @return \WP_Post The legacy Event post.
	 */
	private function given_a_non_migrated_dates_only_event( int $extra_dates = 2, array $override_event_args = [] ): \WP_Post {
		$post = $this->given_a_non_migrated_single_event( $override_event_args );

		$timezone    = new \DateTimeZone( (string) get_post_meta( $post->ID, '_EventTimezone', true ) );
		$utc         = new \DateTimeZone( 'UTC' );
		$event_start = new \DateTimeImmutable( (string) get_post_meta( $post->ID, '_EventStartDate', true ), $timezone );
		$event_end   = new \DateTimeImmutable( (string) get_post_meta( $post->ID, '_EventEndDate', true ), $timezone );

		// The legacy date-rule shape stores minute precision: floor the Event dates to match.
		$event_start = $event_start->setTime( (int) $event_start->format( 'H' ), (int) $event_start->format( 'i' ) );
		$event_end   = $event_end->setTime( (int) $event_end->format( 'H' ), (int) $event_end->format( 'i' ) );
		update_post_meta( $post->ID, '_EventStartDate', $event_start->format( Dates::DBDATETIMEFORMAT ) );
		update_post_meta( $post->ID, '_EventEndDate', $event_end->format( Dates::DBDATETIMEFORMAT ) );
		update_post_meta( $post->ID, '_EventStartDateUTC', $event_start->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ) );
		update_post_meta( $post->ID, '_EventEndDateUTC', $event_end->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ) );

		$periods = [];
		for ( $i = 1; $i <= $extra_dates; $i ++ ) {
			$periods[] = [
				'start' => $event_start->add( new \DateInterval( "P{$i}W" ) ),
				'end'   => $event_end->add( new \DateInterval( "P{$i}W" ) ),
			];
		}

		update_post_meta( $post->ID, '_EventRecurrence', Date_Rules::to_meta( $periods, $event_start, $event_end ) );

		return $post;
	}

	/**
	 * Creates a non-migrated dates-only legacy Event along with fabricated legacy child
	 * posts, one per additional date, mimicking the pre-6.0 child-post structure.
	 *
	 * @param int $extra_dates How many additional dates (and children) to author.
	 *
	 * @return array{parent: \WP_Post, children: array<int,int>} The parent post and the child post IDs.
	 */
	private function given_a_non_migrated_dates_only_event_with_legacy_children( int $extra_dates = 2 ): array {
		$parent = $this->given_a_non_migrated_dates_only_event( $extra_dates );

		$timezone = new \DateTimeZone( (string) get_post_meta( $parent->ID, '_EventTimezone', true ) );
		$utc      = new \DateTimeZone( 'UTC' );
		$start    = new \DateTimeImmutable( (string) get_post_meta( $parent->ID, '_EventStartDate', true ), $timezone );
		$end      = new \DateTimeImmutable( (string) get_post_meta( $parent->ID, '_EventEndDate', true ), $timezone );

		$children = [];
		for ( $i = 1; $i <= $extra_dates; $i ++ ) {
			$week        = new \DateInterval( "P{$i}W" );
			$child_start = $start->add( $week );
			$child_end   = $end->add( $week );
			$children[]  = wp_insert_post(
				[
					'post_type'   => TEC::POSTTYPE,
					'post_title'  => $parent->post_title,
					'post_status' => 'publish',
					'post_parent' => $parent->ID,
					'meta_input'  => [
						'_EventStartDate'    => $child_start->format( Dates::DBDATETIMEFORMAT ),
						'_EventEndDate'      => $child_end->format( Dates::DBDATETIMEFORMAT ),
						'_EventStartDateUTC' => $child_start->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ),
						'_EventEndDateUTC'   => $child_end->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ),
						'_EventDuration'     => $child_end->getTimestamp() - $child_start->getTimestamp(),
						'_EventTimezone'     => $timezone->getName(),
					],
				]
			);
		}

		return [
			'parent'   => $parent,
			'children' => $children,
		];
	}

	/**
	 * Creates a non-migrated legacy Event whose `_EventRecurrence` meta contains a
	 * recurrence rule pattern: NOT a dates-only one.
	 *
	 * @return \WP_Post The legacy Event post.
	 */
	private function given_a_non_migrated_rule_based_event(): \WP_Post {
		$post = $this->given_a_non_migrated_single_event();

		$event_start = (string) get_post_meta( $post->ID, '_EventStartDate', true );
		$event_end   = (string) get_post_meta( $post->ID, '_EventEndDate', true );

		update_post_meta(
			$post->ID,
			'_EventRecurrence',
			[
				'rules'       => [
					[
						'type'           => 'Custom',
						'custom'         => [
							'interval'  => 1,
							'type'      => 'Week',
							'week'      => [ 'day' => [ 1 ] ],
							'same-time' => 'yes',
						],
						'end-type'       => 'After',
						'end-count'      => 5,
						'EventStartDate' => $event_start,
						'EventEndDate'   => $event_end,
					],
				],
				'exclusions'  => [],
				'description' => null,
			]
		);

		return $post;
	}

	private function given_the_current_migration_phase_is( $phase = null ) {
		/*
		 * The suite truncates the options table between tests with raw SQL: the object
		 * cache can hold a value for a row that no longer exists, making `update_option`
		 * run an UPDATE against a missing row and silently write nothing. Purge the
		 * caches so the write goes through the insert path when needed.
		 */
		wp_cache_delete( State::STATE_OPTION_KEY, 'options' );
		wp_cache_delete( 'alloptions', 'options' );
		wp_cache_delete( 'notoptions', 'options' );

		$state          = get_option( State::STATE_OPTION_KEY, [] );
		$state['phase'] = $phase;
		update_option( State::STATE_OPTION_KEY, $state );
		tribe( State::class )->set( 'phase', $phase );
	}

	private function given_a_site_with_no_events() {
		global $wpdb;
		// Delete all Event post meta.
		$wpdb->query(
			$wpdb->prepare(
				"delete from $wpdb->postmeta
				where post_id in (select ID from $wpdb->posts where post_type = %s)",
				TEC::POSTTYPE
			)
		);
		// Delete all Event posts.
		$wpdb->query(
			$wpdb->prepare(
				"delete from $wpdb->posts where post_type = %s",
				TEC::POSTTYPE
			)
		);
	}

	private function assert_custom_tables_exist() {
		$schema_builder = tribe()->make( Schema_Builder::class );
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertTrue( $table_schema->exists() );
		}
	}

	private function assert_custom_tables_not_exist(){
		$schema_builder = tribe()->make( Schema_Builder::class );
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertFalse( $table_schema->exists() );
		}
	}

	private function given_the_custom_tables_do_not_exist() {
		$schema_builder = tribe()->make( Schema_Builder::class );
		$schema_builder->down();
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertFalse( $table_schema->exists() );
		}
	}

	private function given_the_custom_tables_do_exist() {
		$schema_builder = tribe()->make( Schema_Builder::class );
		$schema_builder->up();
		$this->assert_custom_tables_exist();
	}

	private function given_the_initialization_transient_expired() {
		delete_transient( Activation::ACTIVATION_TRANSIENT );
	}

	private function given_a_migrated_single_event( $args = [] ) {
		$post = $this->given_a_non_migrated_single_event( $args );
		Event::upsert( [ 'post_id' ], Event::data_from_post( $post ) );
		$event = Event::find( $post->ID, 'post_id' );
		$this->assertInstanceOf( Event::class, $event );
		$event->occurrences()->save_occurrences();
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );

		return $post;
	}

	private function given_action_scheduler_is_loaded() {
		if ( function_exists( 'as_enqueue_async_action' ) ) {
			return;
		}

		tribe( Provider::class )->load_action_scheduler_late();
	}

	private function given_custom_tables_are_not_initialized() {
		delete_transient( Activation::ACTIVATION_TRANSIENT );
	}
}