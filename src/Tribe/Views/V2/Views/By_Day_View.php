<?php
/**
 * ${CARET}
 *
 * @since   4.9.7
 *
 * @package Tribe\Events\Views\V2\Views
 */


namespace Tribe\Events\Views\V2\Views;


use Tribe\Events\Views\V2\View;
use Tribe\Traits\Cache_User;
use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Date_Utils as Dates;
use Tribe__Utils__Array as Arr;

abstract class By_Day_View extends View{
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
	 * By_Day_View constructor.
	 *
	 * @since 4.9.7
	 */
	public function __construct(  ) {
		add_action( 'shutdown', [ $this, 'dump_cache' ] );
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
	 * @return array An array in the shape `[ <Y-m> => [...<events>], <Y-m> => [...<events>] ]`.
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

		$this->user_date = $date ?: $this->context->get( 'event_date', 'now' );

		list( $grid_start, $grid_end ) = $this->calculate_grid_start_end( $this->user_date );

		try {
			$grid_start_date = $grid_start->setTime( 0, 0 );
			$grid_end_date   = $grid_end->setTime( 23, 59, 59 );
			$days            = new \DatePeriod(
				$grid_start_date,
				new \DateInterval( 'P1D' ),
				$grid_end_date
			);
		} catch ( \Exception $e ) {
			// If anything happens let's return an empty array.
			return [];
		}

		if ( empty( $this->repository_args ) ) {
			$this->setup_repository_args();
		}

		$repository_args = $this->repository_args;
		$order_by        = Arr::get( $repository_args, 'order_by', 'event_date' );
		$order           = Arr::get( $repository_args, 'order', 'ASC' );
		unset( $repository_args['order_by'], $repository_args['order'] );

		$this->warmup_cache( 'grid_days', 0, Cache_Listener::TRIGGER_SAVE_POST );
		$this->warmup_cache( 'grid_days_found', 0, Cache_Listener::TRIGGER_SAVE_POST );
		$events_per_day = $this->get_events_per_day();

		/** @var \DateTime $day */
		foreach ( $days as $day ) {
			$day_string = $day->format( 'Y-m-d' );

			if ( isset( $this->grid_days_cache[ $day_string ] ) ) {
				continue;
			}

			$start = tribe_beginning_of_day( $day->format( Dates::DBDATETIMEFORMAT ) );
			$end   = tribe_end_of_day( $day->format( Dates::DBDATETIMEFORMAT ) );

			$day_query = tribe_events()->by_args( $repository_args )
			                           ->where( 'date_overlaps', $start, $end )
			                           ->per_page( $events_per_day )
			                           ->order_by( $order_by, $order );
			$event_ids = $day_query->get_ids();
			$found     = $day_query->found();

			$this->grid_days_cache[ $day_string ]       = $event_ids;
			$this->grid_days_found_cache[ $day_string ] = $found;
		}

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
		$events_per_day = $this->context->get( 'events_per_page', 10 );

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
}