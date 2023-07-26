<?php
/**
 * The Day View.
 *
 * @package Tribe\Events\Views\V2\Views
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\Url;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Traits\With_Fast_Forward_Link;
use Tribe__Date_Utils as Dates;
use Tribe__Utils__Array as Arr;
use Tribe__Context;

use DateTime;

class Day_View extends View {
	use With_Fast_Forward_Link;

	/**
	 * Slug for this view
	 *
	 * @since 4.9.4
	 * @deprecated 6.0.7
	 *
	 * @var string
	 */
	protected $slug = 'day';

	/**
	 * Statically accessible slug for this view.
	 *
	 * @since 6.0.7
	 *
	 * @var string
	 */
	protected static $view_slug = 'day';

	/**
	 * Cached dates for the prev/next links.
	 *
	 * @since 5.16.1
	 *
	 * @var array
	 */
	protected array $memoized_dates = [];

	/**
	 * Visibility for this view.
	 *
	 * @since 4.9.4
	 * @since 4.9.11 Made the property static.
	 *
	 * @var bool
	 */
	protected static $publicly_visible = true;

	/**
	 * Default untranslated value for the label of this view.
	 *
	 * @since 6.0.4
	 *
	 * @var string
	 */
	protected static $label = 'Day';

	/**
	 * @inheritDoc
	 */
	public static function get_view_label(): string {
		static::$label = _x( 'Day', 'The text label for the Day View.', 'the-events-calendar' );

		return static::filter_view_label( static::$label );
	}

	/**
	 * Get the date of the event immediately previous to the current view date.
	 *
	 * @since 5.16.1
	 *
	 * @param DateTime|false $current_date A DateTime object signifying the current date for the view.
	 *
	 * @return DateTime|false Either the previous event chronologically, the previous month, or false if no next event found.
	 */
	public function get_previous_event_date( $current_date ) {
		$context = $this->context instanceof Tribe__Context ? $this->context : null;
		$args = $this->filter_repository_args( $this->setup_repository_args( $context ) );
		// This value will mess up our query.
		unset( $args['date_overlaps'] );

		// Use cache to reduce the performance impact.
		$cache_key = __METHOD__ . '_' . substr( md5( wp_json_encode( [ $current_date, $args ] ) ), 10 );

		if ( isset( $this->memoized_dates[ $cache_key ] ) ) {
			return $this->memoized_dates[ $cache_key ];
		}

		// When dealing with previous event date we only fetch one.
		$args['posts_per_page'] = 1;

		// Find the first event that starts before the start of today.
		$prev_event = tribe_events()
			->by_args( $args )
			->where( 'starts_before', tribe_beginning_of_day( $current_date->format( 'Y-m-d' ) ) )
			->order( 'DESC' )
			->first();

		if ( ! $prev_event instanceof \WP_Post ) {
			$this->memoized_dates[ $cache_key ] = false;

			return false;
		}

		// Show the closest date on which that event appears (but not the current date).
		$prev_event_date = Dates::build_date_object( $prev_event->dates->start );
		$prev_date       = min(
			$prev_event_date,
			$current_date->sub( new \DateInterval( 'P1D' ) )
		);

		$this->memoized_dates[ $cache_key ] = $prev_date;

		return $prev_date;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prev_url( $canonical = false, array $passthru_vars = [] ) {
		$cache_key = __METHOD__ . '_' . md5( wp_json_encode( func_get_args() ) );

		if ( isset( $this->cached_urls[ $cache_key ] ) ) {
			return $this->cached_urls[ $cache_key ];
		}

		$date = $this->context->get( 'event_date', $this->context->get( 'today', 'today' ) );

		$one_day       = new \DateInterval( 'P1D' );
		$url_date      = Dates::build_date_object( $date )->sub( $one_day );
		$earliest      = $this->context->get( 'earliest_event_date', $url_date );
		$earliest_date = Dates::build_date_object( $earliest )->setTime( 0, 0, 0 );

		if ( $url_date < $earliest_date ) {
			$url = '';
		} else {
			$url = $this->build_url_for_date( $url_date, $canonical, $passthru_vars );
		}

		$url = $this->filter_prev_url( $canonical, $url );

		$this->cached_urls[ $cache_key ] = $url;

		return $url;
	}

	/**
	 * Get the date of the event immediately after to the current view date.
	 *
	 * @since 5.16.1
	 *
	 * @param DateTime|false $current_date A DateTime object signifying the current date for the view.
	 *
	 * @return DateTime|false Either the next event chronologically, the next month, or false if no next event found.
	 */
	public function get_next_event_date( $current_date ) {
		$context = $this->context instanceof Tribe__Context ? $this->context : null;
		$args = $this->filter_repository_args( $this->setup_repository_args( $context ) );
		// This value will mess up our query.
		unset( $args['date_overlaps'] );

		// Use cache to reduce the performance impact.
		$cache_key = __METHOD__ . '_' . substr( md5( wp_json_encode( [ $current_date, $args ] ) ), 10 );

		if ( isset( $this->memoized_dates[ $cache_key ] ) ) {
			return $this->memoized_dates[ $cache_key ];
		}

		// For the next event date we only care about 1 item.
		$args['posts_per_page'] = 1;

		// The first event that ends after the end of the month; it could still begin in this month.
		$next_event = tribe_events()
			->by_args( $args )
			->where( 'starts_after', tribe_end_of_day( $current_date->format( 'Y-m-d' ) ) )
			->order( 'ASC' )
			->first();

		if ( ! $next_event instanceof \WP_Post ) {
			$this->memoized_dates[ $cache_key ] = false;

			return false;
		}

		// At a minimum pick the next month or the month the next event starts in.
		$next_date = max(
			Dates::build_date_object( $next_event->dates->start ),
			$current_date->add( new \DateInterval( 'P1D' ) )
		);

		$this->memoized_dates[ $cache_key ] = $next_date;

		return $next_date;
	}

	/**
	 * {@inheritDoc}
	 */
	public function next_url( $canonical = false, array $passthru_vars = [] ) {
		$cache_key = __METHOD__ . '_' . md5( wp_json_encode( func_get_args() ) );

		if ( isset( $this->cached_urls[ $cache_key ] ) ) {
			return $this->cached_urls[ $cache_key ];
		}

		$date = $this->context->get( 'event_date', $this->context->get( 'today', 'today' ) );

		$one_day     = new \DateInterval( 'P1D' );
		$url_date    = Dates::build_date_object( $date )->add( $one_day );
		$latest      = $this->context->get( 'latest_event_date', $url_date );
		$latest_date = Dates::build_date_object( $latest )->setTime( 0, 0, 0 );

		if ( $url_date > $latest_date ) {
			$url = '';
		} else {
			$url = $this->build_url_for_date( $url_date, $canonical, $passthru_vars );
		}

		$url = $this->filter_next_url( $canonical, $url );

		$this->cached_urls[ $cache_key ] = $url;

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( \Tribe__Context $context = null ) {

		$context = null !== $context ? $context : $this->context;

		$args = parent::setup_repository_args( $context );

		$context_arr = $context->to_array();

		$date = Arr::get( $context_arr, 'event_date', 'now' );

		$current_date          = Dates::build_date_object( $date );
		$args['date_overlaps'] = [
			tribe_beginning_of_day( $current_date->format( 'Y-m-d' ) ),
			tribe_end_of_day( $current_date->format( 'Y-m-d' ) )
		];

		/**
		 * @todo  @bordoni We need to consider fetching events on a given day from a cache
		 *        base on what @lucatume suggested on dev meeting for caching more efficiently.
		 */
		$args['posts_per_page'] = -1;

		return $args;
	}

	/**
	 * Builds the Day View URL for a specific date.
	 *
	 * This is the method underlying the construction of the previous and next URLs.
	 *
	 * @since 4.9.10
	 *
	 * @param mixed $url_date          The date to build the URL for, a \DateTime object, a string or a timestamp.
	 * @param bool  $canonical         Whether to return the canonical (pretty) version of the URL or not.
	 * @param array $passthru_vars     An optional array of query variables that should pass thru the method untouched
	 *                                 in key and value.
	 *
	 * @return string The Day View URL for the date.
	 */
	protected function build_url_for_date( $url_date, $canonical, array $passthru_vars = [] ) {
		$url_date        = Dates::build_date_object( $url_date );
		$url             = new Url( $this->get_url() );
		$date_query_args = (array) $url->get_query_args_aliases_of( 'event_date', $this->context );

		$url             = add_query_arg(
			[ 'eventDate' => $url_date->format( Dates::DBDATEFORMAT ) ],
			remove_query_arg( $date_query_args, $this->get_url() )
		);

		if ( ! empty( $url ) && $canonical ) {
			$input_url = $url;

			if ( ! empty( $passthru_vars ) ) {
				$input_url = remove_query_arg( array_keys( $passthru_vars ), $url );
			}

			// Make sure the view slug is always set to correctly match rewrites.
			$input_url     = add_query_arg( [ 'eventDisplay' => static::$view_slug ], $input_url );
			$canonical_url = tribe( 'events.rewrite' )->get_clean_url( $input_url );

			if ( ! empty( $passthru_vars ) ) {
				$canonical_url = add_query_arg( $passthru_vars, $canonical_url );
			}

			$url = $canonical_url;
		}

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_template_vars() {
		$template_vars   = parent::setup_template_vars();
		$sorted_events   = $this->sort_events( $template_vars['events'] );

		$next_date       = Dates::build_date_object( $template_vars['url_event_date'] )->add( new \DateInterval( 'P1D' ) )->format( 'Y-m-d' );
		$prev_date       = Dates::build_date_object( $template_vars['url_event_date'] )->sub( new \DateInterval( 'P1D' ) )->format( 'Y-m-d' );

		$next_event_date = $this->get_next_event_date( Dates::build_date_object( $template_vars['url_event_date'] ) );
		$prev_event_date = $this->get_previous_event_date( Dates::build_date_object( $template_vars['url_event_date'] ) );

		$index_next_rel  = $next_event_date && $next_date === $next_event_date->format( 'Y-m-d' );
		$index_prev_rel  = $prev_event_date && $prev_date === $prev_event_date->format( 'Y-m-d' );

		$next_rel        = $index_next_rel ? 'next' : 'noindex';
		$prev_rel        = $index_prev_rel ? 'prev' : 'noindex';

		$template_vars['events']   = $sorted_events;
		$template_vars['next_rel'] = $next_rel;
		$template_vars['prev_rel'] = $prev_rel;

		return $template_vars;
	}

	/**
	 * Add time slot and sort events for the day view.
	 *
	 * Iterate over the day events to add time slots and sort them.
	 *
	 * @since 4.9.11
	 *
	 * @param array $events  An array of events.
	 *
	 * @return array The sorted and modified array.
	 */
	protected function sort_events( $events ) {

		$all_day = [];
		$ongoing = [];
		$hourly  = [];

		$today        = Dates::build_date_object( $this->context->get( 'today', 'today' ) );
		$request_date = $this->context->get( 'event_date', $today->format( Dates::DBDATEFORMAT ) );

		foreach ( $events as $i => $event ) {
			if ( ! empty( $event->all_day ) ) {
				$event->timeslot = 'all_day';
				$all_day[ $i ]   = $event;
			} elseif ( ! empty( $event->multiday ) && $event->dates->start_display->format( Dates::DBDATEFORMAT ) !== $request_date ) {
				$event->timeslot = 'multiday';
				$ongoing[ $i ]   = $event;
			} else {
				$event->timeslot = null;
				$hourly[ $i ]    = $event;
			}
		}

		return array_values( $all_day + $ongoing + $hourly );

	}

	/**
	 * Overrides the base View method to implement logic tailored to the Day View.
	 *
	 * @since 4.9.11
	 *
	 * @param array $events An array of the View events, if any.
	 */
	protected function setup_messages( array $events ) {
		if ( empty( $events ) ) {
			$keyword = $this->context->get( 'keyword', false );

			if ( $keyword ) {
				$this->messages->insert( Messages::TYPE_NOTICE, Messages::for_key( 'no_results_found_w_keyword', esc_html( trim( $keyword ) ) ) );

				return;
			}

			$date_time  = Dates::build_date_object( $this->context->get( 'event_date', 'today' ) );
			$date_label = date_i18n(
				tribe_get_date_format( true ),
				$date_time->getTimestamp() + $date_time->getOffset()
			);

			$fast_forward_link = $this->get_fast_forward_link( true );

			if ( ! empty( $fast_forward_link ) ) {
				$this->messages->insert(
					Messages::TYPE_NOTICE,
					Messages::for_key( 'day_no_results_found_w_ff_link', $date_label, $fast_forward_link )
				);

				return;
			}

			$message_key = $this->upcoming_events_count() ? 'day_no_results_found' : 'no_upcoming_events';
			$this->messages->insert( Messages::TYPE_NOTICE, Messages::for_key( $message_key, $date_label ) );
		}
	}
}
