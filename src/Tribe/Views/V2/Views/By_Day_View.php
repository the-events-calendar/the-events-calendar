<?php
/**
 * The parent, abstract, View that models a view rendering on a per-day basis.
 *
 * @since   4.9.7
 *
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use DateTimeInterface;
use Tribe\Events\Models\Post_Types\Event;
use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\Repository\Event_Period;
use Tribe\Events\Views\V2\Utils\Stack;
use Tribe\Events\Views\V2\View;
use Tribe\Traits\Cache_User;
use Tribe\Utils\Taxonomy;
use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;
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

	const CHUNK_SIZE = 200;

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
	 * An array of cached events for the grid.
	 *
	 * @since 5.0.0
	 *
	 * @var array
	 */
	protected $grid_events = [];

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
		$date_formatted = Dates::build_date_object( $date );

		if (
			! $force
			&& ! empty( $this->grid_days_cache )
			&& isset( $this->user_date )
			&& ( ! $date || $this->user_date === $date_formatted->format( Dates::DBDATEFORMAT ) )
		) {
			return $this->grid_days_cache;
		}

		if ( empty( $this->user_date ) ) {
			$this->user_date = $date ?: $this->context->get( 'event_date', 'now' );
		}

		[ $grid_start, $grid_end ] = $this->calculate_grid_start_end( $this->user_date );

		/**
		 * Allows injecting the View grid days before any default logic runs.
		 *
		 * Note: this filter assumes the filtering function will handle any additional filtering criteria
		 * the View might be required based on the Context.
		 *
		 * @since  5.7.0
		 *
		 * @param array<string,array<int>>|null The View grid days in a map from the day date in `Y-m-d` format to the
		 *                                      list of Event post IDs matching the search criteria for each day.
		 * @param DateTimeInterface $grid_start The View grid start date.
		 * @param DateTimeInterface $grid_end   The View grid end date.
		 * @param By_Day_View       $this       A reference to the View instance that has fired this filter.
		 */
		$grid_days = apply_filters( 'tribe_events_views_v2_by_day_view_grid_days', null, $grid_start, $grid_end, $this );

		if ( null !== $grid_days ) {
			return $grid_days;
		}

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
			 * If repository arguments have not been set up yet, let's do it now.
			 */
			$this->repository_args = $this->filter_repository_args( $this->setup_repository_args( $this->context ) );
		}

		$repository_args = $this->repository_args;
		$order_by        = Arr::get( $repository_args, 'order_by', 'event_date' );
		$order           = Arr::get( $repository_args, 'order', 'ASC' );
		unset( $repository_args['order_by'], $repository_args['order'] );

		$this->warmup_cache( 'grid_days', 0, Cache_Listener::TRIGGER_SAVE_POST );
		$this->warmup_cache( 'grid_days_found', 0, Cache_Listener::TRIGGER_SAVE_POST );

		// @todo [BTRIA-599]: Remove this when the Event_Period repository is solid and cleaned up.
		$using_period_repository = tribe_events_view_v2_use_period_repository();
		$use_site_timezone       = Timezones::is_mode( 'site' );

		if ( $using_period_repository ) {
			/** @var Event_Period $repository */
			if ( tribe_is_truthy( tribe_get_option( 'enable_month_view_cache', false ) ) ) {
				$repository = tribe_events( 'period', 'caching' );
			} else {
				$repository = tribe_events( 'period' );
			}
			$repository->by_period( $grid_start_date, $grid_end_date )->fetch();
		} else {
			global $wpdb;

			$first_grid_day = $days->start;
			$start          = tribe_beginning_of_day( $first_grid_day->format( Dates::DBDATETIMEFORMAT ) );
			$last_grid_day  = $days->end;
			$end            = tribe_end_of_day( $last_grid_day->format( Dates::DBDATETIMEFORMAT ) );

			/*
			 * Sort events in duration ascending order to make sure events that start on the same date and time
			 * will be correctly positioned for multi-day, or all-day, parsing.
			 * If not explicit, then events with the same start date and time would be sorted in the order MySQL
			 * read them (not guaranteed).
			 */
			$order_by                   = tribe_normalize_orderby( $order_by, $order );
			$order_by['event_duration'] = 'ASC';

			$events_repository = tribe_events()
				->set_found_rows( true )
				->fields( 'ids' )
				->by_args( $repository_args )
				->where( 'date_overlaps', $start, $end, null, 2 )
				->per_page( - 1 )
				->order_by( $order_by, $order );

			/**
			 * Allows modifications to the repository, which allows specific modifications to the grid query.
			 *
			 * @since  5.8.2
			 *
			 * @param \Tribe__Repository__Interface $events_repository The Event repository we are going to filter.
			 * @param DateTimeInterface             $grid_start        The View grid start date.
			 * @param DateTimeInterface             $grid_end          The View grid end date.
			 * @param By_Day_View                   $this              A reference to the View instance that has fired this filter.
			 */
			$events_repository = apply_filters( 'tribe_events_views_v2_by_day_view_day_repository', $events_repository, $grid_start, $grid_end, $this );

			$view_event_ids = $events_repository->all();

			/**
			 * Allows filtering the formatted day results before the default logic kicks in and after all the
			 * matching Event post IDs have been found.
			 *
			 * @since 5.7.0
			 *
			 * @param null|array<int,\stdClass> $day_results    A map from each event Post ID to the value object that
			 *                                                  will represent the Event ID, start date, end date and
			 *                                                  timezone.
			 * @param array<int>                $view_event_ids The set of Event Post IDs to build and format the Day
			 * @param By_Day_View               $this           A reference to the `By_Day_View` instance that is applying the
			 *                                                  filter.
			 */
			$day_results = apply_filters( 'tribe_events_views_v2_by_day_view_day_results', null, $view_event_ids, $this );

			if ( null === $day_results ) {
				$day_results = $this->prepare_day_results( $view_event_ids, $use_site_timezone );
			}
		}

		$all_day_event_ids = [];
		$site_timezone     = Timezones::build_timezone_object();
		$utc               = Timezones::build_timezone_object( 'UTC' );

		// phpcs:ignore
		/** @var \Tribe\Utils\Date_I18n $day */
		foreach ( $days as $day ) {
			$day_string = $day->format( 'Y-m-d' );

			if ( $using_period_repository && isset( $repository ) ) {
				$day_results   = $repository->by_date( $day_string )->get_set();
				$day_event_ids = [];

				$event_ids = [];
				if ( $day_results->count() ) {
					// Sort events by honoring order and direction.
					$day_results->order_by( $order_by, $order );
					$event_ids = array_map( 'absint', $day_results->pluck( 'ID' ) );
				}

				$this->grid_days_cache[ $day_string ]       = array_values( $day_event_ids );
				$this->grid_days_found_cache[ $day_string ] = $day_results->count();
			} else {
				$start = tribe_beginning_of_day( $day->format( Dates::DBDATETIMEFORMAT ) );
				$end   = tribe_end_of_day( $day->format( Dates::DBDATETIMEFORMAT ) );

				// Events overlap a day if Event start date <= Day End AND Event end date >= Day Start.
				$results_in_day = array_filter(
					$day_results,
					static function ( $event ) use ( $start, $end, $use_site_timezone, $site_timezone, $utc ) {
						// If the timezone setting is set to "manual timezone for each event" then this is correct.
						if ( ! $use_site_timezone ) {
							return $event->start_date <= $end && $event->end_date >= $start;
						}

						// If the timezone setting is set to "site-wide timezone setting" then this is NOT correct.
						// What we should do is:
						// * use the event UTC time
						// * convert it to the current site timezone
						// * check if the event fits into the day, given shifted start and end of day
						$event_localized_start_date = Dates::build_date_object( $event->start_date, $utc )
						                                   ->setTimezone( $site_timezone );
						$event_localized_end_date   = Dates::build_date_object( $event->end_date, $utc )
						                                   ->setTimezone( $site_timezone );

						return $event_localized_start_date->format( Dates::DBDATETIMEFORMAT ) <= $end
						       && $event_localized_end_date->format( Dates::DBDATETIMEFORMAT ) >= $start;
					}
				);

				$day_event_ids = array_map( 'absint', wp_list_pluck( $results_in_day, 'ID' ) );

				$this->grid_days_cache[ $day_string ]       = array_values( $day_event_ids );
				$this->grid_days_found_cache[ $day_string ] = count( $results_in_day );
			}

			$all_day_event_ids = array_merge( $all_day_event_ids, $day_event_ids );
		}

		$this->grid_events = $this->get_grid_events( $all_day_event_ids );

		/**
		 * Multi-day events will always appear on the second day and forward, back-fill if they did not make the
		 * cut (of events per day) on previous days.
		 */
		$this->backfill_multiday_event_ids( $this->grid_events );

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
		$this->fill_week_duration_cache();

		Taxonomy::prime_term_cache( $this->grid_events );
		Event::prime_cache( $this->grid_events );

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

		$view_slug = static::get_view_slug();

		/**
		 * Filters the number of events per day to fetch in th View.
		 *
		 * @since 4.9.7
		 *
		 * @param int         $events_per_day The default number of events that will be fetched for each day.
		 * @param By_Day_View $this           The current View instance.
		 */
		return apply_filters( "tribe_events_views_v2_{$view_slug}_events_per_day", $events_per_day, $this );
	}

	/**
	 * Gets the current desired chunk size for breaking up batched queries.
	 *
	 * @since 5.0.0
	 *
	 * @return int
	 */
	protected function get_chunk_size() {
		/**
		 * Filters the chunk size used for building grid dates.
		 *
		 * @since 5.0.0
		 *
		 * @param int             $chunk_size Max number of values to query at a time.
		 * @param \Tribe__Context $context    Context of request.
		 * @param By_Day_View     $view       Current view object.
		 */
		return apply_filters( 'tribe_events_views_v2_by_day_view_chunk_size', self::CHUNK_SIZE, $this->get_context(), $this );
	}

	/**
	 * Fetches events for the grid in chunks so we do not have to fetch events a second time.
	 *
	 * @since 5.0.0
	 *
	 * @param array $event_ids
	 *
	 * @return array|void
	 */
	protected function get_grid_events( array $event_ids = [] ) {
		if ( empty( $event_ids ) ) {
			return [];
		}

		$events = [];

		$event_id_chunks = array_chunk( $event_ids, $this->get_chunk_size() );
		foreach ( $event_id_chunks as $ids ) {
			// Prefetch provided events in a single query.
			$event_results = tribe_events()
				->in( $ids )
				->per_page( - 1 )
				->all();

			// Massage events to be indexed by event ID.
			foreach ( $event_results as $event_result ) {
				$events[ $event_result->ID ] = $event_result;
			}
		}

		return $events;
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
	 * @param array $events An array of event posts
	 */
	protected function backfill_multiday_event_ids( array $events = [] ) {
		if ( empty( $events ) ) {
			return;
		}

		try {
			$one_day = new \DateInterval( 'P1D' );
		} catch ( \Exception $e ) {
			return;
		}

		$event_ids = wp_list_pluck( $events, 'ID' );

		foreach ( $event_ids as $event_id ) {
			$event = $events[ $event_id ];

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

			$site_timezone     = Timezones::build_timezone_object();
			$utc               = Timezones::build_timezone_object( 'UTC' );
			$use_site_timezone = Timezones::is_mode( 'site' );

			/** @var \DateTime $event_day */
			foreach ( $event_period as $event_day ) {
				$event_day_string = $event_day->format( Dates::DBDATEFORMAT );
				$start            = tribe_beginning_of_day( $event_day->format( Dates::DBDATETIMEFORMAT ) );
				$end              = tribe_end_of_day( $event_day->format( Dates::DBDATETIMEFORMAT ) );

				if ( ! isset( $this->grid_days_cache[ $event_day_string ] ) ) {
					continue;
				}

				// If the timezone setting is set to "manual timezone for each event" then this is correct.
				if ( ! $use_site_timezone ) {
					$should_backfill = $event->start_date <= $end && $event->end_date >= $start;
				} else {
					// If the timezone setting is set to "site-wide timezone setting" then this is NOT correct.
					// What we should do is:
					// * use the event UTC time
					// * convert it to the current site timezone
					// * check if the event fits into the day, given shifted start and end of day
					$event_localized_start_date = Dates::build_date_object( $event->start_date, $utc )
					                                   ->setTimezone( $site_timezone );
					$event_localized_end_date   = Dates::build_date_object( $event->end_date, $utc )
					                                   ->setTimezone( $site_timezone );

					$should_backfill = $event_localized_start_date->format( Dates::DBDATETIMEFORMAT ) <= $end
					                   && $event_localized_end_date->format( Dates::DBDATETIMEFORMAT ) >= $start;
				}

				if ( $should_backfill && ! in_array( $event_id, $this->grid_days_cache[ $event_day_string ], true ) ) {
					$this->grid_days_cache[ $event_day_string ][] = $event_id;
					// No need to update the found cache: that's already taking this event into account.
				}
			}
		}
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

	protected function fill_week_duration_cache() {
		$cache       = tribe( 'cache' );
		$occurrences = [ 'first' => [], 'last' => [], 'count' => [] ];
		foreach ( $this->grid_days_cache as $day => $events ) {
			foreach ( $events as $event ) {
				if ( ! isset( $occurrences['first'][ $event ] ) ) {
					$occurrences['first'][ $event ] = $day;
				}

				if ( isset( $occurrences['count'][ $event ] ) ) {
					$occurrences['count'][ $event ] += 1;
				} else {
					$occurrences['count'][ $event ] = 1;
				}

				$occurrences['last'][ $event ] = $day;
			}
		}
		$displays_on = [];
		foreach ( array_chunk( $this->grid_days_cache, 7, true ) as $week ) {
			$week_days  = array_keys( $week );
			$week_start = reset( $week_days );
			$week_end   = end( $week_days );
			foreach ( $week as $day => $events ) {
				foreach ( $events as $event ) {
					$cache_key             = $event . '_' . $week_start . '_week';
					$happens_this_week     = true;
					$event_obj             = tribe_get_event( $event );
					$event_start           = ! empty( $event_obj->dates->start_display ) ? $event_obj->dates->start_display->format( Dates::DBDATEFORMAT ) : '';
					$event_end             = ! empty( $event_obj->dates->end_display ) ? $event_obj->dates->end_display->format( Dates::DBDATEFORMAT ) : '';
					$starts_this_week      = $occurrences['first'][ $event ] >= $week_start && $event_start >= $week_start;
					$ends_this_week        = $occurrences['last'][ $event ] <= $week_end && $event_end <= $week_end;
					$displays_on[ $event ] = [];
					$this_week_duration    = 7;

					if ( $starts_this_week && $ends_this_week ) {
						$this_week_duration      = $occurrences['count'][ $event ];
						$displays_on[ $event ][] = $occurrences['first'][ $event ];
					} elseif ( $starts_this_week ) {
						$this_week_duration      = Dates::date_diff( $week_end, $occurrences['first'][ $event ] ) + 1;
						$displays_on[ $event ][] = $occurrences['first'][ $event ];
					} elseif ( $ends_this_week ) {
						$this_week_duration      = Dates::date_diff( $occurrences['last'][ $event ], $week_start ) + 1;
						$displays_on[ $event ][] = $week_start;
					}

					$data                = [
						$happens_this_week,
						$starts_this_week,
						$ends_this_week,
						min( 7, $this_week_duration ),
						$displays_on[ $event ],
					];
					$cache[ $cache_key ] = $data;
				}
			}
		}
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
			$input_url = add_query_arg( [ 'eventDisplay' => static::$view_slug ], $input_url );

			$canonical_url = tribe( 'events.rewrite' )->get_clean_url( $input_url );

			if ( ! empty( $passthru_vars ) ) {
				$canonical_url = add_query_arg( $passthru_vars, $canonical_url );
			}

			$url = $canonical_url;
		}

		return $url;
	}

	/**
	 * Formats the day results in the format expected for day-by-day grid building.
	 *
	 * The method will fetch the required data in chunks to avoid overloading the database.
	 *
	 * @since 5.7.0
	 *
	 * @param array<int> $view_event_ids    The set of Event Post IDs to build and format the Day
	 * @param bool       $use_site_timezone Whether to use the site timezone to format the event dates or not. The value
	 *                                      descends from the "Timezone Mode" setting.
	 *
	 * @return array<int,\stdClass> A map from each event Post ID to the value object that will represent
	 *                              the Event ID, start date, end date and timezone.
	 */
	protected function prepare_day_results( array $view_event_ids, $use_site_timezone ) {
		$day_results = [];

		$start_meta_key = '_EventStartDate';
		$end_meta_key   = '_EventEndDate';

		if ( $use_site_timezone ) {
			$start_meta_key = '_EventStartDateUTC';
			$end_meta_key   = '_EventEndDateUTC';
		}

		$results_buffer = [];
		$request_chunks = array_chunk( $view_event_ids, $this->get_chunk_size() );
		global $wpdb;

		foreach ( $request_chunks as $chunk_ids ) {
			$sql = "
					SELECT
					  post_id,
						meta_key,
						meta_value
					FROM
						{$wpdb->postmeta}
					WHERE
						meta_key IN ( %s, %s, %s )
						AND post_id IN ( " . implode( ',', $chunk_ids ) . " )
				";

			$chunk_results = $wpdb->get_results( $wpdb->prepare( $sql,
				[ $start_meta_key, $end_meta_key, '_EventTimezone' ] ) );

			$results_buffer[] = $chunk_results;
		}

		$results = count( $results_buffer ) ? array_merge( ...$results_buffer ) : [];

		$indexed_results = [];

		foreach ( $results as $row ) {
			if ( ! isset( $indexed_results[ $row->post_id ] ) ) {
				$indexed_results[ $row->post_id ] = [
					'ID'         => $row->post_id,
					'start_date' => null,
					'end_date'   => null,
					'timezone'   => null,
				];
			}

			$map = [
				$start_meta_key  => 'start_date',
				$end_meta_key    => 'end_date',
				'_EventTimezone' => 'timezone',
			];

			$key = Arr::get( $map, $row->meta_key );

			$indexed_results[ $row->post_id ][ $key ] = $row->meta_value;
		}

		foreach ( $view_event_ids as $id ) {
			$day_results[] = (object) $indexed_results[ $id ];
		}

		return $day_results;
	}

	/**
	 * Overrides the base View implementation to limit the results to the View grid.
	 *
	 * {@inheritdoc}
	 */
	protected function setup_ical_repository_args( $per_page ) {
		if ( empty( $this->repository_args ) ) {
			$this->repository->by_args( $this->get_repository_args() );
		}
		$this->repository->per_page( $per_page );
		[ $start_date, $end_date ] = $this->calculate_grid_start_end( $this->context->get( 'event_date', 'now' ) );
		$this->repository->where( 'ends_after', $start_date );
		$this->repository->where( 'starts_before', $end_date );
	}

	/**
	 * Overrides the base View implementation to remove pagination from the URL.
	 *
	 * {@inheritdoc}
	 */
	public function url_for_query_args( $date = null, $query_args = [] ) {
		// If the query arguments are passed as a string, convert them to an array.
		if ( ! is_array( $query_args ) ) {
			parse_str( $query_args, $query_args );
		}

		// Remove the 'page' and 'paged' query parameters from the array of query arguments.
		unset( $query_args[ 'page' ], $query_args[ 'paged' ] );

		// Call the parent class's 'url_for_query_args' method with the updated query arguments.
		return parent::url_for_query_args( $date, $query_args );
	}
}
