<?php

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Events\Test\Factories\Event;

class SnapshotTest extends \Codeception\TestCase\WPTestCase {
	use SnapshotAssertions;

	public function setUp() {
		parent::setUp();

		require_once codecept_data_dir('classes/Tribe/Plugins/Filter_Bar/Context_Filter.php');
		require_once codecept_data_dir('classes/Tribe/Plugins/Filter_Bar/Tribe_Events_Filterbar_Filter.php');
	}

	// tests for src/Tribe/Compatibility/Filter_Bar/Events_Virtual_Filter.php
	// snapshots: get_admin_form, setup_join_clause, setup_where_clause

	/**
	 * @test
	 */
	public function should_correctly_inject_title() {
		$filter = new Events_Virtual_Filter();
		$html   = $filter->get_admin_form();

		$this->assertEquals( tribe_get_virtual_event_label_singular(), $html );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_field_name() {
		$filter = new Events_Virtual_Filter();
		$method = new \ReflectionMethod( $filter, 'get_admin_field_name' );
		$method->setAccessible( true );

		$output = $method->invoke( $filter, 'virtual' );

		$this->assertEquals('tribe_filter_options[filterbar_events_virtual][virtual]', $output );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_join_clause_for_virtual() {
		$filter = new Events_Virtual_Filter();
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = true;

		$method = $class->getMethod( 'setup_join_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		$this->assertMatchesStringSnapshot( $filter->joinClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_join_clause_for_non_virtual() {
		$filter = new Events_Virtual_Filter();
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = false;

		$method = $class->getMethod( 'setup_join_clause' );
		$method->setAccessible(true);

		$eventFactory = new Event();
		$non_ve       = ( $eventFactory )->create_many( 3, [ 'when' => 'tomorrow' ] );
		$ve           = ( $eventFactory )->create_many(
			3,
			[ 'when' => 'tomorrow', 'meta_input' => [ '_tribe_events_is_virtual' => 'yes' ] ]
		);

		$method->invoke( $filter );

		$this->assertMatchesStringSnapshot( $filter->joinClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_join_clause_for_all() {
		$filter = new Events_Virtual_Filter();
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = 'all';

		$method = $class->getMethod( 'setup_join_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		// Should be empty because we aren't modifying the query.
		$this->assertEmpty( $filter->joinClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_join_clause_for_non_virtual_with_no_virtual_events() {
		$filter = new Events_Virtual_Filter();
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = false;

		$method = $class->getMethod( 'setup_join_clause' );
		$method->setAccessible(true);

		( new Event() )->create_many( 3, [ 'when' => 'tomorrow' ] );

		$method->invoke( $filter );

		$this->assertEmpty( $filter->joinClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_where_clause_for_virtual() {
		$filter = new Events_Virtual_Filter();
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = true;
		$this->given_some_ve_and_non_ve_events();

		$method = $class->getMethod( 'setup_where_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		$this->assertMatchesStringSnapshot( $filter->whereClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_where_clause_for_non_virtual() {
		$filter = new Events_Virtual_Filter();
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = true;
		$this->given_some_ve_and_non_ve_events();

		$method = $class->getMethod( 'setup_where_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		$this->assertMatchesStringSnapshot( $filter->whereClause );
	}

	/**
	 * @test
	 */
	public function it_should_return_correct_where_clause_for_all() {
		$filter = new Events_Virtual_Filter();
		$class  = new \ReflectionClass( $filter );
		$filter->currentValue = 'all';
		$this->given_some_ve_and_non_ve_events();

		$method = $class->getMethod( 'setup_where_clause' );
		$method->setAccessible(true);

		$method->invoke( $filter );

		// Should be empty because we aren't modifying the query.
		$this->assertEmpty( $filter->whereClause );
	}

	protected function given_some_ve_and_non_ve_events() {
		$event_factory = new Event();
		$non_ve        = ( $event_factory )->create_many( 3, [ 'when' => 'tomorrow' ] );
		$ve            = ( $event_factory )->create_many(
			3,
			[ 'when' => 'tomorrow', 'meta_input' => [ '_tribe_events_is_virtual' => 'yes' ] ]
		);
	}
}
