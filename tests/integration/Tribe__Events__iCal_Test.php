<?php

class Tribe__Events__iCal_Test extends \Codeception\TestCase\WPTestCase {
	protected $post_example_settings;

	function setUp() {
		parent::setUp();
		$this->post_example_settings = array(
			'post_author'           => 3,
			'post_title'            => 'Test event',
			'post_content'          => 'This is event content!',
			'post_status'           => 'publish',
			'EventAllDay'           => false,
			'EventHideFromUpcoming' => true,
			'EventOrganizerID'      => 5,
			'EventVenueID'          => 8,
			'EventShowMapLink'      => true,
			'EventShowMap'          => true,
			'EventStartDate'        => '2012-01-01',
			'EventEndDate'          => '2012-01-03',
			'EventStartHour'        => '01',
			'EventStartMinute'      => '15',
			'EventStartMeridian'    => 'am',
			'EventEndHour'          => '03',
			'EventEndMinute'        => '25',
			'EventEndMeridian'      => 'pm',
		);
	}

	public function test_exists() {
		$this->assertTrue( class_exists( 'Tribe__Events__iCal' ), 'Check that Tribe__Events__iCal exists' );
	}

	/**
	 * Check to make sure that get_ical_link function works as expected
	 */
	public function test_get_ical_link_home() {
		$ical_link = tribe( 'tec.iCal' )->get_ical_link();
		$ical_link_home = tribe( 'tec.iCal' )->get_ical_link( 'home' );

		$this->assertEquals( $ical_link, $ical_link_home, 'Check that events home is the default' );
	}

	/**
	 * Check to make sure that get_ical_link function works as expected
	 *
	 * @uses $post_example_settings
	 */
	public function test_get_ical_link_single() {
		global $post;
		$post = get_post( Tribe__Events__API::createEvent( $this->post_example_settings ) );
		$this->assertTrue( $post instanceof WP_Post, 'Check that post creates properly' );

		$ical_link_single_via_object = tribe( 'tec.iCal' )->get_ical_link( 'single' );

		$this->assertNotEmpty( filter_var( $ical_link_single_via_object, FILTER_VALIDATE_URL ), 'Checking that we get back a valid URL from object' );

		$ical_link_single_via_function = tribe_get_single_ical_link();
		$this->assertNotEmpty( filter_var( $ical_link_single_via_function, FILTER_VALIDATE_URL ), 'Checking that we get back a valid URL from function' );

		$this->assertEquals( $ical_link_single_via_object, $ical_link_single_via_function, 'Check that the function and object get the same result' );
	}
}