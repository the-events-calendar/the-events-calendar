<?php
/* -- tribe-event-creation.Test.php -- */

class WP_Test_TribeEventCreation extends Tribe_WP_UnitTestCase {
	
	var $postExampleSettings;
	
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
	
	function test_tribe_create_event_template_tag_post_object_created() {
		$post = get_post( tribe_create_event( $this->postExampleSettings ) );
		
		// Check to make sure the postID is returned and the post object was created from it.
		$this->assertInternalType( 'object', $post);		
	}
	
	function test_tribe_create_event_template_tag_meta_information() {
		$post = get_post( tribe_create_event( $this->postExampleSettings ) );
		
		// Check to make sure all the post information was saved properly.
		$this->assertEquals( 3, $post->post_author );
		$this->assertEquals( 'This is event content!', $post->post_content );
		$this->assertEquals( '', get_post_meta( $post->ID, '_EventAllDay', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventHideFromUpcoming', true ) );
		$this->assertEquals( 5, get_post_meta( $post->ID, '_EventOrganizerID', true ) );
		$this->assertEquals( 8, get_post_meta( $post->ID, '_EventVenueID', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMap', true ) );
		$this->assertEquals( '2012-01-01 01:15:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2012-01-03 15:25:59', get_post_meta( $post->ID, '_EventEndDate', true ) );
	}
	
	function test_tribe_create_event_API_post_object_created() {
		$post = get_post( TribeEventsAPI::createEvent($this->postExampleSettings) );
		
		// Check to make sure the postID is returned and the post object was created from it.
		$this->assertInternalType( 'object', $post);		
	}
	
	function test_tribe_create_event_API_meta_information() {
		$post = get_post( TribeEventsAPI::createEvent( $this->postExampleSettings ) );
		
		// Check to make sure all the post information was saved properly.
		$this->assertEquals( 3, $post->post_author );
		$this->assertEquals( 'This is event content!', $post->post_content );
		$this->assertEquals( '', get_post_meta( $post->ID, '_EventAllDay', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventHideFromUpcoming', true ) );
		$this->assertEquals( 5, get_post_meta( $post->ID, '_EventOrganizerID', true ) );
		$this->assertEquals( 8, get_post_meta( $post->ID, '_EventVenueID', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMap', true ) );
		$this->assertEquals( '2012-01-01 01:15:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2012-01-03 15:25:59', get_post_meta( $post->ID, '_EventEndDate', true ) );
	}
	
}