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
use Tribe\Traits\Cache_User;
use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Template__Month as Month;
use Tribe__Events__Timezones as Timezones;
use Tribe__Utils__Array as Arr;

class Month_View extends View {
	use Cache_User;

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
     * An instance of the Week Stack object.
	 * 
	 * @since TBD
	 * 
	 * @var Stack
	 */
	protected $stack;

	/**
	 * An array of cached event IDs per day.
	 * Used by the `Cache_User` trait.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $grid_days_cache = [];

	/**
	 * An array of cached event counts per day.
	 * Used by the `Cache_User` trait.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	private $grid_days_found_cache = [];

	/**
	 * Month_View constructor.
	 * 
	 * @since TBD
	 * 
	 * @param Stack $stack An instance of the Stack object.
	 */
	public function __construct( Stack $stack) {
		$this->stack = $stack;
		add_action( 'shutdown', [ $this, 'dump_cache' ] );
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
			isset( $this->year_month )
			&& $year_month && $year_month === $this->year_month
			&& ! empty( $this->grid_days_cache )
			&& ! $force
		) {
			return $this->grid_days_cache;
		}

		$year_month = $year_month ?: $this->year_month;

		$grid_start = Month::calculate_first_cell_date( $year_month );
		$grid_end   = Month::calculate_final_cell_date( $year_month );
		$timezone   = Timezones::build_timezone_object();

		try {
			$grid_start_date = ( new \DateTime( $grid_start, $timezone ) )->setTime( 0, 0 );
			$grid_end_date   = ( new \DateTime( $grid_end, $timezone ) )->setTime( 23, 59, 59 );
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

		/** @var \DateTime $day */
		foreach ( $days as $day ) {
			$day_string = $day->format( 'Y-m-d' );

			if ( isset( $this->grid_days_cache[ $day_string ] ) ) {
				return $this->grid_days_cache[ $day_string ];
			}

			$start = clone $day->setTime( 0, 0, 0 );
			$end   = clone $day->setTime( 23, 59, 59 );

			$day_query = tribe_events()->by_args( $repository_args )
			                           ->where( 'date_overlaps', $start, $end )
			                           ->order_by( $order_by, $order );
			$event_ids = $day_query ->get_ids();
			$found     = $day_query->found();

			$this->grid_days_cache[ $day_string ]       = $event_ids;
			$this->grid_days_found_cache[ $day_string ] = $found;
		}

		return $this->grid_days_cache;
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
		return ! empty( $this->grid_days_cache )
			? array_unique( array_merge( ... array_values( $this->grid_days_cache ) ) )
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

		// @todo @be we'll need to be a bit more sophisticated here with the ordering.
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
		$grid_days = $this->get_grid_days();
		$days      = $this->get_days_data( $grid_days );

		$grid_date             = Dates::build_date_object( $this->context->get( 'event_date', 'today' ) );
		$month_and_year_format = tribe_get_option( 'monthAndYearFormat', 'F Y' );

		$template_vars['today_date']          = Dates::build_date_object()->format( 'Y-m-d' );
		$template_vars['grid_date']           = $grid_date->format( 'Y-m-d' );
		$template_vars['formatted_grid_date'] = $grid_date->format( $month_and_year_format );
		$template_vars['events']              = $grid_days;
		$template_vars['days']                = $days;

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
		foreach ( array_chunk( $grid_events_by_day, 7, true ) as $week_events_by_day ) {
			$week_stacks[] = $this->stack->build_from_events( $week_events_by_day );
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
		$from = Dates::build_date_object( $from )->setTime( 0, 0 );
		$to   = Dates::build_date_object( $to )->setTime( 23, 59, 59 );

		$events = $this->get_grid_days();
		$multiday_stack = $this->build_multiday_stacks( $events );

		$start_index = array_key_exists( $from->format( 'Y-m-d' ), $multiday_stack )
			? array_search( $from->format( 'Y-m-d' ), array_keys( $multiday_stack ), true )
			: 0;
		$end_index   = array_key_exists( $to->format( 'Y-m-d' ), $multiday_stack )
			? array_search( $to->format( 'Y-m-d' ), array_keys( $multiday_stack ), true )
			: count( $multiday_stack ) - 1;

		$stack = array_slice( $multiday_stack, $start_index, $end_index - $start_index + 1, true );

		return $stack;
	}

	/**
	 * Returns the number of events found for each day.
	 *
	 * The number of events found ignores the per-page setting and it includes any event happening on the day.
	 * This includes multi-day events happening on the day.
	 *
	 * @since TBD
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
	 * Populates the data for each day in the grid and returns it.
	 *
	 * @since TBD
	 *
	 * @param array $grid_days An associative array of events per day, in the shape `[ <Y-m-d> => [...<events>] ]`.
	 *
	 * @return array An associative array of day data for each day in the shape `[ <Y-m-d> => <day_data> ]`.
	 */
	protected function get_days_data( array $grid_days ) {
		$found_events = $this->get_grid_days_counts();

		// The multi-day stack will contain spacers and post IDs.
		$multiday_stacks = $this->build_multiday_stacks( $grid_days );

		// Let's prepare an array of days more digestible by the templates.
		$days = [];
		foreach ( $grid_days as $day_date => $day_events ) {
			/**
			 * This will be used to call `tribe_get_event` in the context of a specific week for each day
			 * to have valid and coherent `starts_this_week` and `ends_this_week` properties set.
			 *
			 * @see tribe_get_event()
			 */
			$date_object = Dates::build_date_object( $day_date );

			// The multi-day stack includes spacers; that's why we use `element`.
			$multiday_events_stack = array_map( static function ( $element ) use ( $date_object ) {
				// If it's numeric make an event object of it.
				return is_numeric( $element ) ?
					tribe_get_event( $element, OBJECT, $date_object->format( 'Y-m-d' ) )
					: $element;
			}, Arr::get( $multiday_stacks, $day_date, [] ) );

			$the_day_events = array_map( 'tribe_get_event',
				array_filter( $day_events, static function ( $event ) use ( $date_object ) {
					$event = tribe_get_event( $event, OBJECT, $date_object->format( 'Y-m-d' ) );

					return $event instanceof \WP_Post && ! ( $event->multiday || $event->all_day );
				} )
			);

			$more_events  = 0;
			$found_events = Arr::get( $found_events, $day_date, 0 );

			if ( $found_events ) {
				/*
				 * We cannot know before-hand what spacer will be used (it's filterable) so we have to count the events
				 * by keeping only the posts.
				 */
				$multiday_events_count = count( array_filter( $multiday_events_stack ), static function ( $el ) {
					return $el instanceof \WP_Post;
				} );
				/*
				 * In the context of the Month View we want to know if there are more events we're not seeing.
				 * So we exclude the ones we see and the multi-day ones that we're seeing in the multi-day stack.
				 */
				$more_events = $found_events - $multiday_events_count - count( $the_day_events );
			}

			$featured_events = array_map( 'tribe_get_event',
				array_filter( $day_events,
					static function ( $event ) use ( $date_object ) {
						$event = tribe_get_event( $event, OBJECT, $date_object->format( 'Y-m-d' ) );

						return $event instanceof \WP_Post && $event->featured;
					} )
			);

			$start_of_week = get_option( 'start_of_week', 0 );

			$day_data = [
				'date'             => $day_date,
				'is_start_of_week' => $start_of_week === $date_object->format( 'N' ),
				'year_number'      => (int) $date_object->format( 'Y' ),
				'month_number'     => (int) $date_object->format( 'm' ),
				'day_number'       => (int) $date_object->format( 'd' ),
				'events'           => $the_day_events,
				'featured_events'  => $featured_events,
				'multiday_events'  => $multiday_events_stack,
				'found_events'     => $found_events,
				'more_events'      => $more_events,
			];

			$days[ $day_date ] = $day_data;
		}

		return $days;
	}
}
