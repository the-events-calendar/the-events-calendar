<?php

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

use Tribe\Events\Event_Status\Status_Labels;
use Tribe\Events\Test\Factories\Event;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;
use WP_Query;

class Events_Status_Soldout_Test extends HtmlTestCase {

	/**
	 * @var Events_Status_Filter
	 */
	protected $filter;

	public function setUp() {
		parent::setUp();

		require_once codecept_data_dir( 'classes/Tribe/Plugins/Filter_Bar/Context_Filter.php' );
		require_once codecept_data_dir( 'classes/Tribe/Plugins/Filter_Bar/Tribe_Events_Filterbar_Filter.php' );

		$this->filter = new Events_Status_Filter( new Status_Labels() );
	}

	/**
	 * @test
	 */
	public function it_should_modify_query_when_soldout_is_selected() {
		// Set the filter to only look for sold-out events.
		$this->filter->currentValue = 'soldout';

		// Create regular event.
		$event_factory = new Event();
		$regular_event = $event_factory->create( [ 'when' => 'tomorrow' ] );

		// Create sold-out event and ticket.
		$soldout_event = $event_factory->create( [ 'when' => 'tomorrow' ] );
		$this->create_soldout_ticket_for_event( $soldout_event );

		// Create WP_Query instance.
		$query = new WP_Query();

		// Call pre_get_posts method.
		$class  = new \ReflectionClass( $this->filter );
		$method = $class->getMethod( 'pre_get_posts' );
		$method->setAccessible( true );
		$method->invoke( $this->filter, $query );

		// Check that the sold-out event is in post__not_in.
		$post_not_in = $query->get( 'post__not_in', [] );
		$this->assertContains(
			$soldout_event,
			$post_not_in,
			'Sold-out event should be excluded from query results'
		);

		// Check regular event is not in post__not_in.
		$this->assertNotContains(
			$regular_event,
			$post_not_in,
			'Regular event should not be excluded from query results'
		);

		// Cleanup.
		wp_delete_post( $regular_event, true );
		wp_delete_post( $soldout_event, true );
	}

	/**
	 * @test
	 */
	public function it_should_modify_query_when_soldout_is_in_array() {
		// Set the filter to look for multiple statuses including sold-out.
		$this->filter->currentValue = [ 'canceled', 'soldout' ];

		// Create sold-out event.
		$event_factory = new Event();
		$soldout_event = $event_factory->create( [ 'when' => 'tomorrow' ] );
		$this->create_soldout_ticket_for_event( $soldout_event );

		// Create WP_Query instance.
		$query = new WP_Query();

		// Call pre_get_posts method.
		$class  = new \ReflectionClass( $this->filter );
		$method = $class->getMethod( 'pre_get_posts' );
		$method->setAccessible( true );
		$method->invoke( $this->filter, $query );

		// Check that the sold-out event is in post__not_in.
		$post_not_in = $query->get( 'post__not_in', [] );
		$this->assertContains(
			$soldout_event,
			$post_not_in,
			'Sold-out event should be excluded when soldout is part of filter array'
		);

		// Cleanup.
		wp_delete_post( $soldout_event, true );
	}

	/**
	 * @test
	 */
	public function it_should_not_modify_query_when_soldout_not_selected() {
		// Set the filter to something other than sold-out.
		$this->filter->currentValue = 'canceled';

		// Create sold-out event that should NOT be filtered out in this case.
		$event_factory = new Event();
		$soldout_event = $event_factory->create( [ 'when' => 'tomorrow' ] );
		$this->create_soldout_ticket_for_event( $soldout_event );

		// Create WP_Query instance.
		$query                = new WP_Query();
		$original_post_not_in = $query->get( 'post__not_in', [] );

		// Call pre_get_posts method.
		$class  = new \ReflectionClass( $this->filter );
		$method = $class->getMethod( 'pre_get_posts' );
		$method->setAccessible( true );
		$method->invoke( $this->filter, $query );

		// Verify post__not_in was not modified.
		$this->assertEquals(
			$original_post_not_in,
			$query->get( 'post__not_in', [] ),
			'post__not_in should not be modified when soldout is not selected'
		);

		// Cleanup.
		wp_delete_post( $soldout_event, true );
	}

	/**
	 * @test
	 */
	public function it_should_handle_no_soldout_events_gracefully() {
		// Set the filter to look for sold-out events.
		$this->filter->currentValue = 'soldout';

		// Create regular event with no sold-out status.
		$event_factory = new Event();
		$regular_event = $event_factory->create( [ 'when' => 'tomorrow' ] );

		// Create WP_Query instance.
		$query = new WP_Query();

		// Call pre_get_posts method.
		$class  = new \ReflectionClass( $this->filter );
		$method = $class->getMethod( 'pre_get_posts' );
		$method->setAccessible( true );
		$method->invoke( $this->filter, $query );

		// Check that post__not_in is either unchanged or doesn't contain our regular event.
		$post_not_in = $query->get( 'post__not_in', [] );
		if ( ! empty( $post_not_in ) ) {
			$this->assertNotContains(
				$regular_event,
				$post_not_in,
				'Regular event should not be excluded even when filtering for sold-out events'
			);
		}

		// Cleanup.
		wp_delete_post( $regular_event, true );
	}

	/**
	 * @test
	 */
	public function it_should_not_filter_unlimited_capacity_tickets() {
		// Set the filter to look for sold-out events.
		$this->filter->currentValue = 'soldout';

		// Create event with unlimited capacity ticket (capacity = -1).
		$event_factory   = new Event();
		$unlimited_event = $event_factory->create( [ 'when' => 'tomorrow' ] );
		$this->create_unlimited_ticket_for_event( $unlimited_event );

		// Create WP_Query instance.
		$query = new WP_Query();

		// Call pre_get_posts method.
		$class  = new \ReflectionClass( $this->filter );
		$method = $class->getMethod( 'pre_get_posts' );
		$method->setAccessible( true );
		$method->invoke( $this->filter, $query );

		// Check that unlimited capacity event is NOT filtered out.
		$post_not_in = $query->get( 'post__not_in', [] );
		$this->assertNotContains(
			$unlimited_event,
			$post_not_in,
			'Event with unlimited capacity ticket should not be filtered out'
		);

		// Cleanup.
		wp_delete_post( $unlimited_event, true );
	}

	/**
	 * @test
	 */
	public function it_should_not_filter_tickets_without_stock_management() {
		// Set the filter to look for sold-out events.
		$this->filter->currentValue = 'soldout';

		// Create event with ticket that has no stock management.
		$event_factory  = new Event();
		$no_stock_event = $event_factory->create( [ 'when' => 'tomorrow' ] );
		$this->create_ticket_without_stock_management( $no_stock_event );

		// Create WP_Query instance.
		$query = new WP_Query();

		// Call pre_get_posts method.
		$class  = new \ReflectionClass( $this->filter );
		$method = $class->getMethod( 'pre_get_posts' );
		$method->setAccessible( true );
		$method->invoke( $this->filter, $query );

		// Check that event without stock management is NOT filtered out.
		$post_not_in = $query->get( 'post__not_in', [] );
		$this->assertNotContains(
			$no_stock_event,
			$post_not_in,
			'Event with ticket without stock management should not be filtered out'
		);

		// Cleanup.
		wp_delete_post( $no_stock_event, true );
	}

	/**
	 * Helper method to create a sold-out ticket for an event
	 *
	 * @param int $event_id The event ID to create a sold-out ticket for
	 *
	 * @return int The ticket ID
	 */
	private function create_soldout_ticket_for_event( $event_id ) {
		// Create a ticket.
		$ticket_id = wp_insert_post( [
			'post_type'   => 'tec_tc_ticket',
			'post_status' => 'publish',
			'post_title'  => 'Test Ticket for Event ' . $event_id,
		] );

		// Set up all required meta for sold-out detection.
		update_post_meta( $ticket_id, '_manage_stock', 'yes' );
		update_post_meta( $ticket_id, '_stock', '0' );
		update_post_meta( $ticket_id, '_tribe_ticket_capacity', '10' ); // Set capacity > -1
		update_post_meta( $ticket_id, '_tec_tickets_commerce_event', $event_id );

		return $ticket_id;
	}

	/**
	 * Helper method to create an unlimited capacity ticket for an event
	 *
	 * @param int $event_id The event ID to create an unlimited ticket for
	 *
	 * @return int The ticket ID
	 */
	private function create_unlimited_ticket_for_event( $event_id ) {
		// Create a ticket.
		$ticket_id = wp_insert_post( [
			'post_type'   => 'tec_tc_ticket',
			'post_status' => 'publish',
			'post_title'  => 'Unlimited Test Ticket for Event ' . $event_id,
		] );

		// Set up unlimited capacity ticket (capacity = -1).
		update_post_meta( $ticket_id, '_manage_stock', 'yes' );
		update_post_meta( $ticket_id, '_stock', '0' );
		update_post_meta( $ticket_id, '_tribe_ticket_capacity', '-1' ); // Unlimited capacity
		update_post_meta( $ticket_id, '_tec_tickets_commerce_event', $event_id );

		return $ticket_id;
	}

	/**
	 * Helper method to create a ticket without stock management
	 *
	 * @param int $event_id The event ID to create a ticket for
	 *
	 * @return int The ticket ID
	 */
	private function create_ticket_without_stock_management( $event_id ) {
		// Create a ticket.
		$ticket_id = wp_insert_post( [
			'post_type'   => 'tec_tc_ticket',
			'post_status' => 'publish',
			'post_title'  => 'No Stock Management Ticket for Event ' . $event_id,
		] );

		// Set up ticket without stock management.
		update_post_meta( $ticket_id, '_manage_stock', 'no' );
		update_post_meta( $ticket_id, '_tribe_ticket_capacity', '10' );
		update_post_meta( $ticket_id, '_tec_tickets_commerce_event', $event_id );

		return $ticket_id;
	}
}
