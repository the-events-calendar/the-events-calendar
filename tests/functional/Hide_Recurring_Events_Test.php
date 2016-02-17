<?php


class Tribe_Hide_Recurring_Event_Test extends \Codeception\TestCase\WPTestCase {

	protected $event_args;
	protected $test_parent_id  = 0;
	protected $base_date       = '';
	protected $one_month_ahead = '';


	/**
	 * Create a recurring event for use by all subsequent tests.
	 */
	public function setUp() {
		parent::setUp();
		// Use a future date for consistency
		$this->base_date       = date( 'Y-m-d', strtotime( '+1 year' ) );
		$this->one_month_ahead = date( 'Y-m-d', strtotime( '+1 year +1 month' ) );

		$this->event_args = $this->generate_event_args();

	}

	/**
	 * Ensure we get back the expected event data when querying with the
	 * tribeHideRecurrence flag set.
	 */
	public function test_hides_subsequent_recurring_events() {
		// Create the parent then trigger the build of all child events
		$this->test_parent_id = Tribe__Events__API::createEvent( $this->event_args );
		Tribe__Events__Pro__Main::instance()->queue_processor->process_queue( PHP_INT_MAX );

		$children = get_posts( array(
			'post_type'      => Tribe__Events__Main::POSTTYPE,
			'post_parent'    => $this->test_parent_id,
			'fields'         => 'ids',
			'posts_per_page' => 10,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		) );

		$query   = new WP_Query();
		$results = $query->query( array(
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date'          => $this->base_date,
			'eventDisplay'        => 'custom',
			'order'               => 'ASC'
		) );

		$this->assertCount( 1, $results );
		$this->assertEquals( $this->test_parent_id, reset( $results )->ID );

		$results = $query->query( array(
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date'          => $this->one_month_ahead,
			'eventDisplay'        => 'custom',
			'orderby'             => 'ID',
			'order'               => 'ASC',
		) );

		$this->assertCount( 1, $results );
		$this->assertNotEmpty( $children );
		$this->assertEquals( $children[4], reset( $results )->ID );

		$query   = new WP_Query();
		$results = $query->query( array(
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 0,
			'start_date'          => $this->base_date,
			'eventDisplay'        => 'custom',
		) );

		$this->assertCount( 8, $results );

		$option = tribe_get_option( 'hideSubsequentRecurrencesDefault', false );
		tribe_update_option( 'hideSubsequentRecurrencesDefault', true );

		$query   = new WP_Query();
		$results = $query->query( array(
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 0,
			'start_date'          => $this->base_date,
			'eventDisplay'        => 'custom',
		) );

		$this->assertCount( 8, $results );

		tribe_update_option( 'hideSubsequentRecurrencesDefault', $option );
	}

	public function test_supports_ids_fields_on_non_recurring_events() {
		$args = $this->generate_event_args();
		unset( $args['recurrence'] );
		$count = 3;
		for ( $i = 0; $i < $count; $i ++ ) {
			$this->test_parent_id = Tribe__Events__API::createEvent( $args );
			Tribe__Events__Pro__Main::instance()->queue_processor->process_queue( PHP_INT_MAX );
		}

		$query   = new WP_Query();
		$results = $query->query( array(
			'fields'              => 'ids',
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date'          => $this->base_date,
			'eventDisplay'        => 'custom',
			'orderby'             => 'ID',
			'order'               => 'ASC',
			'nopaging'            => true,
		) );

		$this->assertCount( $count, $results );
		foreach ( $results as $result ) {
			$this->assertInternalType( 'int', $result );
		}
	}

	/**
	 * supports ids fields on recurring events
	 */
	public function test_supports_ids_fields_on_recurring_events() {
		$args  = $this->generate_event_args();
		$count = 3;
		for ( $i = 0; $i < $count; $i ++ ) {
			$this->test_parent_id = Tribe__Events__API::createEvent( $args );
			Tribe__Events__Pro__Main::instance()->queue_processor->process_queue( PHP_INT_MAX );
		}

		$query   = new WP_Query();
		$results = $query->query( array(
			'fields'              => 'ids',
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date'          => $this->base_date,
			'eventDisplay'        => 'custom',
			'orderby'             => 'ID',
			'order'               => 'ASC',
			'nopaging'            => true,
		) );

		$this->assertCount( $count, $results );
		foreach ( $results as $result ) {
			$this->assertInternalType( 'int', $result );
		}
	}

	/**
	 * supports id=>parent fields on non recurring events
	 */
	public function test_supports_id_parent_fields_on_non_recurring_events() {
		$args                = $this->generate_event_args();
		$parent              = $this->factory()->post->create();
		$args['post_parent'] = $parent;
		unset( $args['recurrence'] );
		$count = 3;
		for ( $i = 0; $i < $count; $i ++ ) {
			$this->test_parent_id = Tribe__Events__API::createEvent( $args );
			Tribe__Events__Pro__Main::instance()->queue_processor->process_queue( PHP_INT_MAX );
		}

		$query   = new WP_Query();
		$results = $query->query( array(
			'fields'              => 'id=>parent',
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date'          => $this->base_date,
			'eventDisplay'        => 'custom',
			'orderby'             => 'ID',
			'order'               => 'ASC',
			'nopaging'            => true,
		) );

		$this->assertCount( $count, $results );
		foreach ( $results as $key => $value ) {
			$this->assertInternalType( 'int', $key );
			$this->assertEquals( $parent, $value );
		}
	}

	/**
	 * supports id=>parent fields on recurring events
	 */
	public function test_supports_id_parent_fields_on_recurring_events() {
		$args  = $this->generate_event_args();
		$parent              = $this->factory()->post->create();
		$args['post_parent'] = $parent;
		$count = 3;
		for ( $i = 0; $i < $count; $i ++ ) {
			$this->test_parent_id = Tribe__Events__API::createEvent( $args );
			Tribe__Events__Pro__Main::instance()->queue_processor->process_queue( PHP_INT_MAX );
		}

		$query   = new WP_Query();
		$results = $query->query( array(
			'fields'              => 'id=>parent',
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date'          => $this->base_date,
			'eventDisplay'        => 'custom',
			'orderby'             => 'ID',
			'order'               => 'ASC',
			'nopaging'            => true,
		) );

		$this->assertCount( $count, $results );
		foreach ( $results as $key => $value ) {
			$this->assertInternalType( 'int', $key );
			$this->assertEquals( $parent, $value );
		}
	}

	protected function generate_event_args() {
		return [
			'post_type'        => Tribe__Events__Main::POSTTYPE,
			'post_title'       => __FUNCTION__ . ' ',
			'post_content'     => __CLASS__ . ' ' . __FUNCTION__,
			'post_name'        => 'test-tribeHideRecurrence',
			'post_status'      => 'publish',
			'EventStartDate'   => $this->base_date,
			'EventEndDate'     => $this->base_date,
			'EventStartHour'   => 16,
			'EventEndHour'     => 17,
			'EventStartMinute' => 0,
			'EventEndMinute'   => 0,
			'EventTimezone'    => 'UTC',
			'recurrence'       => [
				'rules' => [
					[
						'type'      => 'Every Week',
						'end-type'  => 'After',
						'end'       => null,
						'end-count' => 8,
					],
				],
			],
		];
	}
}