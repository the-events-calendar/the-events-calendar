<?php


/**
 * Class Tribe__Events__Pro__Recurrence__Scheduler
 *
 * For recurring events with too many instances, constrain them
 * to only create instances within a configured time around
 * the current date. Use cron to add/remove instances
 * on a rolling basis.
 */
class Tribe__Events__Pro__Recurrence__Scheduler {

	const CRON_HOOK = 'tribe-recurrence-cron';
	public static $default_before_range = 24;
	public static $default_after_range  = 24;
	protected     $today;

	private $range_before  = 24; // months
	private $range_after   = 24; // months
	private $earliest_date = '1970-01-01';
	private $latest_date   = '2999-12-31';

	public function __construct( $range_before = null, $range_after = null ) {
		$this->range_before = ( is_numeric( $range_before ) && intval( $range_before ) >= 0 ) ? intval( $range_before ) : self::$default_before_range;
		$this->range_after  = ( is_numeric( $range_after ) && intval( $range_after ) >= 0 ) ? intval( $range_after ) : self::$default_after_range;

		$this->today = date( 'Y-m-d', current_time( 'timestamp' ) );
		$this->set_earliest_date();
		$this->set_latest_date();
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
		add_action( self::CRON_HOOK, array(
			$this,
			'clean_up_old_recurring_events'
		), 10, 0 );
		add_action( self::CRON_HOOK, array(
			$this,
			'schedule_future_recurring_events'
		), 20, 0 );
		add_action( 'tribe_events_pro_blog_deactivate', array(
			$this,
			'clear_scheduled_task'
		) );
	}

	public function remove_hooks() {
		remove_action( self::CRON_HOOK, array(
			$this,
			'clean_up_old_recurring_events'
		), 10, 0 );
		remove_action( self::CRON_HOOK, array(
			$this,
			'schedule_future_recurring_events'
		), 20, 0 );
	}

	public function clear_scheduled_task() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	public function clean_up_old_recurring_events() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$sql = "
			SELECT
				DISTINCT post_id
			FROM
				{$wpdb->postmeta} m
				LEFT JOIN {$wpdb->posts} p ON p.ID = m.post_id
			WHERE
				p.post_parent <> 0
				AND p.post_type = %s
				AND m.meta_key= '_EventStartDate'
				AND m.meta_value < %s
		";

		$args = array(
			'post_type'     => Tribe__Events__Main::POSTTYPE,
			'earliest_date' => $this->earliest_date,
		);

		$sql  = apply_filters( 'tribe_events_pro_clean_up_old_recurring_events_sql', $sql );
		$args = apply_filters( 'tribe_events_pro_clean_up_old_recurring_events_sql_args', $args );

		$post_ids = $wpdb->get_col( $wpdb->prepare( $sql, $args ) );
		foreach ( $post_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}

	public function schedule_future_recurring_events() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT m.post_id FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON m.post_id=p.ID WHERE m.meta_key='_EventNextPendingRecurrence' AND m.meta_value < %s AND p.post_parent = 0", $this->latest_date ) );
		foreach ( $post_ids as $post_id ) {
			Tribe__Events__Pro__Recurrence__Meta::save_pending_events( $post_id );
		}
	}

	public function get_before_range() {
		return $this->range_before;
	}

	public function get_after_range() {
		return $this->range_after;
	}

	public function set_before_range( $range ) {
		if ( is_numeric( $range ) && intval( $range ) >= 0 ) {
			$this->range_before = intval( $range );
			$this->set_earliest_date();
		}
	}

	public function set_after_range( $range ) {
		if ( is_numeric( $range ) && intval( $range ) >= 0 ) {
			$this->range_after = intval( $range );
			$this->set_latest_date();
		};
	}

	protected function set_earliest_date() {
		$this->earliest_date = date( 'Y-m-d', strtotime( $this->today . ' -' . $this->range_before . 'months' ) );
	}

	protected function set_latest_date() {
		$this->latest_date = date( 'Y-m-d', strtotime( $this->today . ' +' . $this->range_after . 'months' ) );
	}
}
