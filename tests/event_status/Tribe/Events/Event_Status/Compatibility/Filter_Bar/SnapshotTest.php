<?php

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

use Tribe\Events\Event_Status\Status_Labels;
use Tribe\Events\Test\Factories\Event;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class SnapshotTest extends HtmlTestCase {

	public function setUp() {
		parent::setUp();

		require_once codecept_data_dir( 'classes/Tribe/Plugins/Filter_Bar/Context_Filter.php' );
		require_once codecept_data_dir( 'classes/Tribe/Plugins/Filter_Bar/Tribe_Events_Filterbar_Filter.php' );
	}

	// tests for src/Tribe/Event_Status/Compatibility/Filter_Bar/Events_Status_Filter.php
	// snapshots: get_admin_form, setup_join_clause, setup_where_clause

	/**
	 * @test
	 */
	public function should_correctly_inject_title() {
		$status_labels = new Status_Labels();
		$filter = new Events_Status_Filter( $status_labels );
		$html   = $filter->get_admin_form();

		$this->assertEquals( $status_labels->get_event_status_label(), $html );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_field_name() {
		$filter = new Events_Status_Filter( new Status_Labels() );
		$method = new \ReflectionMethod( $filter, 'get_admin_field_name' );
		$method->setAccessible( true );

		$output = $method->invoke( $filter, 'event_status' );

		$this->assertEquals('tribe_filter_options[filterbar_event_status][event_status]', $output );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_join_clause_for_event_status() {
		$filter = new Events_Status_Filter( new Status_Labels() );
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = true;

		$method = $class->getMethod( 'setup_join_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		$this->assertMatchesSnapshot( $filter->joinClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_where_clause_for_canceled() {
		$filter = new Events_Status_Filter( new Status_Labels() );
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = [ 'canceled' ];
		$this->given_some_scheduled_canceled_and_postponed_events();

		$method = $class->getMethod( 'setup_where_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		$this->assertMatchesSnapshot( $filter->whereClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_where_clause_for_postponed() {
		$filter = new Events_Status_Filter( new Status_Labels() );
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = [ 'postponed' ];
		$this->given_some_scheduled_canceled_and_postponed_events();

		$method = $class->getMethod( 'setup_where_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		$this->assertMatchesSnapshot( $filter->whereClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_where_clause_for_canceled_and_postponed() {
		$filter = new Events_Status_Filter( new Status_Labels() );
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = [ 'canceled', 'postponed' ];
		$this->given_some_scheduled_canceled_and_postponed_events();

		$method = $class->getMethod( 'setup_where_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		$this->assertMatchesSnapshot( $filter->whereClause );
	}

	protected function given_some_scheduled_canceled_and_postponed_events() {
		$event_factory = new Event();
		$scheduled        = ( $event_factory )->create_many( 3, [ 'when' => 'tomorrow' ] );
		$canceled            = ( $event_factory )->create_many(
			3,
			[ 'when' => 'tomorrow', 'meta_input' => [ '_tribe_events_status' => 'canceled' ] ]
		);
		$postponed            = ( $event_factory )->create_many(
			3,
			[ 'when' => 'tomorrow', 'meta_input' => [ '_tribe_events_status' => 'postponed' ] ]
		);
	}
}
