<?php

/**
 * Class TribeEventsPro_RecurrenceSeriesSplitter_Test
 * @group recurrence
 */
class TribeEventsPro_RecurrenceSeriesSplitter_Test extends Tribe__Events__Pro__WP_UnitTestCase {
	public function test_break_single_event_from_series() {

		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
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
				'rules' => array(
					0 => array(
						'type' 				=> 'Every Week',
						'end-type' 			=> 'After',
						'end'				=> null,
						'end-count' 		=> 5,
					),
				),// end rules array
			)//end recurrence array
		);
		$post_id = Tribe__Events__API::createEvent($event_args);
		$original_children = get_posts(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		));
		$this->assertNotEmpty($original_children);

		$child_to_break = $original_children[2];

		$breaker = new Tribe__Events__Pro__Recurrence_Series_Splitter();

		$breaker->break_single_event_from_series($child_to_break);

		$updated_children = get_posts(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
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
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $child_to_break,
			'post_status' => 'publish',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		)));
		$this->assertEquals( '2014-05-22 16:00:00', get_post_meta( $child_to_break, '_EventStartDate', TRUE));

		$parent_recurrence = get_post_meta( $post_id, '_EventRecurrence', TRUE);
		$this->assertContains( '2014-05-22 16:00:00', $parent_recurrence['exclusions'][0]['custom']['date'] );

		$recurrence_spec = get_post_meta( $post_id, '_EventRecurrence', TRUE );
		$this->assertEquals( 5, $recurrence_spec['rules'][0]['end-count'] );
	}

	public function test_break_first_event_from_series() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
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
				'rules' => array(
					0 => array(
						'type' 				=> 'Every Week',
						'end-type' 			=> 'After',
						'end'				=> null,
						'end-count' 		=> 50,
					),
				),// end rules array
			)
		);
		$post_id = Tribe__Events__API::createEvent($event_args);
		// process the queue, otherwise all the children won't get created
		Tribe__Events__Pro__Main::instance()->queue_processor->process_queue();

		$original_children = get_posts(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'eventDisplay' => 'custom',
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		));
		$this->assertNotEmpty( $original_children );
		// we fetched all the children of the orginal, so we'd expect 1 less than the total events
		$this->assertCount( 49, $original_children );

		$breaker = new Tribe__Events__Pro__Recurrence_Series_Splitter();

		$breaker->break_first_event_from_series($post_id);
		// now that the original is broken from the recurring series, it should no longer be a recurring event
		$this->assertEmpty(get_post_meta($post_id, '_EventRecurrence', TRUE));

		$updated_children = get_posts(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'eventDisplay' => 'custom',
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		));
		// the original post should no longer be the parent of all the recurring events
		$this->assertEmpty($updated_children);

		// the first element of the original children should be the new parent
		$new_parent = get_post($original_children[0]);

		// the new parent should not have a parent
		$this->assertEmpty($new_parent->post_parent);
		// first child was promoted to parent, so remaining children count should be 48
		$this->assertCount( 48, get_posts( array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $new_parent->ID,
			'post_status' => 'publish',
			'fields' => 'ids',
			'eventDisplay' => 'custom',
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		)));
		// this should be the next week from the original (the 8th, not the 1st)
		$this->assertEquals( '2014-05-08 16:00:00', get_post_meta($new_parent->ID, '_EventStartDate', TRUE ) );

		// let's make sure the specs for the recurrence made it to the new parent
		$recurrence_spec = get_post_meta( $new_parent->ID, '_EventRecurrence', TRUE );
		$this->assertEquals( 'Every Week', $recurrence_spec['rules'][0]['type'] );
	}

	public function test_break_remaining_events_from_series() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
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
				'rules' => array(
					0 => array(
						'type' 				=> 'Every Week',
						'end-type' 			=> 'After',
						'end'				=> null,
						'end-count' 		=> 5,
					),
				),// end rules array
			)
		);
		$post_id = Tribe__Events__API::createEvent($event_args);

		// process the queue, otherwise all the children won't get created
		Tribe__Events__Pro__Main::instance()->queue_processor->process_queue();

		$original_children = get_posts(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		));
		//There are 4 events in the original children
		$this->assertCount(4, $original_children );
		$this->assertNotEmpty( $original_children );

		//breaks on the 3rd event
		$child_to_break = $original_children[2];

		$breaker = new Tribe__Events__Pro__Recurrence_Series_Splitter();
		//sets a break and keeps the break remaining events from the original
		$breaker->break_remaining_events_from_series($child_to_break);

		$updated_children = get_posts(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'orderby' => 'ID',
			'order' => 'ASC',
		));	

		$this->assertCount(4, $original_children );

		foreach ( $original_children as $child_id ) {
			$date = strtotime(get_post_meta($child_id, '_EventStartDate', TRUE));
			if ( $date < strtotime('2014-05-22') ) {
				//if its after the break it should be equal to the updated
				$this->assertContains( $child_id, $updated_children );
			} else {
				//if its after the break it should not be equal to the updated
				$this->assertNotContains( $child_id, $updated_children );
			}
		}

		$broken_child = get_post($child_to_break);
		$this->assertEmpty($broken_child->post_parent);
		$this->assertCount( 1, get_posts( array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $child_to_break,
			'post_status' => 'publish',
			'fields' => 'ids',
		)));
		//makes sure that the event that should be broken should start on may 22 the third event.
		$this->assertEquals( '2014-05-22 16:00:00', get_post_meta($child_to_break, '_EventStartDate', TRUE));

		//checking to make sure that there is four left in the original
		$recurrence_spec = get_post_meta( $post_id, '_EventRecurrence', TRUE );
		$this->assertEquals( 4, $recurrence_spec['rules'][0]['end-count'] );
	}
}
 