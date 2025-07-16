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

	/**
	 * @var \ReflectionClass
	 */
	protected $reflection_class;

	public function setUp() {
		parent::setUp();

		require_once codecept_data_dir( 'classes/Tribe/Plugins/Filter_Bar/Context_Filter.php' );
		require_once codecept_data_dir( 'classes/Tribe/Plugins/Filter_Bar/Tribe_Events_Filterbar_Filter.php' );

		$this->filter           = new Events_Status_Filter( new Status_Labels() );
		$this->reflection_class = new \ReflectionClass( $this->filter );
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
		$this->call_private_method( 'pre_get_posts', [ $query ] );

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
		$this->call_private_method( 'pre_get_posts', [ $query ] );

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
		$this->call_private_method( 'pre_get_posts', [ $query ] );

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
		$this->call_private_method( 'pre_get_posts', [ $query ] );

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
		$this->call_private_method( 'pre_get_posts', [ $query ] );

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
		$this->call_private_method( 'pre_get_posts', [ $query ] );

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
	 * @test
	 */
	public function it_should_remove_soldout_from_where_clause() {
		// Set filter to multiple values including soldout.
		$this->filter->currentValue = [ 'canceled', 'soldout', 'postponed' ];

		// Call setup_where_clause method.
		$this->call_private_method( 'setup_where_clause' );

		// Get the where clause.
		$where_clause = $this->get_private_property( 'whereClause' );

		// Verify that soldout is NOT in the where clause.
		$this->assertStringNotContainsString( 'soldout', $where_clause, 'Where clause should not contain soldout' );

		// Verify that canceled and postponed ARE in the where clause.
		$this->assertStringContainsString( 'canceled', $where_clause, 'Where clause should contain canceled' );
		$this->assertStringContainsString( 'postponed', $where_clause, 'Where clause should contain postponed' );
	}

	/**
	 * @test
	 */
	public function it_should_skip_where_clause_when_only_soldout_selected() {
		// Set filter to only soldout.
		$this->filter->currentValue = 'soldout';

		// Call setup_where_clause method.
		$this->call_private_method( 'setup_where_clause' );

		// Get the where clause.
		$where_clause = $this->get_private_property( 'whereClause' );

		// Verify that no where clause was set (should be null or empty).
		$this->assertEmpty( $where_clause, 'Where clause should be empty when only soldout is selected' );
	}

	/**
	 * Helper method to call private/protected methods on the filter
	 *
	 * @param string $method_name The method name to call
	 * @param array  $args        Optional arguments to pass to the method
	 *
	 * @return mixed The method return value
	 */
	private function call_private_method( $method_name, $args = [] ) {
		$method = $this->reflection_class->getMethod( $method_name );
		$method->setAccessible( true );

		return $method->invokeArgs( $this->filter, $args );
	}

	/**
	 * Helper method to get private/protected property values
	 *
	 * @param string $property_name The property name to get
	 *
	 * @return mixed The property value
	 */
	private function get_private_property( $property_name ) {
		$property = $this->reflection_class->getProperty( $property_name );
		$property->setAccessible( true );

		return $property->getValue( $this->filter );
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
		update_post_meta( $ticket_id, '_tribe_ticket_capacity', '-1' ); // Unlimited capacity.
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
