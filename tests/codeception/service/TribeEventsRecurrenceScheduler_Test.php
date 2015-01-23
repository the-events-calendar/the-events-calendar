<?php

/**
 * Class TribeEventsRecurrenceScheduler_Test
 *
 * @group pro
 * @group recurrence
 */
class TribeEventsRecurrenceScheduler_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		Tribe__Events__Pro__Recurrence_Meta::reset_scheduler();
	}

	public function test_default_settings() {
		$scheduler = Tribe__Events__Pro__Recurrence_Meta::get_scheduler();
		$today = date('Y-m-d');
		$two_years_ago = date('Y-m-d', strtotime('24 months ago'));
		$two_years_from_now = date('Y-m-d', strtotime('+24 months'));

		$this->assertEquals(intval(substr($two_years_from_now, 0, 4)) - intval(substr($today, 0, 4)), 2);
		$this->assertEquals(intval(substr($today, 0, 4)) - intval(substr($two_years_ago, 0, 4)), 2);

		$this->assertEquals($two_years_ago, $scheduler->get_earliest_date());
		$this->assertEquals($two_years_from_now, $scheduler->get_latest_date());
	}

	public function test_zeros() {
		tribe_update_option('recurrenceMaxMonthsBefore', 0);
		tribe_update_option('recurrenceMaxMonthsAfter', 0);
		Tribe__Events__Pro__Recurrence_Meta::reset_scheduler();
		$scheduler = Tribe__Events__Pro__Recurrence_Meta::get_scheduler();
		$today = date('Y-m-d');
		$this->assertEquals($today, $scheduler->get_earliest_date());
		$this->assertEquals($today, $scheduler->get_latest_date());
	}

	/**
	 * @ignore
	 */
	public function test_cleanup() {
		$start_date = date('Y-m-d', strtotime('1 year ago'));
		$post_id = Tribe__Events__API::createEvent(array(
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
				'end-count' => 500,
				'type' => 'Every Week',
			)
		));

		/** @var wpdb $wpdb */
		global $wpdb;
		$instances = $wpdb->get_col($wpdb->prepare("SELECT meta_value, post_id FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID=m.post_id AND (p.post_parent=%d OR p.ID=%d) WHERE meta_key='_EventStartDate' AND meta_value < %s", $post_id, $post_id, date('Y-m-d')));
		$this->assertGreaterThan(50, count($instances));

		tribe_update_option('recurrenceMaxMonthsBefore', 6);
		Tribe__Events__Pro__Recurrence_Meta::reset_scheduler();
		$scheduler = Tribe__Events__Pro__Recurrence_Meta::get_scheduler();
		$scheduler->clean_up_old_recurring_events();

		$instances = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID=m.post_id AND (p.post_parent=%d OR p.ID=%d) WHERE meta_key='_EventStartDate' AND meta_value < %s", $post_id, $post_id, date('Y-m-d')));
		$this->assertLessThan(30, count($instances));

		// the first instance should always remain
		$this->assertContains($start_date.' 16:00:00', $instances);
	}

	public function test_future_scheduling() {
		tribe_update_option('recurrenceMaxMonthsAfter', 6);
		Tribe__Events__Pro__Recurrence_Meta::reset_scheduler();
		$start_date = date('Y-m-d');
		$post_id = Tribe__Events__API::createEvent(array(
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
				'end-count' => 500,
				'type' => 'Every Week',
			)
		));

		/** @var wpdb $wpdb */
		global $wpdb;
		$instances = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID=m.post_id AND (p.post_parent=%d OR p.ID=%d) WHERE meta_key='_EventStartDate'", $post_id, $post_id));
		$this->assertLessThan(30, count($instances));

		tribe_update_option('recurrenceMaxMonthsAfter', 8);
		Tribe__Events__Pro__Recurrence_Meta::reset_scheduler();
		$scheduler = Tribe__Events__Pro__Recurrence_Meta::get_scheduler();
		$scheduler->schedule_future_recurring_events();

		$instances = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID=m.post_id AND (p.post_parent=%d OR p.ID=%d) WHERE meta_key='_EventStartDate'", $post_id, $post_id));
		$this->assertGreaterThan(30, count($instances));
		$this->assertLessThan(40, count($instances));
	}

	/**
	 * This tests for a bug from issue #22420
	 */
	public function ignore_test_update_with_recurrence() {
		$start_date = date('Y-m-d');
		$expected_dates = array($start_date.' 16:00:00');
		for ( $i = 1 ; $i < 5 ; $i++ ) {
			$expected_dates[] = date('Y-m-d 16:00:00', strtotime($start_date.' +'.$i.' weeks'));
		}
		$post_id = Tribe__Events__API::createEvent(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'post_type' => Tribe__Events__Events::POSTTYPE,
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

		$instances = get_post_meta($post_id, '_EventStartDate', FALSE);
		$this->assertEqualSets($expected_dates, $instances);

		// now change the order of the meta entries in the DB
		// this replicates a db that might be configured to return in arbitrary order
		delete_post_meta($post_id, '_EventStartDate');
		$rearranged = array($instances[1], $instances[0], $instances[2], $instances[3], $instances[4]);
		foreach ( $rearranged as $date ) {
			add_post_meta($post_id, '_EventStartDate', $date);
		}

		// according to the bug, when we update the event, the second instance will disappear
		$instances = get_post_meta($post_id, '_EventStartDate', FALSE);
		$this->assertEqualSets($expected_dates, $instances);
		Tribe__Events__API::updateEvent($post_id, array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__.' (updating)',
			'post_type' => Tribe__Events__Events::POSTTYPE,
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

		$instances = get_post_meta($post_id, '_EventStartDate', FALSE);
		$this->assertEqualSets($expected_dates, $instances);

	}
}
