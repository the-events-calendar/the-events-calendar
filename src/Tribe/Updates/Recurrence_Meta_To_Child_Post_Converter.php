<?php


class Tribe__Events__Pro__Updates__Recurrence_Meta_To_Child_Post_Converter {
	/**
	 * Update recurring events to use multiple posts for events
	 * in a series
	 *
	 * @return void
	 */
	public function do_conversion() {
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


		$original    = array_shift( $start_dates );
		$start_dates = array_map( 'strtotime', $start_dates );

		$prepared_start_dates = array_map( array( $this, 'start_date_to_sequence' ), $start_dates );
		$sequence             = new Tribe__Events__Pro__Recurrence__Sequence( $prepared_start_dates, $event_id );

		foreach ( $sequence->get_sorted_sequence() as $date ) {
			if ( ! empty( $date ) ) {
				set_time_limit( 30 );
				$instance = new Tribe__Events__Pro__Recurrence__Instance( $event_id, $date, 0, $date['sequence'] );
				$instance->save();
				delete_post_meta( $event_id, '_EventStartDate', date( 'Y-m-d H:i:s', $date ) );
			}
		}
		delete_post_meta( $event_id, '_EventStartDate' );
		update_post_meta( $event_id, '_EventStartDate', $original );
	}

	private function start_date_to_sequence( array $start_date ) {
		return array( 'timestamp' => $start_date );
	}
}
