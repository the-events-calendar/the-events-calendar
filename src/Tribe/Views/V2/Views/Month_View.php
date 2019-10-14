<?php
/**
 * The Month View.
 *
 * @since   4.9.3
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Template__Month as Month;
use Tribe__Utils__Array as Arr;

class Month_View extends By_Day_View {

	/**
	 * The default number of events to show per-day.
	 *
	 * @since 4.9.7
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
	 * {@inheritDoc}
	 */
	public function prev_url( $canonical = false, array $passthru_vars = [] ) {
		if ( isset( $this->prev_url ) ) {
			return $this->prev_url;
		}

		// Setup the Default date for the month view here.
		$default_date = 'today';
		$date         = $this->context->get( 'event_date', $default_date );
		$current_date = Dates::build_date_object( $date );

		if ( $this->skip_empty() ) {
			// Find the first event that starts before the start of this month.
			$prev_event = tribe_events()
				->by_args( $this->filter_repository_args( $this->setup_repository_args() ) )
				->where( 'starts_before', tribe_beginning_of_day( $current_date->format( 'Y-m-01' ) ) )
				->order( 'DESC' )
				->first();
			if ( ! $prev_event instanceof \WP_Post ) {
				return $this->filter_prev_url( $canonical, '' );
			}

			// Show the closest date on which that event appears (but not the current date).
			$prev_date = min(
				$prev_event->dates->start,
				$current_date->sub( new \DateInterval( 'P1M' ) )
			);
		} else {
			$prev_date = Dates::build_date_object( $current_date->format( 'Y-m-01' ) );
			$prev_date->sub( new \DateInterval( 'P1M' ) );
			// Let's make sure to prevent users from paginating endlessly back when we know there are no more events.
			$earliest = tribe_get_option( 'earliest_date', $prev_date );
			if ( $current_date->format( 'Y-m' ) === Dates::build_date_object( $earliest )->format( 'Y-m' ) ) {
				return $this->filter_prev_url( $canonical, '' );
			}
		}

		$url = $this->build_url_for_date( $prev_date, $canonical, $passthru_vars );

		$this->prev_url = $url;

		return $this->filter_prev_url( $canonical, $url );
	}

	/**
	 * {@inheritDoc}
	 */
	public function next_url( $canonical = false, array $passthru_vars = [] ) {
		if ( isset( $this->next_url ) ) {
			return $this->next_url;
		}

		// Setup the Default date for the month view here.
		$default_date = 'today';
		$date         = $this->context->get( 'event_date', $default_date );
		$current_date = Dates::build_date_object( $date );

		if ( $this->skip_empty() ) {
			// The first event that ends after the end of the month; it could still begin in this month.
			$next_event = tribe_events()
				->by_args( $this->filter_repository_args( $this->setup_repository_args() ) )
				->where( 'ends_after', tribe_end_of_day( $current_date->format( 'Y-m-t' ) ) )
				->order( 'ASC' )
				->first();
			if ( ! $next_event instanceof \WP_Post ) {
				return $this->filter_prev_url( $canonical, '' );
			}

			// At a minimum pick the next month or the month the next event starts in.
			$next_date = max(
				$next_event->dates->start,
				$current_date->add( new \DateInterval( 'P1M' ) )
			);
		} else {
			$next_date = Dates::build_date_object( $current_date->format( 'Y-m-01' ) );
			$next_date->add( new \DateInterval( 'P1M' ) );
			// Let's make sure to prevent users from paginating endlessly forward when we know there are no more events.
			$latest = tribe_get_option( 'latest_date', $next_date );
			if ( $current_date->format( 'Y-m' ) === Dates::build_date_object( $latest )->format( 'Y-m' ) ) {
				return $this->filter_prev_url( $canonical, '' );
			}
		}

		$url = $this->build_url_for_date( $next_date, $canonical, $passthru_vars );

		$this->next_url = $url;

		return $this->filter_next_url( $canonical, $url );
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

		$this->user_date = ( new \DateTime( $date ) )->format( 'Y-m' );

		$args['order_by'] = 'event_date';
		$args['order']    = 'ASC';

		return $args;
	}

	/**
	 * Overrides the base implementation to use the Month view custom number of events per day.
	 *
	 * @since 4.9.7
	 *
	 * @return int The Month view number of events per day.
	 */
	protected function get_events_per_day() {
		$events_per_day = $this->context->get( 'month_posts_per_page', 10 );

		/**
		 * Filters the number of events per day to fetch in the Month view.
		 *
		 * @since 4.9.7
		 *
		 * @param int $events_per_day The default number of events that will be fetched for each day.
		 * @param Month_View $this The current Month View instance.
		 */
		return apply_filters( 'tribe_events_views_v2_month_events_per_day', $events_per_day, $this );
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

		$today                                = $this->context->get( 'today' );
		$template_vars['today_date']          = Dates::build_date_object( $today )->format( 'Y-m-d' );
		$template_vars['grid_date']           = $grid_date->format( 'Y-m-d' );
		$template_vars['formatted_grid_date'] = $grid_date->format( $month_and_year_format );
		$template_vars['events']              = $grid_days;
		$template_vars['days']                = $days;

		return $template_vars;
	}

	/**
	 * Parses the multi-day events and produces the multi-day "stack", including spacers.
	 *
	 * @since 4.9.7
	 *
	 * @param array $grid_events_by_day An array of events, per-day, in the shape `[ <Y-m-d> => [...<event_ids> ] ]`;
	 *
	 * @return array An array of all the month days, each entry filled with spacers and/or event post IDs in the correct
	 *               order. E.g.
	 *               `[ '2019-07-01' => [2, 3, false], '2019-07-02' => [2, 3, 4], '2019-07-03' => [false, 3, 4]]`.
	 */
	protected function build_day_stacks( array $grid_events_by_day ) {
		$week_stacks = [];
		foreach ( array_chunk( $grid_events_by_day, 7, true ) as $week_events_by_day ) {
			$week_stacks[] = $this->stack->build_from_events( $week_events_by_day );
		}

		return array_merge( ...$week_stacks );
	}

	/**
	 * Populates the data for each day in the grid and returns it.
	 *
	 * @since 4.9.7
	 *
	 * @param array $grid_days An associative array of events per day, in the shape `[ <Y-m-d> => [...<events>] ]`.
	 *
	 * @return array An associative array of day data for each day in the shape `[ <Y-m-d> => <day_data> ]`.
	 */
	protected function get_days_data( array $grid_days ) {
		$found_events = $this->get_grid_days_counts();

		// The multi-day stack will contain spacers and post IDs.
		$day_stacks = $this->build_day_stacks( $grid_days );

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
			$day_stack = array_map( static function ( $element ) use ( $date_object ) {
				// If it's numeric make an event object of it.
				return is_numeric( $element ) ?
					tribe_get_event( $element, OBJECT, $date_object->format( 'Y-m-d' ) )
					: $element;
			}, Arr::get( $day_stacks, $day_date, [] ) );

			$the_day_events = array_map( 'tribe_get_event',
				array_filter( $day_events, static function ( $event ) use ( $date_object ) {
					$event = tribe_get_event( $event, OBJECT, $date_object->format( 'Y-m-d' ) );

					return $event instanceof \WP_Post && ! ( $event->multiday || $event->all_day );
				} )
			);

			$more_events  = 0;
			$day_found_events = Arr::get( $found_events, $day_date, 0 );

			if ( $day_found_events ) {
				/*
				 * We cannot know before-hand what spacer will be used (it's filterable) so we have to count the events
				 * by keeping only the posts.
				 */
				$stack_events_count = count(
					array_filter(
						$day_stack,
						static function ( $el ) {
							return $el instanceof \WP_Post;
						}
					)
				);
				/*
				 * In the context of the Month View we want to know if there are more events we're not seeing.
				 * So we exclude the ones we see and the multi-day ones that we're seeing in the multi-day stack.
				 */
				$more_events = $day_found_events - $stack_events_count - count( $the_day_events );
			}

			$featured_events = array_map( 'tribe_get_event',
				array_filter( $day_events,
					static function ( $event ) use ( $date_object ) {
						$event = tribe_get_event( $event, OBJECT, $date_object->format( 'Y-m-d' ) );

						return $event instanceof \WP_Post && $event->featured;
					} )
			);

			$start_of_week = get_option( 'start_of_week', 0 );

			$day_url = tribe_events_get_url( [ 'eventDisplay' => 'day', 'eventDate' => $day_date ] );

			$day_data = [
				'date'             => $day_date,
				'is_start_of_week' => $start_of_week === $date_object->format( 'N' ),
				'year_number'      => $date_object->format( 'Y' ),
				'month_number'     => $date_object->format( 'm' ),
				'day_number'       => $date_object->format( 'j' ),
				'events'           => $the_day_events,
				'featured_events'  => $featured_events,
				'multiday_events'  => $day_stack,
				'found_events'     => $day_found_events,
				'more_events'      => $more_events,
				'day_url'          => $day_url,
			];

			$days[ $day_date ] = $day_data;
		}

		return $days;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function calculate_grid_start_end( $date ) {
		$grid_start = Month::calculate_first_cell_date( $date );
		$grid_end   = Month::calculate_final_cell_date( $date );

		return [ Dates::build_date_object( $grid_start ), Dates::build_date_object( $grid_end ) ];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.9
	 */
	protected function get_label_format() {
		// Something like "January".
		return 'F';
	}

	/**
	 * Whether months w/o any event should be skipped while building navigation links or not.
	 *
	 * By default empty months will not be skipped.
	 *
	 * @since 4.9.9
	 *
	 * @return bool Whether to skip empty months or not.
	 */
	protected function skip_empty() {
		/**
		 * Filters whether months w/o any event should be skipped while building navigation links or not.
		 *
		 * @since 4.9.9
		 *
		 * @param bool       $skip_empty   Whether months w/o any event should be skipped while building
		 *                                 navigation links or not; defaults to `false`.
		 * @param Month_View $this         This Month View instance.
		 */
		return (bool) apply_filters( 'tribe_events_views_v2_month_nav_skip_empty', false, $this );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_url_date_format() {
		return 'Y-m';
	}
}
