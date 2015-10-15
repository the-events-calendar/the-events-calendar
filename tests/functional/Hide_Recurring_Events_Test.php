<?php
class Tribe_Hide_Recurring_Event_Test extends Tribe__Events__Pro__WP_UnitTestCase {
	/**
	 * Creates a recurring event and confirms that we get the expected range of posts
	 * back when we query with the tribeHideRecurrence flag set.
	 */
	public function test_hides_subsequent_recurring_events() {
		$start_date = date('Y-m-d', strtotime('2014-05-01'));
		$event_args = array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_title' => __FUNCTION__,
			'post_content' => __CLASS__ . ' ' . __FUNCTION__,
			'post_name' => 'test-tribeHideRecurrence',
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
						'end-count' 		=> 8,
					),
				),// end rules array
			)
		);
		$post_id = Tribe__Events__API::createEvent($event_args);

		// process the queue, otherwise all the children won't get created
		Tribe__Events__Pro__Main::instance()->queue_processor->process_queue();

		$children = get_posts(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $post_id,
			'fields' => 'ids',
			'posts_per_page' => 10,
			'orderby' => 'ID',
			'order' => 'ASC',
		));

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));

		$results = $results;
		$this->assertCount(1, $results);
		$this->assertEquals($post_id, reset($results));

		$results = $query->query(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date' => '2014-06-01',
			'eventDisplay' => 'custom',
			'orderby' => 'ID',
			'order' => 'ASC',
		));

		$this->assertCount(1, $results);
		$this->assertNotEmpty($children);
		$this->assertEquals($children[4], reset($results));

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 0,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));

		$this->assertCount(8, $results);

		$option = tribe_get_option( 'hideSubsequentRecurrencesDefault', false );
		tribe_update_option( 'hideSubsequentRecurrencesDefault', TRUE );

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 0,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));

		$this->assertCount(8, $results);

		tribe_update_option( 'hideSubsequentRecurrencesDefault', $option );
	}

}