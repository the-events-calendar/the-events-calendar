<?php
/**
 * The parent, abstract, View that models a view rendering on a per-day basis.
 *
 * @since   4.9.7
 *
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\Repository\Event_Period;
use Tribe\Events\Views\V2\Utils\Stack;
use Tribe\Events\Views\V2\View;
use Tribe\Traits\Cache_User;
use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Date_Utils as Dates;
use Tribe__Utils__Array as Arr;

/**
 * Class By_Day_View
 *
 * @since   4.9.7
 *
 * @package Tribe\Events\Views\V2\Views
 */
abstract class By_Day_View extends View {
	use Cache_User;

	/**
	 * The date input by the user, either by selecting the default view or using the bar.
	 *
	 * @var string
	 */
	protected $user_date;
	/**
	 * An array of cached event IDs per day.
	 * Used by the `Cache_User` trait.
	 *
	 * @since 4.9.7
	 *
	 * @var array
	 */
	protected $grid_days_cache = [];

	/**
	 * An array of cached event counts per day.
	 * Used by the `Cache_User` trait.
	 *
	 * @since 4.9.7
	 *
	 * @var array
	 */
	protected $grid_days_found_cache = [];

	/**
	 * An instance of the Stack object.
	 *
	 * @since 4.9.9
	 *
	 * @var Stack
	 */
	protected $stack;

	/**
	 * By_Day_View constructor.
	 *
	 * @since 4.9.7
	 * @since 4.9.11 Added inheritance from View object and `$messages` parameter.
	 *
	 * @param Messages $messages An instance of the view messages handler.
	 * @param Stack    $stack    An instance of the stack handler.
	 */
	public function __construct( Messages $messages, Stack $stack ) {
		parent::__construct( $messages );
		add_action( 'shutdown', [ $this, 'dump_cache' ] );
		$this->stack = $stack;
	}

	/**
	 * Returns an array of event post IDs, divided by days.
	 *
	 * Note that multi-day events will show up in multiple days.
	 *
	 * @since 4.9.7
	 *
	 * @param null|string $date  The date to use as pivot, defaults to the current ones if not set.
	 * @param bool        $force Whether to force a re-fetch or try and use the cached values or not.
	 *
	 * @return array An array in the shape `[ <Y-m-d> => [...<events>], <Y-m-d> => [...<events>] ]`.
	 */
	public function get_grid_days( $date = null, $force = false ) {
		if (
			! $force
			&& ! empty( $this->grid_days_cache )
			&& isset( $this->user_date )
			&& ( null === $date || $this->user_date === $date )
		) {
			return $this->grid_days_cache;
		}

		if ( empty( $this->user_date ) ) {
			$this->user_date = $date ?: $this->context->get( 'event_date', 'now' );
		}

		list( $grid_start, $grid_end ) = $this->calculate_grid_start_end( $this->user_date );

		try {
			$grid_start_date = $grid_start->setTime( 0, 0 );
			// Add a day at the end to pick-up multi-day events starting on the last day.
			$grid_end_date = $grid_end->setTime( 23, 59, 59 )->add( new \DateInterval( 'P1D' ) );
			$days          = new \DatePeriod(
				$grid_start_date,
				new \DateInterval( 'P1D' ),
				$grid_end_date
			);
		} catch ( \Exception $e ) {
			// If anything happens let's return an empty array.
			return [];
		}

		if ( empty( $this->repository_args ) ) {
			/**
			 * If repository arguments have not ben set up yet, let's do it now.
			 */
			$this->repository_args = $this->filter_repository_args( $this->setup_repository_args() );
		}

		$repository_args = $this->repository_args;
		$order_by        = Arr::get( $repository_args, 'order_by', 'event_date' );
		$order           = Arr::get( $repository_args, 'order', 'ASC' );
		unset( $repository_args['order_by'], $repository_args['order'] );

		$this->warmup_cache( 'grid_days', 0, Cache_Listener::TRIGGER_SAVE_POST );
		$this->warmup_cache( 'grid_days_found', 0, Cache_Listener::TRIGGER_SAVE_POST );
		$events_per_day = $this->get_events_per_day();

		// @todo remove this when the Event_Period repository is solid and clean up.
		$using_period_repository = tribe_events_view_v2_use_period_repository();

		if ( $using_period_repository ) {
			/** @var Event_Period $repository */
			if ( tribe_is_truthy( tribe_get_option( 'enable_month_view_cache', false ) ) ) {
				$repository = tribe_events( 'period', 'caching' );
			} else {
				$repository = tribe_events( 'period' );
			}
			$repository->by_period( $grid_start_date, $grid_end_date )->fetch();
		}

		// phpcs:ignore
		/** @var \DateTime $day */
		foreach ( $days as $day ) {
			$day_string = $day->format( 'Y-m-d' );

			if ( $using_period_repository && isset( $repository ) ) {
				$day_results = $repository->by_date( $day_string )->get_set();

				$event_ids = [];
				if ( $day_results->count() ) {
					// Sort events by honoring order and direction.
					$day_results->order_by( $order_by, $order );
					$event_ids = array_map( 'absint', $day_results->pluck( 'ID' ) );
				}

				// @todo @bluedevs truncating here does not make sense, do in template?
				$day_event_ids = array_slice( $event_ids, 0, $events_per_day );

				$this->grid_days_cache[ $day_string ]       = $day_event_ids;
				$this->grid_days_found_cache[ $day_string ] = $day_results->count();
			} else {
				$start = tribe_beginning_of_day( $day->format( Dates::DBDATETIMEFORMAT ) );
				$end   = tribe_end_of_day( $day->format( Dates::DBDATETIMEFORMAT ) );
				/*
				 * We want events overlapping the current day, by more than 1 second.
				 * This prevents events ending on the cutoff from showing up here.
				 */
				$day_query = tribe_events()
					->set_found_rows(true)
					->by_args( $repository_args )
					->where( 'date_overlaps', $start, $end, null, 2 )
					->per_page( $events_per_day )
					->order_by( $order_by, $order );
				$day_event_ids = $day_query->get_ids();
				$found     = $day_query->found();

				$this->grid_days_cache[ $day_string ]       = (array) $day_event_ids;
				$this->grid_days_found_cache[ $day_string ] = (int) $found;
			}

			/*
			 * Multi-day events will always appear on the second day and forward, back-fill if they did not make the
			 * cut (of events per day) on previous days.
			 */
			$this->backfill_multiday_event_ids( $day_event_ids );
		}

		if ( $using_period_repository ) {
			$post_ids = array_filter( array_unique( array_merge( ... array_values( $this->grid_days_cache ) ) ) );
			/** @var \Tribe__Cache $cache */
			$cache = tribe( 'cache' );
			$cache->warmup_post_caches( $post_ids, true );
		} else {
			if ( is_array( $this->grid_days_cache ) && count( $this->grid_days_cache ) ) {
				$this->grid_days_cache = $this->add_implied_events( $this->grid_days_cache );
			}
		}

		// Drop the last day we've added before.
		array_pop( $this->grid_days_cache );
		array_pop( $this->grid_days_found_cache );

		return $this->grid_days_cache;
	}

	/**
	 * Calculates the date of the first and final grid cell for the view.
	 *
	 * Each cell is a day, usually.
	 *
	 * @since 4.9.7
	 *
	 * @param \DateTime|string $date The date that should be used to calculate the grid first and final cell date.
	 *
	 * @return array The View grid first and final cell dates, each one an instance of the `DateTime` class.
	 */
	abstract protected function calculate_grid_start_end( $date );

	/**
	 * Returns the post IDs of all the events found in the View.
	 *
	 * Note: multi-day events will appear once; this is a conflation of all events on the View.
	 *
	 * @since 4.9.7
	 *
	 * @return array A flat array of all the events found on the calendar grid.
	 */
	public function found_post_ids() {
		if ( empty( $this->grid_days_cache ) ) {
			$this->get_grid_days();
		}

		return ! empty( $this->grid_days_cache )
			? array_unique( array_merge( ... array_values( $this->grid_days_cache ) ) )
			: [];
	}

	/**
	 * Returns the number of events found for each day.
	 *
	 * The number of events found ignores the per-page setting and it includes any event happening on the day.
	 * This includes multi-day events happening on the day.
	 *
	 * @since 4.9.7
	 *
	 * @return array An array of days, each containing the count of found events for that day;
	 *               the array has shape `[ <Y-m-d> => <count> ]`;
	 */
	public function get_grid_days_counts() {
		// Fetch the events for each day on the grid, if not done already.
		$this->get_grid_days();

		return $this->grid_days_found_cache;
	}

	/**
	 * Returns the number of events to show per each day on the grid.
	 *
	 * Different Views with different implementations of this logic should override this method.
	 *
	 * @since 4.9.7
	 *
	 * @return int The number of events to show, per each day, in total, in the view.
	 */
	protected function get_events_per_day() {
		$events_per_day = $this->context->get( 'events_per_page', 12 );

		/**
		 * Filters the number of events per day to fetch in th View.
		 *
		 * @since 4.9.7
		 *
		 * @param int         $events_per_day The default number of events that will be fetched for each day.
		 * @param By_Day_View $this           The current View instance.
		 */
		return apply_filters( "tribe_events_views_v2_{$this->slug}_events_per_day", $events_per_day, $this );
	}

	/**
	 * Builds the next or prev URL given the date that should be used.
	 *
	 * @since 4.9.9
	 *
	 * @param mixed $date          The date to build the URL from, a date object or string.
	 * @param bool  $canonical     Whether to return the canonical version of the URL or not.
	 * @param array $passthru_vars An array of variables that should be preserved and applied to the resulting URL.
	 *
	 * @return string The URL as built from the event.
	 */
	protected function build_url_for_date( $date, $canonical, array $passthru_vars = [] ) {
		$url  = $this->get_url();
		$date = Dates::build_date_object( $date );

		$event_date_aliases = $this->url->get_query_args_aliases_of( 'event_date', $this->context );
		$event_date_aliases = array_unique( array_merge( $event_date_aliases, [ 'eventDate', 'tribe-bar-date' ] ) );

		if ( ! empty( $event_date_aliases ) ) {
			$url = remove_query_arg( $event_date_aliases, $this->get_url() );
		}

		$url = add_query_arg( [ 'eventDate' => $date->format( $this->get_url_date_format() ) ], $url );

		if ( ! empty( $url ) && $canonical ) {
			$input_url = $url;

			if ( ! empty( $passthru_vars ) ) {
				$input_url = remove_query_arg( array_keys( $passthru_vars ), $url );
			}

			// Make sure the view slug is always set to correctly match rewrites.
			$input_url = add_query_arg( [ 'eventDisplay' => $this->slug ], $input_url );

			$canonical_url = tribe( 'events.rewrite' )->get_clean_url( $input_url );

			if ( ! empty( $passthru_vars ) ) {
				$canonical_url = add_query_arg( $passthru_vars, $canonical_url );
			}

			$url = $canonical_url;
		}

		return $url;
	}

	/**
	 * Adds the implied events to the grid days results.
	 *
	 * The combination of sticky events, other order rules and a limit to the number of events per day,
	 * might yield incoherent results.
	 * Fact: events do not have "gaps" in them (in the way we model them).
	 * To avoid other queries here we apply the principle below and add "implied" events:
	 * if a an event is present on day 1 and 3 or later, then it must be present on day 2 too.
	 *
	 * Note there's a fallacy in this method: if an event appears once and never again, in any of the days, then it
	 * will never be implied. This is an issue, but this provides a close enough solution on most scenarios.
	 *
	 * @since 4.9.11
	 *
	 * @param array $grid_days The current array of grid days.
	 *
	 * @return array The grid days, modified to contain implied events, if required.
	 */
	protected function add_implied_events( array $grid_days ) {
		$next_days = array_values( $grid_days );

		foreach ( $grid_days as $day_string => &$event_ids ) {
			$prev_day_events = isset( $prev_day_string ) ? $grid_days[ $prev_day_string ] : [];
			$prev_day_string = $day_string;

			// Move the next days forward by "cutting" the head.
			array_shift( $next_days );
			$next_events = $next_days;

			if ( empty( $next_events ) ) {
				// We're done: there cannot be more implied events.
				break;
			}

			// We use `array_unique` here to speed up the following intersect and diff functions.
			$next_events = array_unique( array_merge( ...$next_events ) );

			$implied = array_diff( array_intersect( $prev_day_events, $next_events ), $event_ids );

			if ( count( $implied ) ) {
				// We append the days at the end; this might not in line w/ ordering criteria.
				array_push( $event_ids, ...$implied );
			}
		}

		return $grid_days;
	}

	/**
	 * Back-fills the days cache to add multi-day events that, due to events-per-day limits, might not appear on first
	 * day.
	 *
	 * Multi-day events are pulled from the query as normal events.
	 * When the LIMIT, imposed by events-per-day, is applied a multi-day event starting late in the day, might not
	 * appear on the first day it starts as other, earlier, events, might fill the LIMIT for that day.
	 * That same event will appear, but, on later days, starting from the 2nd one.
	 * Here we go "back" and apply this principle to insert multi-days in previous day(s) cache(s) if required.
	 *
	 * @since 4.9.12
	 *
	 * @param array $event_ids An array of event post IDs for the day.
	 */
	protected function backfill_multiday_event_ids( array $event_ids = [] ) {
		if ( empty( $event_ids ) ) {
			return;
		}

		try {
			$one_day = new \DateInterval( 'P1D' );
		} catch ( \Exception $e ) {
			return;
		}

		foreach ( $event_ids as $event_id ) {
			$event = tribe_get_event( $event_id );

			if ( ! $event instanceof \WP_Post ) {
				continue;
			}

			if ( ! $event->multiday ) {
				continue;
			}

			try {
				$event_period = new \DatePeriod(
					$event->dates->start_display->setTime( 0, 0, 0 ),
					$one_day,
					$event->dates->end_display
				);
			} catch ( \Exception $e ) {
				continue;
			}

			/** @var \DateTime $event_day */
			foreach ( $event_period as $event_day ) {
				$event_day_string = $event_day->format( Dates::DBDATEFORMAT );

				if ( ! isset( $this->grid_days_cache[ $event_day_string ] ) ) {
					continue;
				}

				if ( ! in_array( $event_id, $this->grid_days_cache[ $event_day_string ], true ) ) {
					$this->grid_days_cache[ $event_day_string ][] = $event_id;
					// No need to update the found cache: that's already taking this event into account.
				}
			}
		}
	}

	/**
	 * ${CARET}
	 *
	 * @since 4.9.13
	 *
	 * @return bool
	 */
	protected function using_period_repository() {
		return defined( 'TRIBE_EVENTS_V2_VIEWS_USE_PERIOD_REPOSITORY' )
		       && TRIBE_EVENTS_V2_VIEWS_USE_PERIOD_REPOSITORY;
	}
}
