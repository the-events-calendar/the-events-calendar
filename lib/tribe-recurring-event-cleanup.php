<?php

/**
 * Converts recurring events to single instances
 * and back when pro plugin is activated or
 * deactivated
 */
class TribeRecurringEventCleanup {
	private $recurring = false;

	public function __construct() {
		$this->recurring = apply_filters( 'tribe_enable_recurring_event_queries', $this->recurring );
	}

	/**
	 * Modify the database appropriately to reflect the current
	 * recurring events status
	 */
	public function toggle_recurring_events() {
		$current_status = tribe_get_option( 'recurring_events_are_hidden', false );
		if ( $current_status == 'hidden' && $this->recurring ) {
			$this->restore_hidden_events();
			tribe_update_option( 'recurring_events_are_hidden', 'exposed' );
		} elseif ( $current_status == 'exposed' && ! $this->recurring ) {
			$this->hide_recurring_events();
			tribe_update_option( 'recurring_events_are_hidden', 'hidden' );
		} elseif ( ! $current_status ) {
			tribe_update_option( 'recurring_events_are_hidden', ( $this->recurring ? 'exposed' : 'hidden' ) );
		}
	}

	/**
	 * Convert hidden instances back to normal start dates
	 */
	private function restore_hidden_events() {
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->postmeta} SET meta_key='_EventStartDate' WHERE meta_key='_HiddenEventStartDate'" );
	}

	/**
	 * Convert all but the first instance of a recurring event
	 * to a hidden start date
	 *
	 * Reference for the subqueries: http://bugs.mysql.com/bug.php?id=21262
	 */
	private function hide_recurring_events() {
		global $wpdb;
		$sql = "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key='_EventStartDate' AND post_id IN (
		  SELECT post_id from ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_EventStartDate' GROUP BY post_id HAVING COUNT(meta_key) > 1 ) a
		) AND meta_id NOT IN (
		  SELECT meta_id FROM ( SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key='_EventStartDate' GROUP BY post_id HAVING MIN(CAST(meta_value AS DATETIME)) ) b
		)";
		$ids = $wpdb->get_col( $sql );
		if ( $ids ) {
			$sql = sprintf( "UPDATE {$wpdb->postmeta} SET meta_key='_HiddenEventStartDate' WHERE meta_id IN (%s)", implode( ',', array_map( 'intval', $ids ) ) );
		}
		$wpdb->query( $sql );
	}
}