<?php

namespace TEC\Events\Updates;

class Sync_UTC {

	/**
	 * Settings callback that updates the start/end time on all day events to match the EOD cutoff.
	 *
	 * @since TBD Moved from Tribe__Events__Main.
	 *
	 * @param string $old_value Old setting value.
	 * @param string $new_value New setting value.
	 *
	 * @see   'update_option_'.Tribe__Main::OPTIONNAME
	 */
	public function on_cutoff_change_fix_all_day_events( $old_value, $new_value ) {
		// avoid notices for missing indices
		$default_value = '00:00';
		if ( empty( $old_value['multiDayCutoff'] ) ) {
			$old_value['multiDayCutoff'] = $default_value;
		}
		if ( empty( $new_value['multiDayCutoff'] ) ) {
			$new_value['multiDayCutoff'] = $default_value;
		}

		if ( $old_value['multiDayCutoff'] == $new_value['multiDayCutoff'] ) {
			// we only want to continue if the EOD cutoff was changed
			return;
		}
		// Was changed, now do updates to sync with the new time.
		$this->fix_all_day_events( $new_value['multiDayCutoff'] . ':00' );
	}

	/**
	 * Will fire off a number of all day event updates, to sync their
	 * start / end times with the new event cut off time.
	 *
	 * @since TBD
	 *
	 * @param string $event_cutoff_time H:i time, e.g. 08:00
	 */
	public function fix_all_day_events( string $event_cutoff_time ) {
		global $wpdb;

		// mysql query to set the start times on all day events to the EOD cutoff
		// this will fix all day events with any start time
		$fix_start_dates = $wpdb->prepare( "UPDATE $wpdb->postmeta AS pm1
				INNER JOIN $wpdb->postmeta pm2
					ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_EventAllDay' AND pm2.`meta_value` = 'yes')
				SET pm1.meta_value = CONCAT(DATE(pm1.meta_value), ' ', %s)
				WHERE pm1.meta_key = '_EventStartDate'
					AND DATE_FORMAT(pm1.meta_value, '%%H:%%i') <> %s", $event_cutoff_time, $event_cutoff_time );

		// mysql query to set the end time to the start time plus the duration on every all day event
		$fix_end_dates =
			"UPDATE $wpdb->postmeta AS pm1
				INNER JOIN $wpdb->postmeta pm2
					ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_EventAllDay' AND pm2.meta_value = 'yes')
				INNER JOIN $wpdb->postmeta pm3
					ON (pm1.post_id = pm3.post_id AND pm3.meta_key = '_EventStartDate')
				INNER JOIN $wpdb->postmeta pm4
					ON (pm1.post_id = pm4.post_id AND pm4.meta_key = '_EventDuration')
				SET pm1.meta_value = DATE_ADD(pm3.meta_value, INTERVAL pm4.meta_value SECOND )
				WHERE pm1.meta_key = '_EventEndDate'"; // @todo our wherestatement should only apply to multi + all
		$wpdb->query( $fix_start_dates );
		$wpdb->query( $fix_end_dates );

		/**
		 * Hook that fires when we are changing the end of day cut off time.
		 *
		 * @since TBD
		 *
		 * @param string $event_cutoff_time The end of day cut off time.
		 */
		do_action( 'tec_events_end_of_day_cutoff_time_updated', $event_cutoff_time );


		/**
		 * Sync UTC dates in separate batches, since we can not easily mass update these
		 * due to potential timezone calculations per event.
		 *
		 * @since TBD
		 *
		 * @param array $repository_args The repository args to search for.
		 */
		do_action( 'tec_events_sync_utc_dates', [ 'all_day' => true ] );
	}

	/**
	 * Will recurse and send an async call to update a batch of events based on the passed repository args.
	 *
	 * @since TBD
	 *
	 * @param array $repository_args The repository search args for the events to fetch for UTC sync.
	 * @param int   $iteration       This is used as a way to track how many times we recurse and exit out.
	 */
	public function async_sync_utc_dates( array $repository_args, int $iteration = 0 ) {
		// Paginate query, using original filter + pagination logic.
		$repository_args['paged'] = $repository_args['paged'] ?? 0;
		$per_page                 = 50;
		$repository               = tribe_events()->by_args( $repository_args )
		                                          ->per_page( $per_page )
		                                          ->order_by( 'ID' )
		                                          ->order( 'DESC' );
		// Do our sync updates.
		$repository->sync_utc_dates();
		$found = $repository->found();
		// We may have more, go to next page
		if ( $found >= $per_page && $iteration < 100 ) {
			$repository_args['paged'] ++;
			wp_schedule_single_event( time() + 5, 'tec_events_sync_utc_dates', [ $repository_args, ++ $iteration ] );
		}
		// Bug? We should not hit that many...
		if ( $iteration === 200 ) {
			do_action( 'tribe_log', 'error', "Sync UTC dates hit max iterations ($iteration).", [
				'source' => __METHOD__ . ' ' . __LINE__,
				'args'   => $repository_args,
			] );
		}
	}
}