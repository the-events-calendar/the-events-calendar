<?php
/**
 * The Day View.
 *
 * @package Tribe\Events\Views\V2\Views
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\Url;
use Tribe\Events\Views\V2\View;
use Tribe__Date_Utils as Dates;
use Tribe__Utils__Array as Arr;

class Day_View extends View {
	/**
	 * Slug for this view
	 *
	 * @since 4.9.4
	 *
	 * @var string
	 */
	protected $slug = 'day';

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

		$date = $this->context->get( 'event_date', $this->context->get( 'today', 'today' ) );

		$one_day       = new \DateInterval( 'P1D' );
		$url_date      = Dates::build_date_object( $date )->sub( $one_day );
		$earliest      = tribe_get_option( 'earliest_date', $url_date );
		$earliest_date = Dates::build_date_object( $earliest )->setTime( 0, 0, 0 );

		if ( $url_date < $earliest_date ) {
			$url = '';
		} else {
			$url = $this->build_url_for_date( $url_date, $canonical, $passthru_vars );
		}

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
		$date = $this->context->get( 'event_date', $this->context->get( 'today', 'today' ) );

		$one_day     = new \DateInterval( 'P1D' );
		$url_date    = Dates::build_date_object( $date )->add( $one_day );
		$latest      = tribe_get_option( 'latest_date', $url_date );
		$latest_date = Dates::build_date_object( $latest )->setTime( 0, 0, 0 );

		if ( $url_date > $latest_date ) {
			$url = '';
		} else {
			$url = $this->build_url_for_date( $url_date, $canonical, $passthru_vars );
		}

		$this->next_url = $url;

		return $this->filter_next_url( $canonical, $url );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( \Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$args = parent::setup_repository_args( $context );

		$context_arr = $context->to_array();

		$date = Arr::get( $context_arr, 'event_date', 'now' );
		$event_display = Arr::get( $context_arr, 'event_display_mode', Arr::get( $context_arr, 'event_display' ), 'current' );

		$args['date_overlaps'] = [ tribe_beginning_of_day( $date ), tribe_end_of_day( $date ) ];

		return $args;
	}

	/**
	 * Builds the Day View URL for a specific date.
	 *
	 * This is the method underlying the construction of the previous and next URLs.
	 *
	 * @since TBD
	 *
	 * @param mixed $url_date          The date to build the URL for, a \DateTime object, a string or a timestamp.
	 * @param bool  $canonical         Whether to return the canonical (pretty) version of the URL or not.
	 * @param array $passthru_vars     An optional array of query variables that should pass thru the method untouched
	 *                                 in key in value.
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
			$input_url = add_query_arg( [ 'eventDisplay' => $this->slug ], $input_url );

			$canonical_url = tribe( 'events.rewrite' )->get_clean_url( $input_url );

			if ( ! empty( $passthru_vars ) ) {
				$canonical_url = add_query_arg( $passthru_vars, $canonical_url );
			}

			$url = $canonical_url;
		}

		return $url;
	}
}
