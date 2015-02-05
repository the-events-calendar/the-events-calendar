<?php
/**
 * Events Calendar Pro Week Template Tags
 *
 * Display functions for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'TribeEventsPro' ) ) {

	/**
	 * Whether there are more calendar days available in the loop.
	 *
	 * @return bool True if calendar days are available, false if end of loop.
	 * @return  void
	 * */
	function tribe_events_week_have_days() {
		return Tribe_Events_Pro_Week_Template::have_days();
	}

	/**
	 * increment the current day loop
	 *
	 * @return void
	 */
	function tribe_events_week_the_day() {
		Tribe_Events_Pro_Week_Template::the_day();
	}

	/**
	 * Return current day in the week loop
	 *
	 * @return array
	 */
	function tribe_events_week_get_current_day() {
		return apply_filters( 'tribe_events_week_get_current_day', Tribe_Events_Pro_Week_Template::get_current_day() );
	}

	/**
	 * Check if there are any all day events this week
	 */
	function tribe_events_week_has_all_day_events() {

		return apply_filters( 'tribe_events_week_has_all_day_events', array(
			'Tribe_Events_Pro_Week_Template',
			'has_all_day_events'
		) );

	}

	/**
	 * Return the hours to display on week view. Optionally return formatted, first, or last hour.
	 *
	 * @param null $format - can be 'raw', 'formatted', 'first-hour', or 'last-hour'
	 *
	 * @return array|mixed|string|void
	 */
	function tribe_events_week_get_hours( $format = null ) {
		$range = Tribe_Events_Pro_Week_Template::get_hour_range();
		switch ( $format ) {
			case 'raw':
				return array_keys( $range );
			case 'formatted':
				return $array_values( $range );
			case 'first-hour':
				$hours = array_keys( $range );
				return str_pad( reset( $hours ) , 2, '0', STR_PAD_LEFT ) . ':00:00';
			case 'last-hour':
				$hours = array_keys( $range );
				return str_pad( end( $hours ), 2, '0', STR_PAD_LEFT ) . ':00:00';

		}

		return apply_filters( 'tribe_events_week_get_hours', $range, $format );
	}

	/**
	 * Return the hours to display on week view. Optionally return formatted, first, or last hour.
	 *
	 * @param null $format - can be 'raw', 'formatted', 'first-hour', or 'last-hour'
	 *
	 * @return array|mixed|string|void
	 */
	function tribe_events_week_get_days( $format = null ) {
		$days = Tribe_Events_Pro_Week_Template::get_day_range();
		return apply_filters( 'tribe_events_week_get_days', $days, $format );
	}

	/**
	 * Return the classes used on each week day
	 *
	 * @return string
	 */
	function tribe_events_week_day_header_classes() {
		echo apply_filters( 'tribe_events_week_day_header_classes', Tribe_Events_Pro_Week_Template::day_header_classes() );
	}

	/**
	 * Return the text used in week day headers
	 * Wrapped in a <span> tag and data attribute needed for mobile js
	 *
	 * @return string
	 */
	function tribe_events_week_day_header() {
		$day  = tribe_events_week_get_current_day();
		$html = '<span data-full-date="' . $day['formatted_date'] . '">' . $day['formatted_date'] . '</span>';

		// if day view is enabled and there are events on the day, make it a link to the day
		if ( tribe_events_is_view_enabled( 'day' ) && $day['has_events'] ) {
			$html = '<a href="' . tribe_get_day_link( tribe_events_week_get_the_date( false ) ) . '" rel="bookmark">' . $html . '</span></a>';
		}

		return apply_filters( 'tribe_events_week_day_header', $html );
	}

	/**
	 * Setup css classes for daily columns in week view
	 *
	 * @return void
	 */
	function tribe_events_week_column_classes() {
		echo apply_filters( 'tribe_events_week_column_classes', Tribe_Events_Pro_Week_Template::column_classes() );
	}

	/**
	 * Return the current day in the week grid loop
	 *
	 * @param boolean $echo
	 *
	 * @return string $html
	 */
	function tribe_events_week_get_the_date( $echo = true ) {
		$day  = tribe_events_week_get_current_day();
		$html = apply_filters( 'tribe_events_week_get_the_date', $day['date'] );
		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Echo up html attributes required for proper week view js functionality
	 *
	 * @return array
	 */
	function tribe_events_the_week_event_attributes($event) {

		$attrs = Tribe_Events_Pro_Week_Template::get_event_attributes( $event );

		$attrs = apply_filters( 'tribe_events_week_event_attributes', $attrs );

		foreach ( $attrs as $attr => $value ) {
			echo " $attr=" . '"' . esc_attr( $value ) . '"';
		}

	}

	/**
	 * Build the previous week link
	 *
	 * @param string $text the text to be linked
	 *
	 * @return string
	 */
	function tribe_previous_week_link( $text = '' ) {
		try {
			$date = tribe_get_first_week_day();
			if ( $date <= tribe_events_earliest_date( TribeDateUtils::DBDATEFORMAT ) ) {
				return '';
			}

			$url = tribe_get_last_week_permalink();
			if ( empty( $text ) ) {
				$text = __( '<span>&laquo;</span> Previous Week', 'tribe-events-calendar-pro' );
			}

			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
			$attributes   = sprintf( ' data-week="%s" ', date( 'Y-m-d', strtotime( $current_week . ' -7 days' ) ) );
			if ( ! empty( $url ) ) {
				return sprintf( '<a %s href="%s" rel="prev">%s</a>', $attributes, $url, $text );
			}
		} catch ( OverflowException $e ) {
			return '';
		}
	}

	/**
	 * Build the next week link
	 *
	 * @param string $text the text to be linked
	 *
	 * @return string
	 */
	function tribe_next_week_link( $text = '' ) {
		try {
			$date = date( TribeDateUtils::DBDATEFORMAT, strtotime( tribe_get_first_week_day() . ' +1 week' ) );
			if ( $date >= tribe_events_latest_date( TribeDateUtils::DBDATEFORMAT ) ) {
				return '';
			}

			$url = tribe_get_next_week_permalink();
			if ( empty( $text ) ) {
				$text = __( 'Next Week <span>&raquo;</span>', 'tribe-events-calendar-pro' );
			}

			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
			$attributes   = sprintf( ' data-week="%s" ', date( 'Y-m-d', strtotime( $current_week . ' +7 days' ) ) );


			return sprintf( '<a %s href="%s" rel="next">%s</a>', $attributes, $url, $text );
		} catch ( OverflowException $e ) {
			return '';
		}
	}



}