<?php

/**
 * Tests event creation functionality
 *
 * @group core
 *
 * @package TribeEvents
 */
class Tribe_Event_Creation_Test extends WP_UnitTestCase {

	/**
	 * @var holds example data for the post
	 */
	var $postExampleSettings;

	/**
	 * Extend the setUp() function by assigning values for the event creation.
	 *
	 * @uses $postExampleSettings
	 */
	function setUp() {
		parent::setUp();
		$this->postExampleSettings = array(
			'post_author' => 3,
			'post_content' => 'This is event content!',
			'EventAllDay' => false,
			'EventHideFromUpcoming' => true,
			'EventOrganizerID' => 5,
			'EventVenueID' => 8,
			'EventShowMapLink' => true,
			'EventShowMap' => true,
			'EventStartDate' => '2012-01-01',
			'EventEndDate' => '2012-01-03',
			'EventStartHour' => '01',
			'EventStartMinute' => '15',
			'EventStartMeridian' => 'am',
			'EventEndHour' => '03',
			'EventEndMinute' => '25',
			'EventEndMeridian' => 'pm'
		);
	}

	/**
	 * Check to make sure that the post object is created from a returned post ID.
	 *
	 * @uses $postExampleSettings
	 */
	function test_tribe_create_event_template_tag_post_object_created() {
		$post = get_post( tribe_create_event( $this->postExampleSettings ) );

		$this->assertInternalType( 'object', $post);
	}

	/**
	 * Check to make sure that the event data is saved properly.
	 *
	 */
	function test_tribe_create_event_template_tag_meta_information() {
		$post = get_post( tribe_create_event( $this->postExampleSettings ) );

		$this->assertEquals( 3, $post->post_author );
		$this->assertEquals( 'This is event content!', $post->post_content );
		$this->assertEquals( '', get_post_meta( $post->ID, '_EventAllDay', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventHideFromUpcoming', true ) );
		$this->assertEquals( 5, get_post_meta( $post->ID, '_EventOrganizerID', true ) );
		$this->assertEquals( 8, get_post_meta( $post->ID, '_EventVenueID', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMap', true ) );
		$this->assertEquals( '2012-01-03 15:25:00', get_post_meta( $post->ID, '_EventEndDate', true ) );
	}

	/**
	 * Check to make sure that the post object is created from a returned post ID.
	 *
	 * @uses $postExampleSettings
	 */
	function test_tribe_create_event_API_post_object_created() {
		$post = get_post( TribeEventsAPI::createEvent($this->postExampleSettings) );

		$this->assertInternalType( 'object', $post);
	}

	/**
	 * Check to make sure that the event data is saved properly.
	 *
	 */
	function test_tribe_create_event_API_meta_information() {
		$post = get_post( TribeEventsAPI::createEvent( $this->postExampleSettings ) );

		$this->assertEquals( 3, $post->post_author );
		$this->assertEquals( 'This is event content!', $post->post_content );
		$this->assertEquals( '', get_post_meta( $post->ID, '_EventAllDay', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventHideFromUpcoming', true ) );
		$this->assertEquals( 5, get_post_meta( $post->ID, '_EventOrganizerID', true ) );
		$this->assertEquals( 8, get_post_meta( $post->ID, '_EventVenueID', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMap', true ) );
		$this->assertEquals( '2012-01-03 15:25:00', get_post_meta( $post->ID, '_EventEndDate', true ) );
	}

}