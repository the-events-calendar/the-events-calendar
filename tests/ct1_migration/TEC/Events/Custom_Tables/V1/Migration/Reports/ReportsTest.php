<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use TEC\Events\Custom_Tables\V1\Migration\State;

class ReportsTest extends \CT1_Migration_Test_Case {

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

		$data['estimated_time_in_hours']  = 1.3;
		$data['total_events']             = 1234;
		$data['total_events_migrated']    = 33;
		$data['total_events_in_progress'] = 55;
		$data['total_events_remaining']   = $data['total_events'] - $data['total_events_migrated'];
		$data['event_reports']            = [ $event_report1, $event_report2 ];
		$data['migration_phase']          = State::PHASE_MIGRATION_IN_PROGRESS;
		$data['is_completed']             = true;
		$data['is_running']               = false;
		$data['progress_percent']         = 0;
		$data['date_completed']           = null;

		$site_report = new Site_Report( $data );
		$object      = json_decode( json_encode( $site_report ) );

		$this->assertCount( count( $data['event_reports'] ), $object->event_reports );
		$this->assertEquals( $data['estimated_time_in_hours'], $object->estimated_time_in_hours );
		$this->assertEquals( $data['total_events'], $object->total_events );
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
		// Setup some faux state
		$post1      = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$post2      = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$some_error = uniqid( 'test_', true );

		// Fail the report
		$event_report1 = ( new Event_Report( $post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', false )
			->add_created_event( $post2, 1 )
			->add_strategy( 'split' );
		$event_report1->migration_failed( $some_error );
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
		$this->assertCount( 2, $site_report->event_reports );

		$site_report = Site_Report::build( 1, 1 );
		$this->assertEquals( 4, $site_report->total_events );
		$this->assertEquals( 0, $site_report->total_events_in_progress );
		$this->assertEquals( 2, $site_report->total_events_migrated );
		$this->assertEquals( 2, $site_report->total_events_remaining );
		$this->assertCount( 1, $site_report->event_reports );
	}

}