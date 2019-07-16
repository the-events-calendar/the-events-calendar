<?php
/**
 * The Month View.
 *
 * @since   4.9.3
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe__Context as Context;
use Tribe__Date_Utils as Date_Utils;
use Tribe__Events__Template__Month as Month;
use Tribe__Timezones as Timezones;
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

		$this->grid_days = [];
		/** @var \DateTime $day */
		foreach ( $days as $day ) {
			$start = clone $day->setTime( 0, 0, 0 );
			$end   = clone $day->setTime( 23, 59, 59 );

			$event_ids = tribe_events()
				->by_args( $this->repository_args )
				->where( 'date_overlaps', $start, $end )
				->get_ids();

			$this->grid_days[ $day->format( 'Y-m-d' ) ] = $event_ids;
		}

		return $this->grid_days;
	}

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
		$this->repository->order_by( 'event_date', 'ASC' );

		/*
		 * The event fetching will happen day-by-day so we set here the repository args we'll re-use fetching each
		 * day events.
		 */
		$this->repository_args = $args;

		return $args;
	}

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

		// Let's prepare an array of days more digestible by the templates.
		$days = [];
		foreach ( $events as $day_date => $day_events ) {
			$date_object = Date_Utils::build_date_object( $day_date );

			$days[] = [
				'date'                => $day_date, // Y-m-d
				'year_number'                => (int) $date_object->format( 'Y' ),
				'month_number'               => (int) $date_object->format( 'm' ),
				'day_number'                 => (int) $date_object->format( 'd' ),
				'events'              => $day_events, // Would include spacers.
				'featured_events' => array_filter( $day_events ), static function ( \WP_Post $event ) {
					return $event->featured;
				},
				'multiday_events' => [],
			];
		}

		$template_vars['events'] = $events;
		$template_vars['days']   = $days;

		return $template_vars;
	}
}
