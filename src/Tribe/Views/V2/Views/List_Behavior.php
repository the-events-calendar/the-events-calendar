<?php
/**
 * Provides methods for Views that behave like a List View.
 *
 * @since   4.9.11
 *
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;
use Tribe__Utils__Array as Arr;

/**
 * Trait List_Behavior
 *
 * @since   4.9.11
 *
 * @package Tribe\Events\Views\V2\Views
 *
 * @property Context $context The current View context.
 */
trait List_Behavior {

	/**
	 * Set up the datepicker-related template vars for the View.
	 *
	 * @since 4.9.11
	 *
	 * @param array $template_vars The current template vars.
	 *
	 * @return array The template vars, modified to add the required variables.
	 */
	protected function setup_datepicker_template_vars( array $template_vars ) {
		if ( ! isset( $this->context ) ) {
			return $template_vars;
		}

		$now                 = Dates::build_date_object( Arr::get( $template_vars, 'now', 'now' ) );
		$today               = Dates::build_date_object( Arr::get( $template_vars, 'today', 'today' ) );
		// This could yield an empty string, that we want to discard to keep "now" if that's the case.
		$bar_date            = Arr::get( $template_vars, [ 'bar', 'date' ] ) ?: $now;
		$user_date           = Dates::build_date_object( $bar_date );
		$compact_date_format = ! empty( $template_vars['date_formats']->compact )
			? $template_vars['date_formats']->compact
			: Dates::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );
		$now_text            = _x( 'Now', 'The datepicker label when the range start is now.', 'the-events-calendar' );
		$is_past             = 'past' === $this->context->get( 'event_display_mode', false );
		$page                = max( 1, (int) $this->context->get( 'page', 1 ) );
		$show_now            = ! $is_past
		                       && $user_date->format( Dates::DBDATEFORMAT ) === $now->format( Dates::DBDATEFORMAT );
		$has_next_page       = ! empty( $template_vars['next_url'] );
		$date_sorted_events  = (array) Arr::get( $template_vars, 'events', [] );

		/*
		 * Events can be sorted by a number of filterable criteria: we cannot assume the first event will be the first
		 * in date order, and the same applies to the end date.
		 * Furthermore, depending on the view and the display mode, the order of the events might be ascending or
		 * descending.
		 * We want to build a date range that should ignore sticky events, different orders, and so on.
		 * We sort the events by ASC start date order, using the "display" date to cope with event timezone, site
		 * timezone and timezone settings.
		 * We use the "display" date as we need to build a display, user-facing string.
		 */
		usort( $date_sorted_events,
			static function ( $a, $b )
			{
				if ( $a->dates->start_display == $b->dates->start_display ) {
					return 0;
				}

				return ( $a->dates->start_display < $b->dates->start_display ) ? - 1 : 1;
			} );

		$start       = null;
		$end         = null;
		$first_event = reset( $date_sorted_events );
		$last_event  = end( $date_sorted_events );

		// Now should only apply to the first page, we should then use the event dates.
		$show_now = $show_now && $this->context->get( 'page' ) <= 1;

		if ( ! $is_past ) {
			$start = $first_event instanceof \WP_Post ? $first_event->dates->start_display : $user_date;
			$end   = $last_event instanceof \WP_Post ? $last_event->dates->start_display : $user_date;
		} else {
			$start = $first_event instanceof \WP_Post ? $first_event->dates->start_display : $user_date;
			$end   = $last_event instanceof \WP_Post ? $last_event->dates->start_display : $user_date;
		}

		$end_is_now = ( $is_past && 1 === $page )
		              || $now->format( 'Y-m-d' ) === $end->format( 'Y-m-d' );

		// Do the events all have the same start dates?
		$diff_dates = count(
			              array_unique(
				              array_map(
					              static function ( $event )
					              {
						              return $event->dates->start_display->format( 'Y-m-d' );
					              },
					              $date_sorted_events
				              )
			              )
		              ) > 1;

		$show_end = ( $is_past && 1 === $page )
		            || (
			            $has_next_page
			            && $diff_dates
			            && $start->format( 'Y-m-d' ) !== $end->format( 'Y-m-d' )
		            );

		$start_timestamp_w_offset = $start->getTimestamp() + $start->getOffset();
		$start_format_w_year      = $today->format( 'Y' ) !== $start->format( 'Y' );
		$start_label_format       = tribe_get_date_format( $start_format_w_year );

		$start_time_label = date_i18n( $start_label_format, $start_timestamp_w_offset );

		$now_label = $now_text;
		if ( empty( $date_sorted_events ) || ! $has_next_page ) {
			$onwards_label_start = $show_now ? $now_text : $start_time_label;
			$now_label           = sprintf(
			// translators: the placeholder is for the date range start, e.g. "Now" or "October 23".
				_x( '%s onwards', 'The datepicker range definition when no events are found.', 'the-events-calendar' ),
				$onwards_label_start
			);
			$show_now            = true;
		}

		$end_timestamp_w_offset = $end->getTimestamp() + $end->getOffset();
		$end_format_w_year      = Dates::build_date_object()->format( 'Y' ) !== $end->format( 'Y' );
		$end_label_format       = tribe_get_date_format( $end_format_w_year );
		$end_date_label         = $end_is_now ? $now_text : date_i18n( $end_label_format, $end_timestamp_w_offset );

		$template_vars['show_now']                   = $show_now;
		$template_vars['now_label']                  = $now_label;
		$template_vars['show_end']                   = $show_end;
		$template_vars['selected_start_datetime']    = date_i18n( 'Y-m-d', $start_timestamp_w_offset );
		$template_vars['selected_start_date_mobile'] = $start->format( $compact_date_format );
		$template_vars['selected_start_date_label']  = $start_time_label;
		$template_vars['selected_end_datetime']      = date_i18n( 'Y-m-d', $end_timestamp_w_offset );
		$template_vars['selected_end_date_mobile']   = $end->format( $compact_date_format );
		$template_vars['selected_end_date_label']    = $end_date_label;
		$template_vars['datepicker_date']            = $start->format( $compact_date_format );

		return $template_vars;
	}

	/**
	 * Remove the query arguments and values related to the View `past` display mode, if supported.
	 *
	 * The method will alter the View context to remove notions of a "past" view request from it.
	 *
	 * @since 4.9.11
	 */
	protected function remove_past_query_args() {
		$display_mode = $this->context->get( 'event_display_mode', false );
		$is_past      = 'past' === $display_mode;

		if ( ! $is_past ) {
			return;
		}

		$url       = $this->context->get( 'url', home_url() );
		$view_data = $this->context->get( 'view_data', [] );

		$upcoming_url      = remove_query_arg( 'eventDisplay', $url );
		$updated_view_data = array_merge( $view_data, [ 'url' => $upcoming_url ] );

		$this->context = $this->context->alter(
			[
				'event_display_mode' => $this->context->get( 'event_display', false ),
				'url'                => $upcoming_url,
				'view_data'          => $updated_view_data,
			]
		);
	}
}
