<?php

/**
 * Class TribeEventsPro_RecurrenceSeriesSplitter_Test
 * @group recurrence
 */
class TribeEventsPro_RecurrenceSeriesSplitter_Test extends Tribe__Events__WP_UnitTestCase {
	public function test_break_single_event_from_series() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'post_status' => 'publish',
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
			'recurrence' => array(
				'end-type' => 'After',
				'end-count' => 5,
				'type' => 'Every Week',
			)
		);
		$post_id = Tribe__Events__API::createEvent($event_args);
		$original_children = get_posts(array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		));

		$child_to_break = $original_children[2];

		$breaker = new Tribe__Events__Pro__Recurrence_Series_Splitter();

		$breaker->break_single_event_from_series($child_to_break);

		$updated_children = get_posts(array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
		));
		foreach ( $original_children as $child_id ) {
			if ( $child_id == $child_to_break ) {
				$this->assertNotContains( $child_id, $updated_children );
			} else {
				$this->assertContains( $child_id, $updated_children );
			}
		}

		$broken_child = get_post($child_to_break);
		$this->assertEmpty($broken_child->post_parent);
		$this->assertEmpty( get_posts( array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $child_to_break,
			'post_status' => 'publish',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		)));
		$this->assertEquals( '2014-05-22 16:00:00', get_post_meta($child_to_break, '_EventStartDate', TRUE));

		$parent_recurrence = get_post_meta( $post_id, '_EventRecurrence', TRUE );
		$this->assertContains( '2014-05-22 16:00:00', $parent_recurrence['excluded-dates'] );

		$recurrence_spec = get_post_meta( $post_id, '_EventRecurrence', TRUE );
		$this->assertEquals( 4, $recurrence_spec['end-count'] );
	}

	public function test_break_first_event_from_series() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'post_status' => 'publish',
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
			'recurrence' => array(
				'end-type' => 'After',
				'end-count' => 50,
				'type' => 'Every Week',
			)
		);
		$post_id = Tribe__Events__API::createEvent($event_args);
		$original_children = get_posts(array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'eventDisplay' => 'custom',
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		));

		$breaker = new Tribe__Events__Pro__Recurrence_Series_Splitter();

		$breaker->break_first_event_from_series($post_id);
		$this->assertEmpty(get_post_meta($post_id, '_EventRecurrence', TRUE));

		$updated_children = get_posts(array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'eventDisplay' => 'custom',
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		));
		$this->assertEmpty($updated_children);

		$new_parent = get_post($original_children[0]);

		$this->assertEmpty($new_parent->post_parent);
		$this->assertCount( 48, get_posts( array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $new_parent->ID,
			'post_status' => 'publish',
			'fields' => 'ids',
			'eventDisplay' => 'custom',
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		)));
		$this->assertEquals( '2014-05-08 16:00:00', get_post_meta($new_parent->ID, '_EventStartDate', TRUE));

		$recurrence_spec = get_post_meta( $new_parent->ID, '_EventRecurrence', TRUE );
		$this->assertEquals( 49, $recurrence_spec['end-count'] );
	}

	public function test_break_remaining_events_from_series() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'post_status' => 'publish',
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
			'recurrence' => array(
				'end-type' => 'After',
				'end-count' => 5,
				'type' => 'Every Week',
			)
		);
		$post_id = Tribe__Events__API::createEvent($event_args);
		$original_children = get_posts(array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		));

		$child_to_break = $original_children[2];

		$breaker = new Tribe__Events__Pro__Recurrence_Series_Splitter();

		$breaker->break_remaining_events_from_series($child_to_break);

		$updated_children = get_posts(array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		));
		foreach ( $original_children as $child_id ) {
			$date = strtotime(get_post_meta($child_id, '_EventStartDate', TRUE));
			if ( $date < strtotime('2014-05-22') ) {
				$this->assertContains( $child_id, $updated_children );
			} else {
				$this->assertNotContains( $child_id, $updated_children );
			}
		}

		$broken_child = get_post($child_to_break);
		$this->assertEmpty($broken_child->post_parent);
		$this->assertCount( 1, get_posts( array(
			'post_type' => Tribe__Events__Events::POSTTYPE,
			'post_parent' => $child_to_break,
			'post_status' => 'publish',
			'fields' => 'ids',
		)));
		$this->assertEquals( '2014-05-22 16:00:00', get_post_meta($child_to_break, '_EventStartDate', TRUE));

		$recurrence_spec = get_post_meta( $post_id, '_EventRecurrence', TRUE );
		$this->assertEquals( 3, $recurrence_spec['end-count'] );
	}
}
 