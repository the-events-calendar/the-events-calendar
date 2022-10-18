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

	private function given_the_current_migration_phase_is( $phase = null ) {
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

	private function given_a_migrated_single_event(){
		$post = $this->given_a_non_migrated_single_event();
		Event::upsert( [ 'post_id' ], Event::data_from_post( $post ) );
		$event = Event::find( $post->ID, 'post_id' );
		$this->assertInstanceOf( Event::class, $event );
		$event->occurrences()->save_occurrences();
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );

		return $post;
	}

	private function given_action_scheduler_is_loaded() {
		tribe( Provider::class )->load_action_scheduler_late();
	}

	private function given_custom_tables_are_not_initialized() {
		delete_transient( Activation::ACTIVATION_TRANSIENT );
	}
}