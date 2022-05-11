<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Single_Event_Migration_Strategy;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe__Date_Utils as Dates;

class ReportsTest extends \CT1_Migration_Test_Case {

	use CT1_Fixtures;

	/**
	 * Our Event_Report needs to be JSON compatible for storage/frontend consumption.
	 *
	 * @test
	 */
	public function should_serialize_event_report() {
		// Setup some faux state
		$faux_post1 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$faux_post2 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$faux_post3 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();

		$strategy       = 'split';
		$past_microtime = microtime( true ) - 1;
		$event_report   = ( new Event_Report( $faux_post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', false )
			->add_created_event( $faux_post2, 1 )
			->add_created_event( $faux_post3, 1 )
			->add_strategy( $strategy );

		$object = json_decode( json_encode( $event_report ) );

		$this->assertEquals( $event_report->is_single, $object->is_single );
		$this->assertEquals( $event_report->has_tickets, $object->has_tickets );
		$this->assertEquals( $event_report->tickets_provider, $object->tickets_provider );
		$this->assertEquals( $event_report->status, $object->status );
		$this->assertContains( $strategy, $object->strategies_applied );
		$this->assertGreaterThan( $past_microtime, $object->start_timestamp );
		$this->assertEquals( $event_report->created_events, $object->created_events );
		$this->assertEquals( $faux_post1->ID, $object->source_event_post->ID );
		$this->assertEquals( $faux_post1->post_title, $object->source_event_post->post_title );
	}

	/**
	 * Our Site_Report needs to be JSON compatible for storage/frontend consumption.
	 *
	 * @test
	 */
	public function should_serialize_site_report() {
		$faux_post1 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$faux_post2 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$faux_post3 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();

		$strategy      = 'split';
		$event_report1 = ( new Event_Report( $faux_post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', false )
			->add_created_event( $faux_post2, 1 )
			->add_created_event( $faux_post3, 1 )
			->add_strategy( $strategy );
		$event_report2 = ( new Event_Report( $faux_post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', false )
			->add_created_event( $faux_post2, 1 )
			->add_created_event( $faux_post3, 1 )
			->add_strategy( $strategy );

		$data['estimated_time_in_seconds'] = 130;
		$data['estimated_time_in_minutes'] = round( $data['estimated_time_in_seconds'] / 60, 0 );
		$data['total_events']              = 1234;
		$data['total_events_migrated']     = 33;
		$data['total_events_in_progress']  = 55;
		$data['total_events_remaining']    = $data['total_events'] - $data['total_events_migrated'];
		$data['migration_phase']           = State::PHASE_MIGRATION_IN_PROGRESS;
		$data['is_completed']              = true;
		$data['has_changes']               = $data['total_events_migrated'] > 0;
		$data['is_running']                = false;
		$data['total_events_failed']       = 0;
		$data['has_errors']                = $data['total_events_failed'] > 0;
		$data['progress_percent']          = 0;
		$data['date_completed']            = null;

		$site_report = new Site_Report( $data );
		$object      = json_decode( json_encode( $site_report ) );

		$this->assertEquals( $data['estimated_time_in_seconds'], $object->estimated_time_in_seconds );
		$this->assertEquals( $data['estimated_time_in_minutes'], $object->estimated_time_in_minutes );
		$this->assertEquals( $data['total_events'], $object->total_events );
		$this->assertEquals( $data['has_errors'], $object->has_errors );
		$this->assertEquals( $data['total_events_migrated'], $object->total_events_migrated );
		$this->assertEquals( $data['total_events_in_progress'], $object->total_events_in_progress );
		$this->assertEquals( $data['total_events_remaining'], $object->total_events_remaining );
		$this->assertEquals( State::PHASE_MIGRATION_IN_PROGRESS, $object->migration_phase );
		$this->assertTrue( $object->has_changes );
		$this->assertTrue( $object->is_completed );
		$this->assertFalse( $object->is_running );
	}

	/**
	 * Should save a successful Event_Report with appropriate values.
	 *
	 * @test
	 */
	public function should_hydrate_event_report() {
		// Setup some faux state
		$post1 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$post2 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();

		// Success the report
		$event_report1 = ( new Event_Report( $post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', false )
			->add_created_event( $post2, 1 )
			->add_strategy( 'split' );
		$event_report1->migration_success();
		$fields = array_keys( $event_report1->get_data() );

		// Fetch in new object to see if hydration succeeded
		$event_report2 = new Event_Report( $post1 );

		// Assert all fields have hydrated properly
		$this->assertNotEmpty( $event_report2->get_data() );
		foreach ( $fields as $field ) {
			$this->assertEquals( $event_report1->$field, $event_report2->$field );
		}
		$this->assertEquals( $event_report1->get_data(), $event_report2->get_data() );
	}

	/**
	 * Should save a successful Event_Report with appropriate values.
	 *
	 * @test
	 */
	public function should_save_successful_event_report() {
		// Setup some faux state
		$post1 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$post2 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();

		// Success the report
		$event_report1 = ( new Event_Report( $post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', false )
			->add_created_event( $post2, 1 )
			->add_strategy( 'split' );
		$event_report1->migration_success();

		// Assert it is saved properly
		$meta  = get_post_meta( $post1->ID, Event_Report::META_KEY_REPORT_DATA, true );
		$phase = get_post_meta( $post1->ID, Event_Report::META_KEY_MIGRATION_PHASE, true );
		$this->assertEquals( Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS, $phase );
		$this->assertEquals( $event_report1->get_data(), $meta );
		$this->assertNotEmpty( $meta['end_timestamp'] );
	}

	/**
	 * Should save a failed Event_Report with appropriate data.
	 *
	 * @test
	 * @throws \Tribe__Repository__Usage_Error
	 */
	public function should_save_failed_event_report() {
		$text = tribe( String_Dictionary::class );
		// Setup some faux state
		$post1         = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$post2         = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$error_key     = 'canceled';
		$error_context = "Some Event";
		$some_error    = sprintf( $text->get( 'migration-error-k-' . $error_key ), $error_context );

		// Fail the report
		$event_report1 = ( new Event_Report( $post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', false )
			->add_created_event( $post2, 1 )
			->add_strategy( 'split' );
		$event_report1->migration_failed( $error_key, [ $error_context ] );
		$event_report = new Event_Report( $post1 );

		// Assert it is saved properly
		$meta  = get_post_meta( $post1->ID, Event_Report::META_KEY_REPORT_DATA, true );
		$phase = get_post_meta( $post1->ID, Event_Report::META_KEY_MIGRATION_PHASE, true );
		$this->assertEquals( Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE, $phase );
		$this->assertEquals( $event_report->get_data(), $meta );
		$this->assertEquals( $some_error, $meta['error'] );
		$this->assertEquals( $some_error, $event_report->error );
		$this->assertNotEmpty( $meta['end_timestamp'] );
	}

	/**
	 * Should build the Site_Report based on existing Event_Reports saved and other required values.
	 *
	 * @test
	 */
	public function should_build_site_report() {
		// Setup some faux state
		$post1         = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$post2         = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$post3         = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$post4         = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$event_report1 = ( new Event_Report( $post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', false )
			->add_created_event( $post2, 1 )
			->add_strategy( 'split' );
		$event_report1->migration_success();
		$event_report1 = ( new Event_Report( $post3 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->add_created_event( $post4, 1 )
			->add_strategy( 'split' );
		$event_report1->migration_failed( 'Something broked.' );

		$site_report = Site_Report::build();
		$this->assertEquals( 4, $site_report->total_events );
		$this->assertEquals( 0, $site_report->total_events_in_progress );
		$this->assertEquals( 2, $site_report->total_events_migrated );
		$this->assertEquals( 2, $site_report->total_events_remaining );
		$this->assertEquals( 1, $site_report->total_events_failed );
		$this->assertTrue( $site_report->has_errors );
		$this->assertCount( 2, $site_report->get_event_reports() );
		$this->assertCount( 1, $site_report->get_event_reports( 1, 1 ) );
	}

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
	protected function given_number_single_events( $count, $upcoming, $report_category, $is_failure ) {

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
	 * The various filter criteria should retrieve the appropriate reports.
	 *
	 * @test
	 */
	public function should_get_filtered_event_reports() {
		$events = new Events();
		// Set up some past and upcoming events with different categories.
		$this->given_number_single_events( 29, true, Single_Event_Migration_Strategy::get_slug(), false );
		$this->given_number_single_events( 30, true, 'faux-category', false );
		$this->given_number_single_events( 31, false, Single_Event_Migration_Strategy::get_slug(), false );
		$this->given_number_single_events( 32, false, 'faux-category', false );
		$this->given_number_single_events( 8, false, 'faux-category', true );

		// Assert the reports retrieved match based on the filters applied.
		$reports = $events->get_events_migrated( 1, 35, [
			Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			'upcoming'                                => true,
			Event_Report::META_KEY_MIGRATION_CATEGORY => Single_Event_Migration_Strategy::get_slug()
		] );
		$this->assertCount( 29, $reports );

		$reports = $events->get_events_migrated( 1, 35, [
			Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			'upcoming'                                => true,
			Event_Report::META_KEY_MIGRATION_CATEGORY => 'faux-category'
		] );
		$this->assertCount( 30, $reports );

		$reports = $events->get_events_migrated( 1, 35, [
			Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			'upcoming'                                => false,
			Event_Report::META_KEY_MIGRATION_CATEGORY => Single_Event_Migration_Strategy::get_slug()
		] );
		$this->assertCount( 31, $reports );

		$reports = $events->get_events_migrated( 1, 35, [
			Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			'upcoming'                                => false,
			Event_Report::META_KEY_MIGRATION_CATEGORY => 'faux-category'
		] );
		$this->assertCount( 32, $reports );

		$reports = $events->get_events_migrated( 1, 100, [
			Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			Event_Report::META_KEY_MIGRATION_CATEGORY => 'faux-category'
		] );
		$this->assertCount( 62, $reports );

		$reports = $events->get_events_migrated( 1, 100, [
			Event_Report::META_KEY_MIGRATION_CATEGORY => 'faux-category'
		] );
		$this->assertCount( 70, $reports );

		$reports = $events->get_events_migrated( 1, 150 );
		$this->assertCount( 130, $reports );
	}
}