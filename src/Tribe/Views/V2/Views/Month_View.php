<?php
/**
 * The Month View.
 *
 * @since   4.9.3
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\Utils\Stack;
use Tribe\Events\Views\V2\View;
use Tribe__Context as Context;
use Tribe__Date_Utils as Date_Utils;
use Tribe__Events__Template__Month as Month;
use Tribe__Events__Timezones as Timezones;
use Tribe__Utils__Array as Arr;

class Month_View extends View {

	/**
	 * The default number of events to show per-day.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected static $posts_per_page_default = 10;

	/**
	 * Slug for this view.
	 *
	 * @since 4.9.3
	 *
	 * @var string
	 */
	protected $slug = 'month';

	/**
	 * Visibility for this view.
	 *
	 * @since 4.9.4
	 *
	 * @var bool
	 */
	protected $publicly_visible = true;

	/**
	 * The year and month string, in the format `Y-m`, the Month View is currently displaying.
	 *
	 * @var string
	 */
	protected $year_month;

	/**
	 * An array cache that contains the Month View events divided by grid day.
	 *
	 * The array will have shape `[ <Y-m-d> => [<events_post_ids>] ]`.
	 *
	 * @var array
	 */
	protected $grid_days;
	
	/**
     * An instance of the Week Stack object.
	 * 
	 * @since TBD
	 * 
	 * @var Stack
	 */
	protected $stack;

	/**
	 * Month_View constructor.
	 * 
	 * @since TBD
	 * 
	 * @param Stack $week_stack An instance of the Week Stack object.
	 */
	public function __construct( Stack $week_stack) {
		$this->stack = $week_stack;
	}

	/**
	 * Returns an array of event post IDs, divided by days.
	 *
	 * Note that multi-day events will show up in multiple days.
	 *
	 * @since TBD
	 *
	 * @param null|string $year_month The year and month, in the `Y-m` format, defaults to the current ones if not set.
	 * @param bool $force Whether to force a re-fetch or try and use the cached values or not.
	 *
	 * @return array An array in the shape `[ <Y-m> => [...<events>], <Y-m> => [...<events>] ]`.
	 */
	public function get_grid_days( $year_month = null, $force = false ) {
		if (
			isset( $this->grid_days, $this->year_month )
			&& $year_month && $year_month === $this->year_month
			&& ! $force
		) {
			return $this->grid_days;
		}

		$year_month = $year_month ?: $this->year_month;

		$grid_start = Month::calculate_first_cell_date( $year_month );
		$grid_end   = Month::calculate_final_cell_date( $year_month );
		$timezone   = Timezones::build_timezone_object();
		try {
			$days = new \DatePeriod(
				new \DateTime( $grid_start, $timezone ),
				new \DateInterval( 'P1D' ),
				new \DateTime( $grid_end, $timezone )
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

		$this->grid_days = [];
		/** @var \DateTime $day */
		foreach ( $days as $day ) {
			$start = clone $day->setTime( 0, 0, 0 );
			$end   = clone $day->setTime( 23, 59, 59 );

			$event_ids = tribe_events()->by_args( $repository_args )
			                           ->where( 'date_overlaps', $start, $end )
			                           ->order_by( $order_by, $order )
			                           ->get_ids();

			$this->grid_days[ $day->format( 'Y-m-d' ) ] = $event_ids;
		}

		return $this->grid_days;
	}

	/**
	 * Returns the post IDs of all the events found in the month.
	 *
	 * Note: multi-day events will appear once; this is a conflation of all events on the month.
	 *
	 * @since TBD
	 *
	 * @return array A flat array of all the events found on the calendar grid.
	 */
	public function found_post_ids() {
		return null !== $this->grid_days
			? array_unique( array_merge( ... array_values( $this->grid_days ) ) )
			: [];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( Context $context = null ) {
		// Let's apply the arguments common to all Views.
		$args = parent::setup_repository_args( $context );

		$context = null !== $context ? $context : $this->context;

		// Let's override the ones the Month View will use differently.
		$context_arr = $context->to_array();
		// The setting governing the Events > Settings > Display > "Month view events per day" setting.
		$args['posts_per_page'] = Arr::get( $context_arr, 'month_posts_per_page', static::$posts_per_page_default );
		// Per-day events never paginate.
		unset( $args['paged'] );

		$date = Arr::get( $context_arr, 'event_date', 'now' );

		$this->year_month = ( new \DateTime( $date ) )->format( 'Y-m' );

		// @todo we'll need to be a bit more sophisticated here with the ordering.
		$args['order_by'] = 'event_date';
		$args['order']    = 'ASC';
		$this->repository->order_by( 'event_date', 'ASC' );

		/*
		 * The event fetching will happen day-by-day so we set here the repository args we'll re-use fetching each
		 * day events.
		 */
		$this->repository_args = $args;

		return $args;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_template_vars() {
		/*
		 * We'll run the fetches day-by-day, we do not want to run a potentially expensive query for ALL the events
		 * in the month.
		 */
		$this->repository->void_query( true );
		$template_vars = parent::setup_template_vars();
		$this->repository->void_query( false );

		// The events will be returned in an array with shape `[ <Y-m-d> => [...<events>], <Y-m-d> => [...<events>] ]`.
		$events = $this->get_grid_days();

		$multiday_stacks = $this->build_multiday_stacks( $events );

		// Let's prepare an array of days more digestible by the templates.
		$days = [];
		foreach ( $events as $day_date => $day_events ) {
			$date_object = Date_Utils::build_date_object( $day_date );

			$days[] = [
				'date'            => $day_date, // Y-m-d
				'year_number'     => (int) $date_object->format( 'Y' ),
				'month_number'    => (int) $date_object->format( 'm' ),
				'day_number'      => (int) $date_object->format( 'd' ),
				'events'          => array_filter( $day_events, static function ( \WP_Post $event ) {
					return ! $event->multiday;
				} ),
				'featured_events' => array_filter( $day_events ),
				static function ( \WP_Post $event ) {
					return $event->featured;
				},
				// Includes spacers.
				'multiday_events' => Arr::get( $multiday_stacks, $day_date, [] ),
			];
		}

		$template_vars['events'] = $events;
		$template_vars['days']   = $days;

		return $template_vars;
	}

	/**
	 * Parses the multi-day events and produces the multi-day "stack", including spacers.
	 *
	 * @since TBD
	 *
	 * @param array $grid_events_by_day An array of events, per-day, in the shape `[ <Y-m-d> => [...<event_ids> ] ]`;
	 *
	 * @return array An array of all the month days, each entry filled with spacers and/or event post IDs in the correct
	 *               order. E.g.
	 *               `[ '2019-07-01' => [2, 3, false], '2019-07-02' => [2, 3, 4], '2019-07-03' => [false, 3, 4]]`.
	 */
	protected function build_multiday_stacks( array $grid_events_by_day ) {
		$week_stacks = [];
		foreach ( array_chunk( $grid_events_by_day, 7, true ) as $week ) {
			$week_stacks[] = $this->stack->build_from_events( $week );
		}

		return array_merge( ...$week_stacks );
	}

	/**
	 * Returns a portion of the parsed multi-day stacks.
	 *
	 * @since TBD
	 *
	 * @param \DateTime|string $from The start of the portion to return.
	 * @param \DateTime|string $to   The end of the portion to return.
	 *
	 * @return array|null A slice of the multi-day stack, in the shape
	 *               `[ '2019-07-01' => [2, 3, false], , '2019-07-03' => [false, 3, 4]]`.
	 */
	public function get_multiday_stack( $from, $to ) {
		$from = Date_Utils::build_date_object( $from );
		$to   = Date_Utils::build_date_object( $to );

		$events          = $this->get_grid_days();
		$multiday_events = $this->build_multiday_stacks( $events );

		$start_index = array_key_exists( $from->format( 'Y-m-d' ), $multiday_events )
			? array_search( $from->format( 'Y-m-d' ), array_keys( $multiday_events ), true )
			: 0;
		$end_index   = array_key_exists( $to->format( 'Y-m-d' ), $multiday_events )
			? array_search( $to->format( 'Y-m-d' ), array_keys( $multiday_events ), true )
			: count( $multiday_events ) - 1;

		$stack = array_slice( $multiday_events, $start_index, $end_index - $start_index + 1, true );

		return $stack;
	}
}
