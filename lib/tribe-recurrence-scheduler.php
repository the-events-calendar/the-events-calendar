<?php

/**
 * Class TribeEventsRecurrenceScheduler
 *
 * For recurring events with too many instances, constrain them
 * to only create instances within a configured time around
 * the current date. Use cron to add/remove instances
 * on a rolling basis.
 */
class TribeEventsRecurrenceScheduler {
	const CRON_HOOK = 'tribe-recurrence-cron';

	private $range_before = 24; // months
	private $range_after = 24; // months
	private $earliest_date = '1970-01-01';
	private $latest_date = '2999-12-31';

	public function __construct( $range_before, $range_after ) {
		$this->range_before = $range_before;
		$this->range_after  = $range_after;

		$today               = date( 'Y-m-d', current_time( 'timestamp' ) );
		$this->earliest_date = date( 'Y-m-d', strtotime( $today . ' -' . $this->range_before . 'months' ) );
		$this->latest_date   = date( 'Y-m-d', strtotime( $today . ' +' . $this->range_after . 'months' ) );
	}

	public function get_latest_date() {
		return $this->latest_date;
	}

	public function get_earliest_date() {
		return $this->earliest_date;
	}

	public function add_hooks() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::CRON_HOOK );
		}
		add_action( self::CRON_HOOK, array( $this, 'schedule_future_recurring_events' ), 20, 0 );
	}

	public function remove_hooks() {
		remove_action( self::CRON_HOOK, array( $this, 'schedule_future_recurring_events' ), 20, 0 );
	}

	public function clean_up_old_recurring_events() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} m LEFT JOIN {$wpdb->posts} p ON p.ID=m.post_id WHERE p.post_parent <> 0 AND m.meta_key='_EventStartDate' AND m.meta_value < %s", $this->earliest_date ) );
		foreach ( $post_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}

	public function schedule_future_recurring_events() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT m.post_id FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON m.post_id=p.ID WHERE m.meta_key='_EventNextPendingRecurrence' AND m.meta_value < %s AND p.post_parent = 0", $this->latest_date ) );
		foreach ( $post_ids as $post_id ) {
			TribeEventsRecurrenceMeta::save_pending_events( $post_id );
		}
	}
}
