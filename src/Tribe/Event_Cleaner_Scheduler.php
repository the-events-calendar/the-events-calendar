<?php
/**
 * Class Event_Cleaner_Scheduler
 *
 * Uses cron to move old events to trash and/or permanently delete them.
 *
 * @since 4.6.13
 */
class Tribe__Events__Event_Cleaner_Scheduler {

	/**
	 * The name of the cron event to permanently delete past events.
	 * @static $del_cron_hook
	 */
	public static $del_cron_hook = 'tribe_del_event_cron';

	/**
	 * The name of the cron event to move past events to trash.
	 * @static $trash_cron_hook
	 */
	public static $trash_cron_hook = 'tribe_trash_event_cron';

	/**
	 * The new value for the $key_trash_events option.
	 *
	 * @var $trash_new_date
	 *
	 * @since 4.6.13
	 */
	public $trash_new_date;

	/**
	 * The new value for the $key_delete_events option.
	 *
	 * @var $del_new_date
	 *
	 * @since 4.6.13
	 */
	public $del_new_date;

	/**
	 * Receives the existing values for $key_trash_events and $key_delete_events options
	 * and defines them as trash_new_date and del_new_date variables.
	 *
	 * @param mixed $move_to_trash
	 * @param mixed $permanently_delete
	 *
	 * @since 4.6.13
	 */
	public function __construct( $move_to_trash = null, $permanently_delete = null ) {
		$this->trash_new_date = $move_to_trash;
		$this->del_new_date   = $permanently_delete;
	}

	/**
	 * Receives the new user-defined value for $key_trash_events option
	 * and defines it as the trash_new_date variable.
	 *
	 * @param mixed $trash_new_value - the value for the $key_trash_events option
	 *
	 * @since 4.6.13
	 */
	public function set_trash_new_date( $trash_new_value ) {
		$this->trash_new_date = $trash_new_value;
	}

	/**
	 * Receives the new user-defined value for $key_delete_events option
	 * and defines it as the del_new_date variable.
	 *
	 * @param mixed $del_new_value - the value for the $key_delete_events option
	 *
	 * @since 4.6.13
	 */
	public function set_delete_new_date( $del_new_value ) {
		$this->del_new_date = $del_new_value;
	}

	/**
	 * Schedules the hooks to delete and move old events to trash
	 * These hooks will be executed daily.
	 *
	 * @since 4.6.13
	 */
	public function add_hooks() {
		if ( ! wp_next_scheduled( self::$trash_cron_hook ) && $this->trash_new_date != null ) {
			/**
			 * Allows adjusting the frequency the trash old events cron will run.
			 *
			 * @since 6.0.13
			 *
			 * @param string The frequency that the trash old events cleaner will run. Defaults to `twicedaily`.
			 */
			$frequency = apply_filters( 'tec_events_event_cleaner_trash_cron_frequency', 'twicedaily' );
			wp_schedule_event( time(), $frequency, self::$trash_cron_hook );
		}

		if ( ! wp_next_scheduled( self::$del_cron_hook ) && $this->del_new_date != null ) {
			wp_schedule_event( time(), 'daily', self::$del_cron_hook );
		}

		if ( null != $this->trash_new_date ) {
			add_action( self::$trash_cron_hook, [ $this, 'move_old_events_to_trash' ], 10, 0 );
		}

		if ( null != $this->del_new_date ) {
			add_action( self::$del_cron_hook, [ $this, 'permanently_delete_old_events' ], 10, 0 );
		}

		add_action( 'tribe_events_blog_deactivate', [ $this, 'trash_clear_scheduled_task' ] );
		add_action( 'tribe_events_blog_deactivate', [ $this, 'delete_clear_scheduled_task' ] );
	}

	/**
	 * Removes the hooks
	 *
	 * @since 4.6.13
	 */
	public function remove_hooks() {
		remove_action( self::$trash_cron_hook, [ $this, 'move_old_events_to_trash' ] );
		remove_action( self::$del_cron_hook, [ $this, 'permanently_delete_old_events' ] );
	}

	/**
	 * Un-schedules all previously-scheduled cron jobs for tribe_trash_event_cron
	 *
	 * @since 4.6.13
	 */
	public function trash_clear_scheduled_task() {
		wp_clear_scheduled_hook( self::$trash_cron_hook );
	}

	/**
	 * Un-schedules all previously-scheduled cron jobs for tribe_del_event_cron
	 *
	 * @since 4.6.13
	 */
	public function delete_clear_scheduled_task() {
		wp_clear_scheduled_hook( self::$del_cron_hook );
	}

	/**
	 * Selects events to be moved to trash or permanently deleted.
	 *
	 * @since 4.6.13
	 * @since 6.0.13 Now batches each purge. By default, it limits to 15 occurrences.
	 * @since 6.2.9  Add an optional 'frequency|interval' format for the events to retrieve field, e.g. '15|MINUTE'.
	 *
	 * @param int $month - The value chosen by user to purge all events older than x months
	 *
	 * @return array $post_ids - an array of event Post_IDs with the Event End Date older than $month
	 */
	public function select_events_to_purge( $month ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// An optional 'frequency|interval' format for the events to retrieve field, e.g. '15|MINUTE'.
		$frequency_struct = explode( '|', $month );
		$frequency        = $frequency_struct[0];
		$interval         = $frequency_struct[1] ?? 'MONTH';

		$event_post_type = Tribe__Events__Main::POSTTYPE;

		$posts_with_parents_sql = "
			SELECT DISTINCT post_parent
			FROM {$wpdb->posts}
			WHERE
				post_type= '$event_post_type'
				AND post_parent <> 0
		";

		$sql = "
			SELECT post_id
			FROM {$wpdb->posts} AS t1
			INNER JOIN {$wpdb->postmeta} AS t2 ON t1.ID = t2.post_id
			WHERE
				t1.post_type = " . '"%1$s"' . "
				AND t2.meta_key = '_EventEndDate'
				AND t2.meta_value <= DATE_SUB( CURRENT_TIMESTAMP(), INTERVAL " . '%2$d %4$s' . " )
				AND t2.meta_value != 0
				AND t2.meta_value != ''
				AND t2.meta_value IS NOT NULL
				AND t1.post_parent = 0
				AND t1.ID NOT IN ( $posts_with_parents_sql )
			LIMIT " . '%3$d';

		/**
		 * Filter - Allows users to manipulate the cleanup query
		 *
		 * @since 4.6.13
		 * @since 6.0.13 Added a limit param to the default query.
		 * @since 6.2.9  Added a mysql `interval` parameter (e.g. 'MONTH' or 'MINUTE'), to go in hand with the `date` field.
		 *
		 * @param string $sql - The query statement.
		 */
		$sql = apply_filters( 'tribe_events_delete_old_events_sql', $sql );

		$args = [
			'post_type' => esc_sql( $event_post_type ),
			'date'      => $frequency,
			'limit'     => 15,
			'interval'  => esc_sql( $interval ),
		];

		/**
		 * Filter - Allows users to modify the query's placeholders
		 *
		 * @since 4.6.13
		 * @since 6.0.13 Added a limit arg, defaulting to 100.
		 * @since 6.2.9  Added a mysql `interval` field (e.g. 'MONTH' or 'MINUTE'), to go in hand with the `date` field.
		 *
		 * @param array $args - The array of variables.
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
	 * @since 4.6.13
	 * @since 6.0.13 Added a return value, and suspends Tribe__Events__Dates__Known_Range::rebuild_known_range() until batch is complete.
	 * @since 6.15.14 Added a filter to skip updating the series post status when cleaning old events.
	 *
	 * @return array<string,WP_Post|false|null> An associative array of ID to the result of wp_trash_post().
	 */
	public function move_old_events_to_trash(): array {
		// When running maintenance, we want to keep the Series post unchanged.
		add_filter( 'tec_events_skip_updating_series_status', '__return_true' );

		$month    = $this->trash_new_date;
		$post_ids = $this->select_events_to_purge( $month );
		$results  = [];

		if ( empty( $post_ids ) ) {
			return $results;
		}

		$this->unhook_rebuild_known_range();
		foreach ( $post_ids as $post_id ) {
			$results[ $post_id ] = wp_trash_post( $post_id );
			clean_post_cache( $post_id );
		}
		Tribe__Events__Dates__Known_Range::instance()->rebuild_known_range();
		$this->hook_rebuild_known_range();
		
		remove_filter( 'tec_events_skip_updating_series_status', '__return_true' );

		return $results;
	}

	/**
	 * Will add the hooks for the Tribe__Events__Dates__Known_Range::rebuild_known_range() callbacks.
	 *
	 * @since 6.0.13
	 */
	public function hook_rebuild_known_range() {
		add_action( 'save_post_' . Tribe__Events__Main::POSTTYPE, [
			Tribe__Events__Dates__Known_Range::instance(),
			'maybe_update_known_range'
		] );
		add_action( 'delete_post', [
			Tribe__Events__Dates__Known_Range::instance(),
			'maybe_rebuild_known_range'
		] );
	}

	/**
	 * Will remove the hooks for the Tribe__Events__Dates__Known_Range::rebuild_known_range() callbacks.
	 *
	 * @since 6.0.13
	 */
	public function unhook_rebuild_known_range() {
		remove_action( 'save_post_' . Tribe__Events__Main::POSTTYPE, [
			Tribe__Events__Dates__Known_Range::instance(),
			'maybe_update_known_range'
		] );
		remove_action( 'delete_post', [
			Tribe__Events__Dates__Known_Range::instance(),
			'maybe_rebuild_known_range'
		] );
	}

	/**
	 * Permanently deletes events that ended before a date specified by user
	 *
	 * @since 4.6.13
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
