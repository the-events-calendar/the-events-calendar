<?php

/**
 * Class Tribe_Recurring_Event_Test
 */
class Tribe_Recurring_Event_Test extends WP_UnitTestCase {
	public function test_is_recurring() {
		$start_date = date('Y-m-d', time());
		$post_id = TribeEventsAPI::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
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
		));
		$this->assertTrue( tribe_is_recurring_event( $post_id ) );
	}

	public function test_is_not_recurring() {
		$start_date = date('Y-m-d', time());
		// no recurrence
		$post_id = TribeEventsAPI::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
		));
		$this->assertFalse( tribe_is_recurring_event( $post_id ) );

		// recur one time
		$post_id = TribeEventsAPI::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
			'recurrence' => array(
				'end-type' => 'After',
				'end-count' => 1,
				'type' => 'Every Week',
			)
		));
		$this->assertFalse( tribe_is_recurring_event( $post_id ) );
	}

	public function test_get_recurrence_start_dates() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$post_id = TribeEventsAPI::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
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
		));
		$dates = tribe_get_recurrence_start_dates( $post_id );
		$expected = array(
			'2014-05-01 16:00:00',
			'2014-05-08 16:00:00',
			'2014-05-15 16:00:00',
			'2014-05-22 16:00:00',
			'2014-05-29 16:00:00',
		);
		$this->assertEqualSets($expected, $dates);
	}

	public function test_update_event() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
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
		$post_id = TribeEventsAPI::createEvent($event_args);
		$original_dates = tribe_get_recurrence_start_dates( $post_id );

		TribeEventsApi::updateEvent($post_id, $event_args);
		$updated_dates = tribe_get_recurrence_start_dates( $post_id );
		$expected = array(
			'2014-05-01 16:00:00',
			'2014-05-08 16:00:00',
			'2014-05-15 16:00:00',
			'2014-05-22 16:00:00',
			'2014-05-29 16:00:00',
		);
		$this->assertEqualSets($original_dates, $updated_dates);
		$this->assertEqualSets($expected, $updated_dates);
	}

	public function test_nondestructive_update_event() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
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
		$post_id = TribeEventsAPI::createEvent($event_args);
		$original_children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'posts_per_page' => 25,
		));

		TribeEventsApi::updateEvent($post_id, $event_args);

		$updated_children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'posts_per_page' => 25,
		));
		$this->assertCount(4, $original_children);
		$this->assertCount(4, $updated_children);
		$this->assertEqualSets($original_children, $updated_children);
	}

	public function test_update_event_with_deleted_instances() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
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
		$post_id = TribeEventsAPI::createEvent($event_args);
		$original_children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'posts_per_page' => 25,
		));

		wp_delete_post( $original_children[2], TRUE );

		$meta = get_post_meta( $post_id, '_EventRecurrence', TRUE );
		$this->assertContains( '2014-05-22 16:00:00', $meta['excluded-dates'] );

		TribeEventsApi::updateEvent($post_id, $event_args);


		$updated_children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
			'posts_per_page' => 25,
		));

		$meta = get_post_meta( $post_id, '_EventRecurrence', TRUE );

		$this->assertCount(4, $original_children);
		$this->assertCount(3, $updated_children);
		$this->assertNotContains( $original_children[2], $updated_children );
	}

	public function test_changing_start_date() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
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
				'type' => 'Every Day',
			)
		);
		$post_id = TribeEventsAPI::createEvent($event_args);
		$original_dates = tribe_get_recurrence_start_dates($post_id);
		$this->assertEqualSets(
			array(
				'2014-05-01 16:00:00',
				'2014-05-02 16:00:00',
				'2014-05-03 16:00:00',
				'2014-05-04 16:00:00',
				'2014-05-05 16:00:00',
			),
			$original_dates
		);

		$new_date = date('Y-m-d', strtotime('2014-05-08'));
		$event_args['EventStartDate'] = $new_date;
		$event_args['EventEndDate'] = $new_date;
		TribeEventsApi::updateEvent($post_id, $event_args);

		$new_dates = tribe_get_recurrence_start_dates($post_id);
		$this->assertEqualSets(
			array(
				'2014-05-08 16:00:00',
				'2014-05-09 16:00:00',
				'2014-05-10 16:00:00',
				'2014-05-11 16:00:00',
				'2014-05-12 16:00:00',
			),
			$new_dates
		);
	}

	public function test_terms_on_update() {
		$tags = array(
			$this->factory->tag->create_object(array('name' => 'test tag a')),
			$this->factory->tag->create_object(array('name' => 'test tag b')),
		);
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'post_status' => 'publish',
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
			'tags_input' => array($tags[0]),
			'recurrence' => array(
				'end-type' => 'After',
				'end-count' => 5,
				'type' => 'Every Week',
			)
		);
		$post_id = TribeEventsAPI::createEvent($event_args);

		$event_args['tags_input'] = array($tags[1]);

		TribeEventsApi::updateEvent($post_id, $event_args);

		$children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
		));

		foreach ( $children as $child_id ) {
			$child_terms = wp_get_object_terms($child_id, 'post_tag', array('fields' => 'ids'));
			$this->assertEqualSets( array($tags[1]), $child_terms );
		}
	}

	public function test_venue_organizer_on_update() {
		$organizers = array(
			TribeEventsAPI::createOrganizer(array(
				'Organizer' => 'Test Organizer A',
			)),
			TribeEventsAPI::createOrganizer(array(
				'Organizer' => 'Test Organizer B',
			)),
		);
		$venues = array(
			TribeEventsAPI::createVenue(array(
				'Venue' => 'Test Venue A',
			)),
			TribeEventsAPI::createVenue(array(
				'Venue' => 'Test Venue B',
			)),
		);
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'post_status' => 'publish',
			'EventStartDate' => $start_date,
			'EventEndDate' => $start_date,
			'EventStartHour' => 16,
			'EventEndHour' => 17,
			'EventStartMinute' => 0,
			'EventEndMinute' => 0,
			'Organizer' => array( 'OrganizerID' => $organizers[0] ),
			'Venue' => array( 'VenueID' => $venues[0] ),
			'recurrence' => array(
				'end-type' => 'After',
				'end-count' => 5,
				'type' => 'Every Week',
			)
		);
		$post_id = TribeEventsAPI::createEvent($event_args);

		$event_args['Organizer'] = array( 'OrganizerID' => $organizers[1] );
		$event_args['Venue'] = array( 'VenueID' => $venues[1] );

		TribeEventsApi::updateEvent($post_id, $event_args);

		$children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'post_status' => 'publish',
			'fields' => 'ids',
		));

		foreach ( $children as $child_id ) {
			$this->assertEquals( $organizers[1], get_post_meta($child_id, '_EventOrganizerID', TRUE));
			$this->assertEquals( $venues[1], get_post_meta($child_id, '_EventVenueID', TRUE));
		}
	}

	public function test_permalink_creation() {
		if ( get_option( 'permalink_structure', '') == '' ) {
			return; // no permalinks to test
		}
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => __CLASS__,
			'post_name' => 'test-permalinks',
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
				'end-count' => 2,
				'type' => 'Every Week',
			)
		);
		$post_id = TribeEventsAPI::createEvent($event_args);
		$base_url = home_url().'/event/test-permalinks/';
		$this->assertEquals($base_url.user_trailingslashit('2014-05-01'), get_post_permalink($post_id));

		$children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'fields' => 'ids',
		));
		$child_id = reset($children);
		$this->assertEquals($base_url.user_trailingslashit('2014-05-08'), get_post_permalink($child_id));
	}

	public function test_eventDate_queries() {
		if ( get_option( 'permalink_structure', '') == '' ) {
			return; // no permalinks to test
		}
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => __CLASS__,
			'post_name' => 'test-permalinks',
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
				'end-count' => 2,
				'type' => 'Every Week',
			)
		);
		$post_id = TribeEventsAPI::createEvent($event_args);

		$children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'fields' => 'ids',
		));
		$child_id = reset($children);

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => TribeEvents::POSTTYPE,
			'fields' => 'ids',
			'tribe_events' => 'test-permalinks', // this will be present for a normal request
			'name' => 'test-permalinks',
			'eventDate' => '2014-05-01',
			'eventDisplay' => 'custom',
		));
		$this->assertEquals($post_id, reset($results));

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => TribeEvents::POSTTYPE,
			'fields' => 'ids',
			'tribe_events' => 'test-permalinks', // this will be present for a normal request
			'name' => 'test-permalinks',
			'eventDate' => '2014-05-08',
			'eventDisplay' => 'custom',
		));
		$this->assertEquals($child_id, reset($results));
	}

	public function test_tribeHideRecurrence_queries() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => __CLASS__,
			'post_name' => 'test-tribeHideRecurrence',
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
				'end-count' => 8,
				'type' => 'Every Week',
			)
		);
		$post_id = TribeEventsAPI::createEvent($event_args);

		$children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'fields' => 'ids',
			'posts_per_page' => 10,
		));

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => TribeEvents::POSTTYPE,
			'fields' => 'ids',
			'tribeHideRecurrence' => 1,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));
		$this->assertCount(1, $results);
		$this->assertEquals($post_id, reset($results));

		$results = $query->query(array(
			'post_type' => TribeEvents::POSTTYPE,
			'fields' => 'ids',
			'tribeHideRecurrence' => 1,
			'start_date' => '2014-06-01',
			'eventDisplay' => 'custom',
		));
		$this->assertCount(1, $results);
		$this->assertEquals($children[4], reset($results));

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => TribeEvents::POSTTYPE,
			'fields' => 'ids',
			'tribeHideRecurrence' => 0,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));
		$this->assertCount(8, $results);

		$option = tribe_get_option( 'hideSubsequentRecurrencesDefault', false );
		tribe_update_option( 'hideSubsequentRecurrencesDefault', TRUE );

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => TribeEvents::POSTTYPE,
			'fields' => 'ids',
			'tribeHideRecurrence' => 0,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));
		$this->assertCount(8, $results);

		tribe_update_option( 'hideSubsequentRecurrencesDefault', $option );
	}

	public function test_child_post_comments() {
		$_SERVER['REMOTE_ADDR'] = null; // to avoid PHP notice

		// we need an admin user to bypass comment flood protection
		$author_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$old_current_user = get_current_user_id();
		wp_set_current_user( $author_id );

		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => __CLASS__,
			'post_name' => 'test-tribeHideRecurrence',
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
				'end-count' => 8,
				'type' => 'Every Week',
			)
		);
		$post_id = TribeEventsAPI::createEvent($event_args);

		$children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $post_id,
			'fields' => 'ids',
			'posts_per_page' => 10,
		));

		$comment_id = wp_new_comment(array(
			'comment_post_ID' => $post_id,
			'comment_author' => 'Comment Author',
			'comment_author_url' => '',
			'comment_author_email' => 'test@example.com',
			'comment_type' => '',
			'comment_content' => 'This is a comment on '.$post_id,
		));
		$comment = get_comment($comment_id);
		$this->assertEquals( $post_id, $comment->comment_post_ID );


		$comment_id = wp_new_comment(array(
			'comment_post_ID' => $children[2],
			'comment_author' => 'Comment Author',
			'comment_author_url' => '',
			'comment_author_email' => 'test@example.com',
			'comment_type' => '',
			'comment_content' => 'This is a comment on '.$children[2],
		));
		$comment = get_comment($comment_id);
		$this->assertEquals( $post_id, $comment->comment_post_ID );

		$comments = get_comments(array(
			'post_id' => $post_id,
		));
		$this->assertCount( 2, $comments );

		$comments = get_comments(array(
			'post_id' => $children[1],
		));
		$this->assertCount( 2, $comments );


		wp_set_current_user( $old_current_user );
	}
}
 