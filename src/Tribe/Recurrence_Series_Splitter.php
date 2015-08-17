<?php

/**
 * Class Tribe__Events__Pro__Recurrence_Series_Splitter
 */
class Tribe__Events__Pro__Recurrence_Series_Splitter {
	/**
	 * @param int $first_event_of_new_series The post ID of the first event of the new series
	 *
	 * @return void
	 */
	public function break_remaining_events_from_series( $first_event_of_new_series ) {
		$post      = get_post( $first_event_of_new_series );
		$parent_id = $post->post_parent;
		if ( empty( $parent_id ) ) {
			return;
		}
		$children = get_posts( array(
			'post_type'      => Tribe__Events__Main::POSTTYPE,
			'post_parent'    => $parent_id,
			'post_status'    => get_post_stati(),
			'meta_key'       => '_EventStartDate',
			'orderby'        => 'meta_key',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'posts_per_page' => - 1,
		) );

		$children_to_move_to_new_series = array();
		$break_date                     = get_post_meta( $first_event_of_new_series, '_EventStartDate', true );
		foreach ( $children as $child_id ) {
			$child_date = get_post_meta( $child_id, '_EventStartDate', true );
			if ( $child_date > $break_date ) {
				$children_to_move_to_new_series[ $child_id ] = $child_date;
			}
		}

		$this->copy_post_meta( $parent_id, $first_event_of_new_series );

		$parent_recurrence = get_post_meta( $parent_id, '_EventRecurrence', true );
		$new_recurrence    = get_post_meta( $first_event_of_new_series, '_EventRecurrence', true );

		$recurrences = Tribe__Events__Pro__Recurrence_Meta::get_recurrence_for_event( $parent_id );
		$earliest_date = strtotime( Tribe__Events__Pro__Recurrence_Meta::$scheduler->get_earliest_date() );
		$latest_date = strtotime( Tribe__Events__Pro__Recurrence_Meta::$scheduler->get_latest_date() );

		$child_movements_by_rule = array();
		foreach ( $parent_recurrence['rules'] as $rule_key => $rule ) {
			if ( empty( $recurrences['rules'][ $rule_key ] ) ) {
				continue;
			}

			$child_movements_by_rule[ $rule_key ] = 0;

			$recurrences['rules'][ $rule_key ]->setMinDate( $earliest_date );
			$recurrences['rules'][ $rule_key ]->setMaxDate( $latest_date );
			$dates = $recurrences['rules'][ $rule_key ]->getDates();

			// count the number of child events that are in this rule that are being moved
			foreach ( $children_to_move_to_new_series as $child_id => $child_date ) {
				$child_movements_by_rule[ $rule_key ] += (int) in_array( $child_date, $dates );
			}

			if ( 'After' === $rule['end-type'] ) {
				$parent_recurrence['rules'][ $rule_key ]['end-count'] -= $child_movements_by_rule[ $rule_key ] + 1;
				$new_recurrence['rules'][ $rule_key ]['end-count'] = $child_movements_by_rule[ $rule_key ] + 1;
			} else {
				$parent_recurrence['rules'][ $rule_key ]['end'] = date( 'Y-m-d', strtotime( $break_date ) );
			}
		}

		update_post_meta( $parent_id, '_EventRecurrence', $parent_recurrence );
		update_post_meta( $first_event_of_new_series, '_EventRecurrence', $new_recurrence );
		add_post_meta( $first_event_of_new_series, '_EventOriginalParent', $parent_id );

		if ( ( count( $children ) - count( $children_to_move_to_new_series ) ) == 1 ) {
			delete_post_meta( $parent_id, '_EventRecurrence' );
		}

		$new_parent                = get_post( $first_event_of_new_series );
		$new_parent->post_parent   = 0;
		$new_parent->comment_count = 0;
		wp_update_post( $new_parent );
		foreach ( $children_to_move_to_new_series as $child_id ) {
			$child                = get_post( $child_id );
			$child->post_parent   = $first_event_of_new_series;
			$child->comment_count = 0;
			wp_update_post( $child );
		}
	}

	/**
	 * @param int $first_event_id The post ID of the first event in the series
	 *
	 * @return void
	 */
	public function break_first_event_from_series( $first_event_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$query = "SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} startDate ON p.ID = startDate.post_id AND startDate.meta_key=%s WHERE p.post_parent=%d AND p.post_type=%s ORDER BY startDate.meta_value";
		$query = $wpdb->prepare( $query, '_EventStartDate', $first_event_id, Tribe__Events__Main::POSTTYPE );
		$children = $wpdb->get_col( $query );

		if ( empty( $children ) ) {
			delete_post_meta( $first_event_id, '_EventRecurrence' );

			return;
		}

		$first_child = get_post( reset( $children ) );

		$this->copy_post_meta( $first_event_id, $first_child->ID );
		delete_post_meta( $first_event_id, '_EventRecurrence' );
		add_post_meta( $first_child->ID, '_EventOriginalParent', $first_event_id );

		$new_series_recurrence = get_post_meta( $first_child->ID, '_EventRecurrence', true );
		$new_date = get_post_meta( $first_child->ID, '_EventStartDate', true );
		$new_series_recurrence = $this->maybe_alter_recurrence_end_count( $first_child->ID, $new_series_recurrence, $date );

		update_post_meta( $first_child->ID, '_EventRecurrence', $new_series_recurrence );

		foreach ( $children as $child_id ) {
			$child = get_post( $child_id );
			if ( $child_id == $first_child->ID ) {
				$child->post_parent = 0;
			} else {
				$child->post_parent = $first_child->ID;
			}
			$child->comment_count = 0;
			wp_update_post( $child );
		}
	}

	/**
	 * @param int $event_to_break_out The ID of the event to break out of the series
	 *
	 * @return void
	 */
	public function break_single_event_from_series( $event_to_break_out ) {
		$post      = get_post( $event_to_break_out );
		$parent_id = $post->post_parent;
		if ( empty( $parent_id ) ) {
			$this->break_first_event_from_series( $event_to_break_out );

			return;
		}

		$this->copy_post_meta( $parent_id, $event_to_break_out );
		delete_post_meta( $event_to_break_out, '_EventRecurrence' );
		add_post_meta( $event_to_break_out, '_EventOriginalParent', $parent_id );

		$child_event_date = get_post_meta( $event_to_break_out, '_EventStartDate', true );
		$parent_recurrence = get_post_meta( $parent_id, '_EventRecurrence', true );
		$parent_recurrence = Tribe__Events__Pro__Recurrence_Meta::add_date_exclusion_to_recurrence( $parent_recurrence, $child_event_date );
		$parent_recurrence = $this->maybe_alter_recurrence_end_count( $parent_id, $parent_recurrence, $child_event_date );

		update_post_meta( $parent_id, '_EventRecurrence', $parent_recurrence );

		$post->post_parent   = 0;
		$post->comment_count = 0;
		wp_update_post( $post );
	}

	/**
	 * Alter the Recurrence rule end-count value for appropriate rules (which, in this case have an end-type of "After")
	 * when the given child event date exists within the rule as a valid date
	 *
	 * @param int $parent_id Event ID of the parent recurrence event
	 * @param array $recurrence_meta Collection of recurrence rules for the event
	 * @param string $child_date Date of the child event we are testing for
	 * @param string $action Type of action we are taking on the field: reduce or set. Reduce subtracts, while set overrides the value
	 * @param int $value Value to either subtract or set the end-count to
	 */
	private function maybe_alter_recurrence_end_count( $parent_id, $recurrence_meta, $child_date, $action = 'reduce', $value = 1 ) {
		$recurrences = Tribe__Events__Pro__Recurrence_Meta::get_recurrence_for_event( $parent_id );
		$earliest_date = strtotime( Tribe__Events__Pro__Recurrence_Meta::$scheduler->get_earliest_date() );
		$latest_date = strtotime( Tribe__Events__Pro__Recurrence_Meta::$scheduler->get_latest_date() );

		// if the recurrence rule has an end-type of "After", then we'll need to reduce the number of events it repeats with
		foreach ( $recurrence_meta['rules'] as $rule_key => $rule ) {
			if ( empty( $rule['end-type'] ) || 'After' !== $rule['end-type'] ) {
				continue;
			}

			if ( empty( $recurrences['rules'][ $rule_key ] ) ) {
				continue;
			}

			$recurrences['rules'][ $rule_key ]->setMinDate( $earliest_date );
			$recurrences['rules'][ $rule_key ]->setMaxDate( $latest_date );
			$dates = $recurrences['rules'][ $rule_key ]->getDates();

			if ( ! in_array( $child_date, $dates ) ) {
				continue;
			}

			if ( 'reduce' === $action ) {
				$recurrence_meta['rules'][ $rule_key ]['end-count'] -= $value;
			} else {
				$recurrence_meta['rules'][ $rule_key ]['end-count'] = $value;
			}
		}

		return $recurrence_meta;
	}

	private function copy_post_meta( $original_post, $destination_post ) {
		$copier = new Tribe__Events__Pro__Post_Meta_Copier();
		$copier->copy_meta( $original_post, $destination_post );
	}
}

