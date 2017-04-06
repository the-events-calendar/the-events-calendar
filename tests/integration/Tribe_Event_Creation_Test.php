<?php

/**
 * Tests event creation functionality
 *
 * @group   core
 *
 * @package TribeEvents
 */
class Tribe_Event_Creation_Test extends \Codeception\TestCase\WPTestCase {
	/**
	 * Check to make sure that the post object is created from a returned post ID.
	 *
	 * @uses $post_example_settings
	 */
	public function test_tribe_create_event_template_tag_post_object_created() {
		$post = get_post( tribe_create_event( $this->post_example_settings ) );

		$this->assertInternalType( 'object', $post );
	}

	/**
	 * Check to make sure that the event data is saved properly.
	 *
	 */
	public function test_tribe_create_event_template_tag_meta_information() {
		$post = get_post( tribe_create_event( $this->post_example_settings ) );

		$this->assertEquals( 3, $post->post_author );
		$this->assertEquals( 'This is event content!', $post->post_content );
		//The Event does not go all day so it is 'no'
		$this->assertEquals( 'no', get_post_meta( $post->ID, '_EventAllDay', true ) );
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
	 * @uses $post_example_settings
	 */
	public function test_tribe_create_event_API_post_object_created() {
		$post = get_post( Tribe__Events__API::createEvent( $this->post_example_settings ) );

		$this->assertInternalType( 'object', $post );
	}

	/**
	 * Check to make sure that the event data is saved properly.
	 */
	public function test_tribe_create_event_API_meta_information() {
		$post = get_post( Tribe__Events__API::createEvent( $this->post_example_settings ) );

		$this->assertEquals( 3, $post->post_author );
		$this->assertEquals( 'This is event content!', $post->post_content );
		$this->assertEquals( 'no', get_post_meta( $post->ID, '_EventAllDay', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventHideFromUpcoming', true ) );
		$this->assertEquals( 5, get_post_meta( $post->ID, '_EventOrganizerID', true ) );
		$this->assertEquals( 8, get_post_meta( $post->ID, '_EventVenueID', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMapLink', true ) );
		$this->assertEquals( 1, get_post_meta( $post->ID, '_EventShowMap', true ) );
		$this->assertEquals( '2012-01-03 15:25:00', get_post_meta( $post->ID, '_EventEndDate', true ) );
	}

}