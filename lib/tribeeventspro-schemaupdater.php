<?php

/**
 * Class TribeEventsPro_SchemaUpdater
 */
class TribeEventsPro_SchemaUpdater {
	const SCHEMA_VERSION = '3.5';

	private function do_updates() {
		set_time_limit( 0 );
		if ( $this->is_version_in_db_less_than( '3.5' ) ) {
			$this->update_3_5();
		}
		tribe_update_option( 'pro-schema-version', self::SCHEMA_VERSION );
	}

	private function is_version_in_db_less_than( $version ) {
		$version_in_db = tribe_get_option( 'pro-schema-version', 0 );
		if ( version_compare( $version, $version_in_db ) > 0 ) {
			return true;
		}

		return false;
	}

	private function update_3_5() {
		$this->recurring_events_from_meta_to_child_posts();
	}

	/**
	 * Update recurring events to use multiple posts for events
	 * in a series
	 *
	 * @return void
	 */
	private function recurring_events_from_meta_to_child_posts() {
		$post_ids = $this->get_recurring_events_still_using_meta_storage();
		foreach ( $post_ids as $p ) {
			$this->convert_recurring_event_to_child_posts( $p );
		}
	}

	private function get_recurring_events_still_using_meta_storage() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$sql      = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_EventStartDate' GROUP BY post_id HAVING COUNT(meta_key) > 1";
		$post_ids = $wpdb->get_col( $sql );

		return $post_ids;
	}

	private function convert_recurring_event_to_child_posts( $event_id ) {
		$start_dates = get_post_meta( $event_id, '_EventStartDate', false );
		if ( ! is_array( $start_dates ) ) {
			return;
		}
		sort( $start_dates );
		$original    = array_shift( $start_dates );
		$start_dates = array_map( 'strtotime', $start_dates );
		foreach ( $start_dates as $date ) {
			if ( ! empty( $date ) ) {
				$instance = new TribeEventsPro_RecurrenceInstance( $event_id, $date );
				$instance->save();
				delete_post_meta( $event_id, '_EventStartDate', date( 'Y-m-d H:i:s', $date ) );
			}
		}
		delete_post_meta( $event_id, '_EventStartDate' );
		update_post_meta( $event_id, '_EventStartDate', $original );
	}

	public static function update_required() {
		$updater = new self();

		return $updater->is_version_in_db_less_than( self::SCHEMA_VERSION );
	}

	public static function init() {
		$updater = new self();
		$updater->do_updates();
	}
}
 