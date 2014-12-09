<?php

/**
 * Class TribeEventsPro_RecurrenceSeriesSplitter
 */
class TribeEventsPro_RecurrenceSeriesSplitter {
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
			'post_type'      => TribeEvents::POSTTYPE,
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
				$children_to_move_to_new_series[] = $child_id;
			}
		}

		$this->copy_post_meta( $parent_id, $first_event_of_new_series );

		$parent_recurrence = get_post_meta( $parent_id, '_EventRecurrence', true );
		$new_recurrence    = get_post_meta( $first_event_of_new_series, '_EventRecurrence', true );

		if ( $parent_recurrence['end-type'] == 'After' ) {
			$parent_recurrence['end-count'] -= ( count( $children_to_move_to_new_series ) + 1 );
			$new_recurrence['end-count'] = ( count( $children_to_move_to_new_series ) + 1 );
		} else {
			$parent_recurrence['end'] = date( 'Y-m-d', strtotime( $break_date ) );
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
		$children = get_posts( array(
			'post_type'      => TribeEvents::POSTTYPE,
			'post_parent'    => $first_event_id,
			'post_status'    => 'any',
			'meta_key'       => '_EventStartDate',
			'orderby'        => 'meta_key',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'posts_per_page' => - 1,
		) );

		if ( empty( $children ) ) {
			delete_post_meta( $first_event_id, '_EventRecurrence' );

			return;
		}

		$first_child = get_post( reset( $children ) );

		$this->copy_post_meta( $first_event_id, $first_child->ID );
		delete_post_meta( $first_event_id, '_EventRecurrence' );
		add_post_meta( $first_child->ID, '_EventOriginalParent', $first_event_id );

		$new_series_recurrence = get_post_meta( $first_child->ID, '_EventRecurrence', true );

		if ( $new_series_recurrence['end-type'] == 'After' ) {
			$new_series_recurrence['end-count'] --;
		}

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

		$parent_recurrence                     = get_post_meta( $parent_id, '_EventRecurrence', true );
		$parent_recurrence['excluded-dates'][] = get_post_meta( $event_to_break_out, '_EventStartDate', true );

		if ( $parent_recurrence['end-type'] == 'After' ) {
			$parent_recurrence['end-count'] --;
		}

		update_post_meta( $parent_id, '_EventRecurrence', $parent_recurrence );

		$post->post_parent   = 0;
		$post->comment_count = 0;
		wp_update_post( $post );
	}

	private function copy_post_meta( $original_post, $destination_post ) {
		require_once( dirname( __FILE__ ) . '/tribeeventspro-postmetacopier.php' );
		$copier = new TribeEventsPro_PostMetaCopier();
		$copier->copy_meta( $original_post, $destination_post );
	}
}
 