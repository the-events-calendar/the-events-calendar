<?php
/**
 * Handles the logic behind the creation of a stack of events like the ones used in the Month, Week and Day views to
 * display multi-day events.
 *
 * A "stack" is an array in this shape:
 * [
 *      <Y-m-d> => [<event_1>, <event_2>, <spacer>, <spacer> ],
 *      <Y-m-d> => [<event_1>, <event_2>, <event_3>, <spacer> ],
 *      <Y-m-d> => [<event_1>, <spacer>, <event_3>, <event_4> ],
 *      <Y-m-d> => [<event_1>, <spacer>, <spacer>, <event_4> ],
 * ]
 *
 * Where each array is a column of the stack. Visually the stack above would render like this (columns to rows):
 *
 * |1|1|1|1|_|
 * |2|2|_|_|_|
 * |_|3|3|_|_|
 * |_|_|_|4|4|
 *
 * Looking at this last representation there is some "wasted" visual space rigth of 2 that might be filled by 4; if the
 * stack is set ot "recycle" space then this would be the representation of the same events:
 *
 * |1|1|1|1|_|
 * |2|2|_|4|4|
 * |_|3|3|_|_|
 *
 * The stack takes now one less row (i.e. the stack columns all have one less element).
 *
 * @since   4.9.7
 * @package Tribe\Events\Views\V2\Utils
 */

namespace Tribe\Events\Views\V2\Utils;
use Tribe__Date_Utils as Dates;

/**
 * Class Stack
 *
 * @since   4.9.7
 * @package Tribe\Events\Views\V2\Utils
 */
class Stack {
	/**
	 * The current day, in the `Y-m-d` format.
	 *
	 * @since 4.9.7
	 *
	 * @var int|string
	 */
	protected $current_day;
	/**
	 * The current day events, a list of event post IDs.
	 *
	 * @since 4.9.7
	 *
	 * @var array
	 */
	protected $day_events;
	/**
	 * An associative array relating each event ID to its current position in the stack.
	 *
	 * @var array
	 */
	protected $stack_positions;
	/**
	 * The current stack.
	 *
	 * @since 4.9.7
	 *
	 * @var array
	 */
	protected $stack;

	/**
	 * Whether to "recycle" the empty stack spaces, where possible, or not..
	 *
	 * @since 4.9.7
	 *
	 * @var bool
	 */
	protected $recycle_space;

	/**
	 * The spacer currently used to mark empty spaces in the stack.
	 *
	 * @since 4.9.7
	 *
	 * @var mixed
	 */
	protected $spacer;

	/**
	 * A flag to indicate whether the stack elements should be normalized or not.
	 *
	 * @since 4.9.7
	 *
	 * @var bool
	 */
	protected $normalize_stack;

	/**
	 * Builds and returns the stack for a group of events, divided by days.
	 *
	 * @since 4.9.7
	 *
	 * @param array      $events_by_day   An array of events, per-day, in the shape `[ <Y-m-d> => [ ...<event_ids> ] ]`.
	 *
	 * @param null|mixed $spacer          The spacer that should be used to indicate an empty space in the stack.
	 *                                    Defaults to the filtered spacer.
	 * @param null|bool  $recycle_space   Whether to recycle spaces or not; defaults to the filtered value.
	 * @param null|bool $normalize_stack  Whether to normalize the stack by padding the bottom of it with spacers or
	 *                                    not; defaults to the filtered value.
	 *
	 * @return array An associative array of days, each with the events "stacked", including spacers, in the shape:
	 *               `[
	 *                  <Y-m-d> => [<event_1>, <event_2>, <spacer> ],
	 *                  <Y-m-d> => [<event_1>, <event_2>, <event_3> ],
	 *                  <Y-m-d> => [<event_1>, <spacer>, <event_3> ],
	 *                ]`
	 *              and so on. Each stack column (a day) will be padded with spacers to have consistent stack height
	 *               which means that all arrays in the stack will have the same length.
	 */
	public function build_from_events( array $events_by_day = [], $spacer = null, $recycle_space = null, $normalize_stack = null ) {
		if ( empty( $events_by_day ) ) {
			return [];
		}

		// @todo @be we use the spacer someplace, refer it to this value.
		$this->spacer          = null !== $spacer ? $spacer : $this->get_spacer();
		$this->recycle_space   = null !== $recycle_space ?
			(bool) $recycle_space
			: $this->should_recycle_spaces( $events_by_day );
		$this->normalize_stack = null !== $normalize_stack ?
			(bool) $normalize_stack
			: $this->should_normalize_stack( $events_by_day );

		// Init the working properties.
		$this->stack           = [];
		$this->stack_positions = [];

		// Make sure all days in the period will make it to the stack; even if empty.
		$events_by_day = $this->add_missing_days( $events_by_day );

		/*
		 * Calculate each multi-day event_id stack position in the stack.
		 */
		foreach ( $events_by_day as $current_day => $the_day_events ) {
			$this->stack[ $current_day ] = $this->build_day_stack( $current_day, $the_day_events );
		}

		if ( $this->normalize_stack ) {
			$this->normalize_stack();
		}

		return $this->stack;
	}

	/**
	 * Returns the "spacer" used to indicate an empty space in the stack.
	 *
	 * @since 4.9.7
	 *
	 * @return mixed The spacer used to indicate an empty space in the stack.
	 */
	public function get_spacer() {
		/**
		 * Filters the spacer that will be used to indicate an empty space in a stack.
		 *
		 * @since 4.9.7
		 *
		 * @param mixed $spacer The spacer that will be used to indicate an empty space in ths stack; default `false`.
		 */
		$spacer = apply_filters( 'tribe_events_views_v2_stack_spacer', false );

		return false;
	}

	/**
	 * Filters and returns a value indicating whether the stack should be built "recycling" spaces or not.
	 *
	 * @since 4.9.7
	 *
	 * @param array $events_by_day An array of event IDs, divided by day, with shape `[ <Y-m-d> => [...<events>] ]`.
	 *
	 * @return bool Whether the stack should be built "recycling" spaces or not.
	 */
	protected function should_recycle_spaces( array $events_by_day = [] ) {
		/**
		 * Filters whether to "recycle" the available spaces or not while building the week stack.
		 *
		 * As an example we have the events:
		 *      1 => [2019-7-1, 2019-7-3]
		 *      2 => [2019-7-2, 2019-7-6]
		 *      3 => [2019-7-5, 2019-7-6]
		 * The week stack would look like this not recycling space:
		 * |1|1|1|-|-|-|-|
		 * |-|2|2|2|2|2|-|
		 * |-|-|-|-|3|3|-|
		 * The week stack would look like this recycling space:
		 * |1|1|1|-|3|3|-|
		 * |-|2|2|2|2|2|-|
		 * The space is "recycled" in the sense that we try to avoid higher stacks, when possible, recycling them.
		 *
		 * @since 4.9.7
		 *
		 * @param bool  $recycle_spaces Whether to recycle space in the week stack or not; default `true`.
		 * @param array $events_by_day  An array of event IDs, divided by day, with shape `[ <Y-m-d> => [...<events>] ]`.
		 */
		return (bool) apply_filters( 'tribe_events_views_v2_stack_recycle_spaces', true, $events_by_day );
	}

	/**
	 * Builds and returns the stack for the current day.
	 *
	 * @since 4.9.7
	 *
	 * @param string $current_day    The current day date, in the `Y-m-d` format.
	 * @param array  $the_day_events All the current day event post IDs.
	 *
	 * @return array The stack for the current day in the shape `[ <event_id>, <spacer>, <event_id>, ...]`.
	 */
	protected function build_day_stack( $current_day, array $the_day_events ) {
		$day_events = $this->filter_stack_events( $the_day_events );

		if ( 0 === count( $day_events ) ) {
			return [];
		}

		// Set some properties we'll use in the methods to avoid having to pass them back and forth.
		$this->current_day = $current_day;
		$this->day_events  = $day_events;

		$this->assign_day_events_position();

		return $this->fill_day_stack();
	}

	/**
	 * Filters an array of events to remove any event that should not be in the stack.
	 *
	 * The default filtering strategy, in the `filter_stack_event` method, will filter out any non multi-day event.
	 * If, in the future, we'll need to change this strategy then either extend the class or use the .
	 *
	 * @since 4.9.7
	 *
	 * @param array $events An array of events, post objects or post IDs, to filter.
	 *
	 * @return array The filtered array of events.
	 */
	protected function filter_stack_events( $events ) {
		$filtered = array_values( array_filter( $events, [ $this, 'filter_stack_event' ] ) );

		/**
		 * Filters the array of events that should be part of the stack.
		 *
		 * By default any non multi-day event will not be part of the stack.
		 *
		 * @since 4.9.7
		 *
		 * @param array $filtered The events as filtered from the default strategy.
		 * @param array $events   The unfiltered events.
		 */
		$filtered = apply_filters( 'tribe_events_views_v2_stack_events', $filtered, $events );

		return $filtered;
	}

	/**
	 * Parses, and sets if required, the stack positions of each event, in the current day, in the stack.
	 *
	 * @since 4.9.7
	 */
	protected function assign_day_events_position() {
		/*
		 * The events come, in the context of the day, sorted by the sorting criteria; e.g. ASC date and time.
		 * In the context of a multi-day stack we might want to maximize the use of space and use empty rows
		 * whenever possible.
		 */
		if ( $this->recycle_space ) {
			$this->stack_positions = $this->assign_open_positions( $this->stack_positions, $this->day_events );

			return;
		}

		$this->stack_positions = $this->assign_next_positions( $this->stack_positions, $this->day_events );
	}

	/**
	 * Normalizes the day stack by adding spacers in each empty position.
	 *
	 * @since 4.9.7
	 *
	 * @return array The day stack with each position, starting from the `0` position, filled with either an event ID or
	 *               a spacer.
	 */
	protected function fill_day_stack() {
		$day_stack = [];

		$day_positions = array_intersect_key(
			$this->stack_positions,
			array_combine( $this->day_events, $this->day_events )
		);

		$max_day_position = count( $day_positions ) ? max( $day_positions ) : 0;
		foreach ( range( 0, $max_day_position ) as $j ) {
			if ( in_array( $j, $day_positions, true ) ) {
				$day_stack[ $j ] = array_search( $j, $day_positions, true );
			} else {
				$day_stack[ $j ] = $this->spacer;
			}
		}

		return $day_stack;
	}

	/**
	 * Normalize the stack by adding padding each stack day to make sure all days are present and have the same length.
	 *
	 * @since 4.9.7
	 */
	protected function normalize_stack() {
		// Calculate the max stack height: we'll need it to pad each day stack.
		$stack_height = array_reduce( $this->stack, static function ( $current_max, array $day_stack ) {
			return max( $current_max, count( $day_stack ) );
		}, 0 );

		// Finally add to the stacks collection.
		foreach ( $this->stack as $current_day => $day_stack ) {
			$this->stack[ $current_day ] = array_pad(
				$day_stack,
				$stack_height,
				$this->spacer
			);
		}
	}

	/**
	 * Checks an event to ensure it should be part of the stack.
	 *
	 * The default strategy is to filter out any non multi-day event, but extending classes can change this.
	 *
	 * @since 4.9.7
	 *
	 * @param int|\WP_Post $event The event post object or ID.
	 *
	 * @return bool Whether teh event should be part of the stack or not.
	 */
	protected function filter_stack_event( $event ) {
		$post = tribe_get_event( $event );

		return $post instanceof \WP_Post && ( ! empty( $post->multiday ) || ! empty( $post->all_day ) );
	}

	/**
	 * Returns the filtered value to decide if the stack should be normalized or not padding each element with spacers
	 * to the same height as the one of the stack elements with more events in it or not.
	 *
	 * @since 4.9.7
	 *
	 * @param array $events_by_day An array of event IDs, divided by day, with shape `[ <Y-m-d> => [...<events>] ]`.
	 *
	 * @return bool Whether the stack should be normalized by padding each one of its elements with spacers at the
	 *              bottom or not.
	 */
	protected function should_normalize_stack(array $events_by_day = []) {
		/**
		 * Filters the value to decide if the stack should be normalized or not padding each element with spacers
		 * to the same height as the one of the stack elements with more events in it or not.
		 *
		 * As an example we have the events:
		 *      1 => [2019-7-1, 2019-7-3]
		 *      2 => [2019-7-2, 2019-7-6]
		 *      3 => [2019-7-5, 2019-7-6]
		 * The week stack would look like this not normalizing it:
		 * |1|1|1|-|-|-|
		 *   |2|2|2|2|2|
		 *         |3|3|
		 * The week stack would look like this normalizing it:
		 * |1|1|1|-|-|-|
		 * |-|2|2|2|2|2|
		 * |-|-|-|-|3|3|
		 * The space is "normalized " by adding spacers at the bottom of any stack element until it reaches the same
		 * height as the one with more elements (the last two days in the example).
		 *
		 * @since 4.9.7
		 *
		 * @param bool $normalize_stack Whether the stack should be normalized by padding each one of its elements with
		 *                              spacers at the bottom or not; defaults to `false`.
		 * @param array $events_by_day An array of event IDs, divided by day, with shape `[ <Y-m-d> => [...<events>] ]`.
		 */
		return apply_filters( 'tribe_events_views_v2_stack_normalize', false, $events_by_day );
	}

	/**
	 * Adds the missing days in the passed events by day to make sure all dates in the period will appear.
	 *
	 * @since 4.9.7
	 *
	 * @param array $events_by_day The events part of the stack, divided by day.
	 *
	 * @return array The events part of the stack, divided by day with added missing days, if any.
	 */
	protected function add_missing_days( array $events_by_day ) {
		$days      = array_keys( $events_by_day );
		$first_day = reset( $days );
		$last_day  = end( $days );

		try {
			// The timezone is not relevant here.
			$period = new \DatePeriod(
				Dates::build_date_object( $first_day ),
				new \DateInterval( 'P1D' ),
				Dates::build_date_object( $last_day )->setTime( 23, 59, 59 )
			);

			$missing = [];
			/** @var \DateTime $date */
			foreach ( $period as $date ) {
				$date_string = $date->format( 'Y-m-d' );
				if ( in_array( $date_string, $days, true ) ) {
					continue;
				}
				$missing[$date_string] = [];
			}
		} catch ( \Exception $e ) {
			// If there's any issue just return the events by day as they are.
			return $events_by_day;
		}

		$events_by_day = array_merge( $events_by_day, $missing );
		ksort( $events_by_day );

		return $events_by_day;
	}

	/**
	 * Assigns to each event the first available position in the day stack.
	 *
	 * This method will "fill" empty spaces in the stack to recycle the space.
	 *
	 * @since 4.9.9
	 *
	 * @param array $stack_positions        The currently assigned stack positions, in the shape
	 *                                      `[ <id> => <position> ]`.
	 * @param array $wo_position            An array of event post IDs for events that do not have a position assigned
	 *                                      in the day stack.
	 *
	 * @return array An updated array of stack positions, in the shape `[ <id> => <position> ]`.
	 */
	protected function assign_open_positions( array $stack_positions, array $events ) {
		$wo_position = array_diff( $events, array_keys( $stack_positions ) );

		if ( ! count( $wo_position ) ) {
			return $stack_positions;
		}
		$taken_day_positions    = array_intersect_key( $stack_positions, array_flip( $events ) );
		$all_possible_positions = range( 0, count( $stack_positions ) + count( $wo_position ) );
		$open_day_positions     = array_values( array_diff( $all_possible_positions, $taken_day_positions ) );
		$assigned_day_positions = array_combine( $wo_position, array_slice( $open_day_positions, 0, count( $wo_position ) ) );
		// Use the `+` to avoid the re-indexing: the indexes here are the event post IDs.
		$stack_positions += $assigned_day_positions;

		return $stack_positions;
	}

	/**
	 * Assigns a stack postion to each event w/o one not recycling space.
	 *
	 * @since 4.9.9
	 *
	 * @param array $stack_positions The current stack positions.
	 * @param array $event_ids       The events to position in the stack, events that already have a position will not
	 *                               be re-positioned.
	 *
	 * @return array The finalized stack positions, where each event has been assigned a position in the stack.
	 */
	protected function assign_next_positions( array $stack_positions, array $event_ids ) {
		$wo_position = array_diff( $event_ids, array_keys( $stack_positions ) );
		foreach ( $wo_position as $position_in_day => $event_id ) {
			// The event position is the next one.
			$stack_positions[ $event_id ] = count( $stack_positions ) ? max( $stack_positions ) + 1 : 0;
		}

		return $stack_positions;
	}
}
