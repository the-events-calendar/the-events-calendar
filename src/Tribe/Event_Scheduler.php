<?php
/**
 * Class Events_Scheduler
 *
 * Uses cron to move old events to trash and/or permanently delete them.
 */
class Tribe__Events__Event_Scheduler {

	const DEL_CRON_HOOK = 'tribe-del-event-cron';
	const TRASH_CRON_HOOK = 'tribe-trash-event-cron';

	public $trash_new_date;
	public $del_new_date;

	/**
	 * Retrieves the existing values for trashPastEvents and deletePastEvents options
	 * and defines them as trash_new_date and del_new_date variables.
	 *
	 * @param mixed $move_to_trash
	 * @param mixed $permanently_delete
	 *
	 * @since TBD
	 */
	public function __construct( $move_to_trash = null, $permanently_delete = null ) {
		$this->trash_new_date = $move_to_trash;
		$this->del_new_date   = $permanently_delete;
	}

	/**
	 * Retrieves the new user-defined value for trashPastEvents option
	 * and defines it as the trash_new_date variable.
	 *
	 * @param $mixed - the value for the trashPastEvents option
	 *
	 * @since TBD
	 */
	public function trash_set_new_date( $trash_new_value ) {
		$this->trash_new_date = $trash_new_value;
	}

	/**
	 * Retrieves the new user-defined value for deletePastEvents option
	 * and defines it as the del_new_date variable.
	 *
	 * @param $mixed - the value for the deletePastEvents option
	 *
	 * @since TBD
	 */
	public function delete_set_new_date( $del_new_value ) {
		$this->del_new_date = $del_new_value;
	}

	/**
	 * Schedules the hooks to delete and move old events to trash
	 * These hooks will be executed daily.
	 *
	 * @since TBD
	 */
	public function add_hooks() {
		if ( ! wp_next_scheduled( self::TRASH_CRON_HOOK ) && $this->trash_new_date != null ) {
			wp_schedule_event( time(), 'daily', self::TRASH_CRON_HOOK );
		}

		if ( ! wp_next_scheduled( self::DEL_CRON_HOOK ) && $this->del_new_date != null ) {
			wp_schedule_event( time(), 'daily', self::DEL_CRON_HOOK );
		}

		if ( $this->trash_new_date != null ) {
			add_action( self::TRASH_CRON_HOOK, array( $this, 'move_old_events_to_trash' ), 10, 0 );
		}

		if ( $this->del_new_date != null ) {
			add_action( self::DEL_CRON_HOOK, array( $this, 'permanently_delete_old_events' ), 10, 0 );
		}

		add_action( 'tribe_events_blog_deactivate', array( $this, 'trash_clear_scheduled_task' ) );
		add_action( 'tribe_events_blog_deactivate', array( $this, 'delete_clear_scheduled_task' ) );
	}

	/**
	 * Removes the hooks
	 *
	 * @since TBD
	 */
	public function remove_hooks() {
		remove_action( self::TRASH_CRON_HOOK, array( $this, 'move_old_events_to_trash' ), 10, 0 );
		remove_action( self::DEL_CRON_HOOK, array( $this, 'permanently_delete_old_events' ), 10, 0 );
	}

	/**
	 * Un-schedules all previously-scheduled cron jobs for tribe-trash-event-cron
	 *
	 * @since TBD
	 */
	public function trash_clear_scheduled_task() {
		wp_clear_scheduled_hook( self::TRASH_CRON_HOOK );
	}

	/**
	 * Un-schedules all previously-scheduled cron jobs for tribe-del-event-cron
	 *
	 * @since TBD
	 */
	public function delete_clear_scheduled_task() {
		wp_clear_scheduled_hook( self::DEL_CRON_HOOK );
	}

	/**
	 * Selects events to be moved to trash or permanently deleted.
	 *
	 * @since TBD
	 *
	 * @param int $month - The value chosen by user to purge all events older than x months
	 *
	 * @return array $post_ids - an array of event Post_IDs with the Event End Date older than $month
	 */
	public function select_events_to_purge( $month ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$sql = "SELECT post_id
		FROM {$wpdb->posts} AS t1
		INNER JOIN {$wpdb->postmeta} AS t2 ON t1.ID = t2.post_id
		WHERE t1.post_type = %d
			AND t2.meta_key = '_EventEndDate'
			AND t2.meta_value <= DATE_SUB( CURDATE(), INTERVAL %d MONTH )";

		/**
		 * Filter - Allows users to manipulate the cleanup query
		 *
		 * @param string $sql - The query statement
		 *
		 * @since TBD
		 */
		$sql = apply_filters( 'tribe_events_delete_old_events_sql', $sql );

		$args = array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'date'      => $month,
		);

		/**
		 * Filter - Allows users to modify the query's placeholders
		 *
		 * @param array $args - The array of variables
		 *
		 * @since TDB
		 */
		$args = apply_filters( 'tribe_events_delete_old_events_sql_args', $args );

		/**
		 * Returns an array of Post IDs (events) that ended before a specific date
		 */
		$post_ids = $wpdb->get_col( $wpdb->prepare( $sql, $args ) );

		return $post_ids;
	}

	/**
	 * Moves to trash events that ended before a date specified by user
	 *
	 * @since TBD
	 *
	 * @return mixed
	 */
	public function move_old_events_to_trash() {

		$month = $this->trash_new_date;

		$post_ids = $this->select_events_to_purge( $month );

		if ( empty( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {
			wp_trash_post( $post_id );
		}
	}

	/**
	 * Permanently deletes events that ended before a date specified by user
	 *
	 * @since TBD
	 *
	 * @return mixed - The post object (if it was deleted successfully) or false (failure)
	 */
	public function permanently_delete_old_events() {

		$month = $this->del_new_date;

		$post_ids = $this->select_events_to_purge( $month );

		if ( empty( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}

	}
}
