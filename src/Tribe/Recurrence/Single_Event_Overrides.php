<?php

/**
 * Hooks into various filters to modify the Single Event output for recurring events
 */
class Tribe__Events__Pro__Recurrence__Single_Event_Overrides {
	/**
	 * constructor!
	 */
	public function __construct() {
		add_filter( 'tribe_events_single_event_time_formatted', array( $this, 'maybe_render_multiple_formatted_times' ), 10, 2 );
		add_filter( 'tribe_events_single_event_time_title', array( $this, 'maybe_render_time_title_as_plural' ), 10, 2 );
	}

	/**
	 * Provides all of the recurring events for the provided date that have the same event parent
	 *
	 * @since 4.0.3
	 *
	 * @param int $event_id Event ID
	 * @param string $date Date to fetch recurring events from
	 *
	 * @return string
	 */
	public function get_recurring_events_for_date( $event_id, $date ) {
		if ( ! ( $event = tribe_events_get_event( $event_id ) ) ) {
			return array();
		}

		$parent_id = empty( $event->post_parent ) ? $event->ID : $event->post_parent;

		$post_status = array( 'publish' );
		if ( is_user_logged_in() ) {
			$post_status[] = 'private';
		}

		$args = array(
			'start_date' => tribe_beginning_of_day( $date ),
			'end_date' => tribe_end_of_day( $date ),
			'post_status' => $post_status,
			'post_parent' => $parent_id,
			'tribeHideRecurrence' => false,
		);

		$events = tribe_get_events( $args );

		return $events;
	}

	/**
	 * Alters the provided heading text for the "Time" section of an event's details to be plural if needed
	 *
	 * @since 4.0.3
	 *
	 * @param string $title Title/label of the Time section of an event's details
	 * @param int $event_id Event ID
	 *
	 * @return string
	 */
	public function maybe_render_time_title_as_plural( $title, $event_id ) {
		if ( ! tribe_is_recurring_event( $event_id ) ) {
			return $title;
		}

		$date = tribe_get_start_date( $event_id, false, Tribe__Date_Utils::DBDATEFORMAT );
		$events = $this->get_recurring_events_for_date( $event_id, $date );

		if ( count( $events ) < 2 ) {
			return $title;
		}

		return __( 'Times:', 'tribe-events-calendar-pro' );
	}

	/**
	 * Alters the provided formatted time to include all recurrence times for the day
	 *
	 * @since 4.0.3
	 *
	 * @param string $formatted_time Formatted time range for the given event
	 * @param int $event_id Event ID
	 *
	 * @return string
	 */
	public function maybe_render_multiple_formatted_times( $formatted_time, $event_id ) {
		if ( ! tribe_is_recurring_event( $event_id ) ) {
			return $formatted_time;
		}

		$date = tribe_get_start_date( $event_id, false, Tribe__Date_Utils::DBDATEFORMAT );
		$time_format = get_option( 'time_format', Tribe__Date_Utils::TIMEFORMAT );
		$time_range_separator = tribe_get_option( 'timeRangeSeparator', ' - ' );

		$events = $this->get_recurring_events_for_date( $event_id, $date );
		$formatted_time = null;

		foreach ( $events as $child ) {
			$start_time = tribe_get_start_date( $child->ID, false, $time_format );
			$end_time = tribe_get_end_date( $child->ID, false, $time_format );

			$formatted_time .= '<div class="tribe-recurring-event-time">';
			if ( $start_time === $end_time ) {
				$formatted_time .= esc_html( $start_time );
			} else {
				$formatted_time .= esc_html( $start_time . $time_range_separator . $end_time );
			}
			$formatted_time .= '</div>';
		}

		return $formatted_time;
	}
}
