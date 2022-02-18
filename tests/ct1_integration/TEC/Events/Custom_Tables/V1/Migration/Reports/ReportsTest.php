<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events_Pro\Custom_Tables\V1\Event_Factory;
use WP_Post;

class ReportsTest extends \Codeception\TestCase\WPTestCase {

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
			->set_is_recurring( true )
			->add_created_event( $faux_post2, 1 )
			->add_created_event( $faux_post3, 1 )
			->set_status( 'success' )
			->add_strategy( $strategy );

		$object = json_decode( json_encode( $event_report ) );

		$this->assertEquals( $event_report->get_is_recurring(), $object->is_recurring );
		$this->assertEquals( $event_report->get_has_tickets(), $object->has_tickets );
		$this->assertEquals( $event_report->get_tickets_provider(), $object->tickets_provider );
		$this->assertEquals( $event_report->get_status(), $object->status );
		$this->assertContains( $strategy, $object->strategies_applied );
		$this->assertGreaterThan( $past_microtime, $object->start_timestamp );
		$this->assertEquals( $event_report->get_created_events(), $object->created_events );
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
			->set_is_recurring( true )
			->add_created_event( $faux_post2, 1 )
			->add_created_event( $faux_post3, 1 )
			->set_status( 'success' )
			->add_strategy( $strategy );
		$event_report2 = ( new Event_Report( $faux_post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set_is_recurring( true )
			->add_created_event( $faux_post2, 1 )
			->add_created_event( $faux_post3, 1 )
			->set_status( 'success' )
			->add_strategy( $strategy );

		$data['estimated_time_in_hours'] = 1.3;
		$data['total_events']            = 1234;
		$data['event_reports']           = [ $event_report1, $event_report2 ];
		$site_report                     = new Site_Report( $data );
		$object                          = json_decode( json_encode( $site_report ) );
		$this->assertCount( count( $data['event_reports'] ), $object->event_reports );
		$this->assertEquals( $data['estimated_time_in_hours'], $object->estimated_time_in_hours );
		$this->assertEquals( $data['total_events'], $object->total_events );
		$this->assertTrue( $object->has_changes );
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
			->set_is_recurring( true )
			->add_created_event( $post2, 1 )
			->set_status( 'success' )
			->add_strategy( 'split' );
		$event_report1->success();

		// Assert it is saved properly
		$meta = get_post_meta( $post1->ID, Event_Report::META_KEY_REPORT_DATA, true );
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
		$some_error = uniqid();

		// Fail the report
		$event_report1 = ( new Event_Report( $post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set_is_recurring( true )
			->add_created_event( $post2, 1 )
			->set_status( 'success' )
			->add_strategy( 'split' );
		$event_report1->failed( $some_error );

		// Assert it is saved properly
		$meta = get_post_meta( $post1->ID, Event_Report::META_KEY_REPORT_DATA, true );
		$this->assertEquals( $event_report1->get_data(), $meta );
		$this->assertEquals( $some_error, $meta['error'] );
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
			->set_is_recurring( true )
			->add_created_event( $post2, 1 )
			->set_status( 'success' )
			->add_strategy( 'split' );
		$event_report1->success();
		$event_report1 = ( new Event_Report( $post3 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->add_created_event( $post4, 1 )
			->set_status( 'success' )
			->add_strategy( 'split' );
		$event_report1->failed( 'Something broked.' );

		$site_report = Site_Report::build();
		$this->assertEquals( 2, $site_report->get_total_events() );
		$this->assertCount( 2, $site_report->get_event_reports() );

		$site_report = Site_Report::build( 1, 1 );
		$this->assertEquals( 2, $site_report->get_total_events() );
		$this->assertCount( 1, $site_report->get_event_reports() );
	}
}