<?php

/**
 * Class TribeEventsPro_RecurrenceInstance_Test
 *
 * @group pro
 * @group recurrence
 */
class TribeEventsPro_RecurrenceInstance_Test extends Tribe__Events__WP_UnitTestCase {
	public function test_duration() {
		$start_date = date('Y-m-d', strtotime('2014-01-01'));
		$parent_id = Tribe__Events__API::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
		));
		$instance = new Tribe__Events__Pro__Recurrence_Instance( $parent_id, strtotime('2014-03-01'));
		$this->assertEquals(3600, $instance->get_duration());
	}

	public function test_end_date() {
		$start_date = date('Y-m-d', strtotime('2014-01-01'));
		$parent_id = Tribe__Events__API::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
		));
		$instance = new Tribe__Events__Pro__Recurrence_Instance( $parent_id, strtotime('2014-03-01 16:00:00'));
		$this->assertEquals( strtotime('2014-03-01 16:00:00')+3600, $instance->get_end_date()->format('U') );
	}

	public function test_save() {
		$start_date = date('Y-m-d', strtotime('2014-01-01'));
		$parent_id = Tribe__Events__API::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
		));
		$instance = new Tribe__Events__Pro__Recurrence_Instance( $parent_id, strtotime('2014-03-01 16:00:00'));
		$instance->save();
		$post = get_post($instance->get_id());
		$this->assertEquals('2014-03-01 16:00:00', get_post_meta($post->ID, '_EventStartDate', true));
		$this->assertEquals('2014-03-01 17:00:00', get_post_meta($post->ID, '_EventEndDate', true));
		$this->assertEquals($parent_id, $post->post_parent);

		$parent = get_post($parent_id, OBJECT, 'display');
		$child = get_post($instance->get_id(), OBJECT, 'display');
		$this->assertNotEquals($parent->guid, $child->guid);
	}

	public function test_venue_organizer() {
		$organizer_id = Tribe__Events__API::createOrganizer(array(
			'Organizer' => 'Test Organizer',
		));
		$venue_id = Tribe__Events__API::createVenue(array(
			'Venue' => 'Test Venue',
		));
		$start_date = date('Y-m-d', strtotime('2014-01-01'));
		$parent_id = Tribe__Events__API::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
			'Organizer' => array( 'OrganizerID' => $organizer_id ),
			'Venue' => array( 'VenueID' => $venue_id ),
		));
		$instance = new Tribe__Events__Pro__Recurrence_Instance( $parent_id, strtotime('2014-03-01 16:00:00'));
		$instance->save();
		$this->assertEquals($organizer_id, get_post_meta($instance->get_id(), '_EventOrganizerID', TRUE));
		$this->assertEquals($venue_id, get_post_meta($instance->get_id(), '_EventVenueID', TRUE));
	}

	public function test_terms() {
		$start_date = date('Y-m-d', strtotime('2014-01-01'));
		$parent_id = Tribe__Events__API::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
		));
		$tag = $this->factory->tag->create_object(array('name' => 'test tag'));
		wp_set_object_terms( $parent_id, (int)$tag, 'post_tag' );
		$instance = new Tribe__Events__Pro__Recurrence_Instance( $parent_id, strtotime('2014-03-01 16:00:00'));
		$instance->save();

		$this->assertEqualSets( array( $tag ), wp_get_object_terms( $instance->get_id(), 'post_tag', array( 'fields' => 'ids' ) ) );
	}
}
 