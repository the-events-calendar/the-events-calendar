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

if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {

	/**
	 * Whether there are more calendar days available in the week loop.
	 *
	 * @return boolean
	 */
	function tribe_events_week_have_days() {
		return Tribe__Events__Pro__Templates__Week::have_days();
	}

	/**
	 * Increment the current day loop.
	 *
	 * @return void
	 */
	function tribe_events_week_the_day() {
		Tribe__Events__Pro__Templates__Week::the_day();
	}

	/**
	 * Return current day in the week loop. The array will contain the following elements:
	 *
	 * 'date'           => date formatted Y-m-d
	 * 'day_number'     => 0 - 6; 0 = Sunday, 6 = Saturday
	 * 'formatted_date' => date formatted for display (the format can be changed in events settings)
	 * 'is_today'       => whether the day is today
	 * 'is_past'        => whether the day has passed
	 * 'is_future'      => whether the day is in the future
	 * 'hourly_events'  => an array of the hourly events on this day
	 * 'all_day_events' => an array of the all day events on this day
	 * 'has_events'     => boolean whether there are any events on this day, either all day or hourly
	 *
	 * @return array
	 */
	function tribe_events_week_get_current_day() {
		return apply_filters( 'tribe_events_week_get_current_day', Tribe__Events__Pro__Templates__Week::get_current_day() );
	}

	/**
	 * Check if there are any all day events this week.
	 *
	 * @return boolean
	 */
	function tribe_events_week_has_all_day_events() {

		return apply_filters( 'tribe_events_week_has_all_day_events', array(
			'Tribe__Events__Pro__Templates__Week',
			'has_all_day_events',
		) );

	}

	/**
	 * Return the hours to display on week view. Optionally return formatted, first, or last hour.
	 *
	 * @param string $return Can be 'raw', 'formatted', 'first-hour', or 'last-hour'.
	 *
	 * @return array
	 */
	function tribe_events_week_get_hours( $return = null ) {
		$range = Tribe__Events__Pro__Templates__Week::get_hour_range();
		switch ( $return ) {
			case 'raw':
				return array_keys( $range );
			case 'formatted':
				return array_values( $range );
			case 'first-hour':
				$hours = array_keys( $range );
				return str_pad( reset( $hours ), 2, '0', STR_PAD_LEFT ) . ':00:00';
			case 'last-hour':
				$hours = array_keys( $range );
				return str_pad( end( $hours ), 2, '0', STR_PAD_LEFT ) . ':59:00';

		}

		return apply_filters( 'tribe_events_week_get_hours', $range, $return );
	}

	/**
	 * Return the range of days to display on week view.
	 *
	 * @return array
	 */
	function tribe_events_week_get_days() {
		$days = Tribe__Events__Pro__Templates__Week::get_day_range();
		return apply_filters( 'tribe_events_week_get_days', $days );
		}

	/**
	 * Echo the classes used on each week day header.
	 *
	 * @return void
	 */
	function tribe_events_week_day_header_classes() {
		echo apply_filters( 'tribe_events_week_day_header_classes', Tribe__Events__Pro__Templates__Week::day_header_classes() );
	}

	/**
	 * Return the text used in week day headers wrapped in a <span> tag and data attribute needed for mobile js.
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
	 * Setup css classes for daily columns in week view.
	 *
	 * @return void
	 */
	function tribe_events_week_column_classes() {
		echo apply_filters( 'tribe_events_week_column_classes', Tribe__Events__Pro__Templates__Week::column_classes() );
				}

	/**
	 * Retrieve the current date in Y-m-d format.
	 *
	 * @param boolean $echo Set to false to return the value rather than echo.
	 *
	 * @return string|void
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
	 * Return html attributes required for proper week view js functionality.
	 *
	 * @param int|object $event The event post, defaults to the global post.
	 * @param string $format The format of the returned value. Can be either 'array' or 'string'
	 * @return array|string
	 */
	function tribe_events_week_event_attributes( $event = null, $format = 'string' ) {

		$attrs = Tribe__Events__Pro__Templates__Week::get_event_attributes( $event );

		$attrs = apply_filters( 'tribe_events_week_event_attributes', $attrs );

		if ( $format == 'array' ) {
			$return = $attrs;
		} elseif ( $format == 'string' ) {
			$return = '';
			foreach ( $attrs as $attr => $value ) {
				$return .= " $attr=" . '"' . esc_attr( $value ) . '"';
		}
	}

		return $return;

	}

	/**
	 * Build the previous week link.
	 *
	 * @param string $text The text to be linked.
	 *
	 * @return string
	 */
	function tribe_events_week_previous_link( $text = '' ) {
		try {
			$date = tribe_get_first_week_day();
			if ( $date <= tribe_events_earliest_date( Tribe__Events__Date_Utils::DBDATEFORMAT ) ) {
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
				return sprintf( '<a %s href="%s" rel="prev">%s</a>', $attributes, esc_url( $url ), $text );
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
	function tribe_events_week_next_link( $text = '' ) {
		try {
			$date = date( Tribe__Events__Date_Utils::DBDATEFORMAT, strtotime( tribe_get_first_week_day() . ' +1 week' ) );
			if ( $date >= tribe_events_latest_date( Tribe__Events__Date_Utils::DBDATEFORMAT ) ) {
				return '';
			}

			$url = tribe_get_next_week_permalink();
			if ( empty( $text ) ) {
				$text = __( 'Next Week <span>&raquo;</span>', 'tribe-events-calendar-pro' );
			}

			global $wp_query;
			$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
			$attributes   = sprintf( ' data-week="%s" ', date( 'Y-m-d', strtotime( $current_week . ' +7 days' ) ) );


			return sprintf( '<a %s href="%s" rel="next">%s</a>', $attributes, esc_url( $url ), $text );
		} catch ( OverflowException $e ) {
			return '';
		}
	}
		}
