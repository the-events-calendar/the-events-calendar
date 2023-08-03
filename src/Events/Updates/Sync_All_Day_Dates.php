<?php

namespace TEC\Events\Updates;

class Sync_All_Day_Dates {

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
	 * `wp_postmeta` table start / end times with the new event cut off time.
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
					ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_EventAllDay' AND pm2.`meta_value` IN('1','yes'))
				SET pm1.meta_value = CONCAT(DATE(pm1.meta_value), ' ', %s)
				WHERE pm1.meta_key = '_EventStartDate'
					AND DATE_FORMAT(pm1.meta_value, '%%H:%%i') <> %s", $event_cutoff_time, $event_cutoff_time );

		// mysql query to set the end time to the start time plus the duration on every all day event
		$fix_end_dates =
			"UPDATE $wpdb->postmeta AS pm1
				INNER JOIN $wpdb->postmeta pm2
					ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_EventAllDay' AND pm2.`meta_value` IN('1','yes'))
				INNER JOIN $wpdb->postmeta pm3
					ON (pm1.post_id = pm3.post_id AND pm3.meta_key = '_EventStartDate')
				INNER JOIN $wpdb->postmeta pm4
					ON (pm1.post_id = pm4.post_id AND pm4.meta_key = '_EventDuration')
				SET pm1.meta_value = DATE_ADD(pm3.meta_value, INTERVAL pm4.meta_value SECOND )
				WHERE pm1.meta_key = '_EventEndDate'";
		$wpdb->query( $fix_start_dates );
		$wpdb->query( $fix_end_dates );

		// Do the same updates to the UTC fields.
		// StartDateUTC
		$fix__utc_start_dates =  "UPDATE `$wpdb->postmeta` AS pm1
        INNER JOIN $wpdb->postmeta pm2 ON (pm1.post_id = pm2.post_id
	        AND pm2.meta_key = '_EventAllDay'
	        AND pm2.`meta_value` IN('1','yes'))
        INNER JOIN $wpdb->postmeta pm3 ON pm2.post_id = pm3.post_id
        	AND pm3.meta_key = '_EventTimezone'
        INNER JOIN $wpdb->postmeta pm4 ON pm2.post_id = pm4.post_id
        	AND pm4.meta_key = '_EventStartDate' 
		SET 
		    pm1.meta_value = DATE_FORMAT(CONVERT_TZ(pm4.meta_value, pm3.meta_value, 'UTC'), '%Y-%m-%d %H:%i:%s')
		WHERE
		    pm1.meta_key = '_EventStartDateUTC'
		        AND CONVERT_TZ(pm4.meta_value, pm3.meta_value, 'UTC') IS NOT NULL";
		// EndDateUTC
		$fix_utc_end_dates = "UPDATE `$wpdb->postmeta` AS pm1
        INNER JOIN $wpdb->postmeta pm2 ON (pm1.post_id = pm2.post_id
	        AND pm2.meta_key = '_EventAllDay'
	        AND pm2.`meta_value` IN('1','yes'))
        INNER JOIN $wpdb->postmeta pm3 ON pm2.post_id = pm3.post_id
        	AND pm3.meta_key = '_EventTimezone'
        INNER JOIN $wpdb->postmeta pm4 ON pm2.post_id = pm4.post_id
        	AND pm4.meta_key = '_EventEndDate' 
		SET 
		    pm1.meta_value = DATE_FORMAT(CONVERT_TZ(pm4.meta_value, pm3.meta_value, 'UTC'), '%Y-%m-%d %H:%i:%s')
		WHERE
		    pm1.meta_key = '_EventEndDateUTC'
		        AND CONVERT_TZ(pm4.meta_value, pm3.meta_value, 'UTC') IS NOT NULL";
		$wpdb->query( $fix__utc_start_dates );
		$wpdb->query( $fix_utc_end_dates );

		/**
		 * Hook that fires when we are changing the end of day cut off time.
		 *
		 * @since TBD
		 *
		 * @param string $event_cutoff_time The end of day cut off time.
		 */
		do_action( 'tec_events_end_of_day_cutoff_time_updated', $event_cutoff_time );
	}
}
