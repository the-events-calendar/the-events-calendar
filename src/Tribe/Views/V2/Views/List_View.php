<?php
/**
 * The List View.
 *
 * @package Tribe\Events\Views\V2\Views
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe__Events__Main as TEC;
use Tribe__Events__Rewrite as Rewrite;
use Tribe__Utils__Array as Arr;

class List_View extends View {
	/**
	 * Slug for this view
	 *
	 * @since 4.9.3
	 *
	 * @var string
	 */
	protected $slug = 'list';

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

		$current_page = (int) $this->context->get( 'page', 1 );
		$display      = $this->context->get( 'event_display_mode', 'list' );

		if ( 'past' === $display ) {
			// Ensure we start fresh.
			unset( $this->next_url );
			$url = parent::next_url( $canonical, [ 'eventDisplay' => 'past' ] );
			// Avoid messing up the caching since we're using the prev URL in the next URL function.
			unset( $this->next_url );
		} else if ( $current_page > 1 ) {
			$url = parent::prev_url( $canonical );
		} else {
			$url = $this->get_past_url( $canonical );
		}

		$url = $this->filter_prev_url( $canonical, $url );

		$this->prev_url = $url;

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function next_url( $canonical = false, array $passthru_vars = [] ) {
		if ( isset( $this->next_url ) ) {
			return $this->next_url;
		}

		$current_page = (int) $this->context->get( 'page', 1 );
		$display      = $this->context->get( 'event_display_mode', 'list' );

		if ( $this->slug === $display || 'default' === $display ) {
			$url = parent::next_url( $canonical );
		} else if ( $current_page > 1 ) {
			// Ensure we start fresh.
			unset( $this->prev_url );
			$url = parent::prev_url( $canonical, [ 'eventDisplay' => 'past' ] );
			// Avoid messing up the caching since we're using the prev URL in the next URL function.
			unset( $this->prev_url );
		} else {
			$url = $this->get_upcoming_url( $canonical );
		}

		$url = $this->filter_next_url( $canonical, $url );

		$this->next_url = $url;

		return $url;
	}

	/**
	 * Return the URL to a page of past events.
	 *
	 * @since 4.9.3
	 *
	 * @param bool $canonical Whether to return the canonical version of the URL or the normal one.
	 * @param int  $page The page to return the URL for.
	 *
	 * @return string The URL to the past URL page, if available, or an empty string.
	 */
	protected function get_past_url( $canonical = false, $page = 1 ) {
		$default_date   = 'now';
		$date           = $this->context->get( 'event_date', $default_date );
		$event_date_var = $default_date === $date ? '' : $date;

		$past = tribe_events()->by_args( $this->setup_repository_args( $this->context->alter( [
			'eventDisplay' => 'past',
			'paged'        => $page,
		] ) ) );

		if ( $past->count() > 0 ) {
			$past_url_object = clone $this->url->add_query_args( array_filter( [
				'post_type'        => TEC::POSTTYPE,
				'eventDisplay'     => 'past',
				'eventDate'        => $event_date_var,
				$this->page_key    => $page,
				'tribe-bar-search' => $this->context->get( 'keyword' ),
			] ) );

			$past_url = (string) $past_url_object;

			if ( ! $canonical ) {
				return $past_url;
			}

			// We've got rewrite rules handling `eventDate` and `eventDisplay`, but not List. Let's remove it.
			$canonical_url = Rewrite::instance()->get_clean_url(
				add_query_arg(
					[ 'eventDisplay' => $this->slug ],
					remove_query_arg( [ 'eventDate' ], $past_url )
				)
			);

			// We use the `eventDisplay` query var as a display mode indicator: we have to make sure it's there.
			$url = add_query_arg( [ 'eventDisplay' => 'past' ], $canonical_url );

			// Let's re-add the `eventDate` if we had one and we're not already passing it with one of its aliases.
			if ( ! (
				empty( $event_date_var )
				|| $past_url_object->get_query_arg_alias_of( 'event_date', $this->context )
			) ) {
				$url = add_query_arg( [ 'eventDate' => $event_date_var ], $url );
			}

			return $url;
		}

		return '';
	}

	/**
	 * Return the URL to a page of upcoming events.
	 *
	 * @since 4.9.3
	 *
	 * @param bool $canonical Whether to return the canonical version of the URL or the normal one.
	 * @param int  $page The page to return the URL for.
	 *
	 * @return string The URL to the upcoming URL page, if available, or an empty string.
	 */
	protected function get_upcoming_url( $canonical = false, $page = 1 ) {
		$default_date   = 'now';
		$date           = $this->context->get( 'event_date', $default_date );
		$event_date_var = $default_date === $date ? '' : $date;
		$url = '';

		$upcoming = tribe_events()->by_args( $this->setup_repository_args( $this->context->alter( [
			'eventDisplay' => 'list',
			'paged'        => $page,
		] ) ) );

		if ( $upcoming->count() > 0 ) {
			$upcoming_url_object = clone $this->url->add_query_args( array_filter( [
				'post_type'        => TEC::POSTTYPE,
				'eventDisplay'     => 'list',
				$this->page_key    => $page,
				'eventDate'        => $event_date_var,
				'tribe-bar-search' => $this->context->get( 'keyword' ),
			] ) );

			$upcoming_url = (string) $upcoming_url_object;

			if ( ! $canonical ) {
				return $upcoming_url;
			}

			// We've got rewrite rules handling `eventDate`, but not List. Let's remove it to build the URL.
			$url = tribe( 'events.rewrite' )->get_clean_url(
				remove_query_arg( [ 'eventDate' ], $upcoming_url )
			);

			// Let's re-add the `eventDate` if we had one and we're not already passing it with one of its aliases.
			if ( ! (
				empty( $event_date_var )
				|| $upcoming_url_object->get_query_arg_alias_of( 'event_date', $this->context )
			) ) {
				$url = add_query_arg( [ 'eventDate' => $event_date_var ], $url );
			}
		}

		return $url ?: $this->get_today_url( $canonical );
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

		if ( 'past' !== $event_display ) {
			$args['ends_after'] = $date;
		} else {
			$args['order']       = 'DESC';
			$args['ends_before'] = $date;
		}

		return $args;
	}
}
