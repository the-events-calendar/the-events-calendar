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
	 * Get HTML method
	 *
	 * @since 4.9.3
	 *
	 */
	public function get_html() {
		$args = $this->setup_repository_args();

		$this->setup_the_loop( $args );

		$template_vars = $this->setup_template_vars();

		$this->template->set_values( $template_vars, false );

		$html = $this->template->render();

		$this->restore_the_loop();

		return $html;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prev_url( $canonical = false, array $passthru_vars = [] ) {
		$current_page = (int) $this->context->get( 'page', 1 );
		$display      = $this->context->get( 'event_display_mode', 'list' );

		if ( 'past' === $display ) {
			$url = parent::next_url( $canonical, [ 'eventDisplay' => 'past' ] );
		} else if ( $current_page > 1 ) {
			$url = parent::prev_url( $canonical );
		} else {
			$url = $this->get_past_url( $canonical );
		}

		$url = $this->filter_prev_url( $canonical, $url );

		return $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function next_url( $canonical = false, array $passthru_vars = [] ) {
		$current_page = (int) $this->context->get( 'page', 1 );
		$display      = $this->context->get( 'event_display_mode', 'list' );

		if ( $this->slug === $display ) {
			$url = parent::next_url( $canonical );
		} else if ( $current_page > 1 ) {
			$url = parent::prev_url( $canonical, [ 'eventDisplay' => 'past' ] );
		} else {
			$url = $this->get_upcoming_url( $canonical );
		}

		$url = $this->filter_next_url( $canonical, $url );

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
		$default_date = 'now';
		$date         = $this->context->get( 'event_date', $default_date );
		$eventDate_var = $default_date === $date ? '' : $date;

		$past = tribe_events()->by_args( $this->setup_repository_args( $this->context->alter( [
			'eventDisplay' => 'past',
			'paged'        => $page,
		] ) ) );

		if ( $past->count() > 0 ) {
			$url = clone $this->url->add_query_args( array_filter( [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'past',
				'eventDate'    => $eventDate_var,
				$this->page_key        => $page,
				'tribe-bar-search' => $this->context->get('keyword'),
			] ) );

			$past_url = (string) $url;

			if ( ! $canonical ) {
				return $past_url;
			}

			// We've got rewrite rules handling `eventDate` and `eventDisplay`, but not List. Let's remove it.
			$canonical_url = Rewrite::instance()->get_clean_url(
				add_query_arg(
					[ 'eventDisplay' => $this->slug ],
					remove_query_arg( [
						'eventDate',
					], $past_url )
				)
			);

			// We use the `eventDisplay` query var as a display mode indicator: we have to make sure it's there.
			$url = add_query_arg( [ 'eventDisplay' => 'past' ], $canonical_url );

			// Let's re-add the `eventDate` if we had one.
			if ( ! empty( $eventDate_var ) ) {
				$url = add_query_arg( [ 'eventDate' => $eventDate_var ], $canonical_url );
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
	protected function get_upcoming_url($canonical = false, $page = 1) {
		$default_date = 'now';
		$date         = $this->context->get( 'event_date', $default_date );

		$upcoming = tribe_events()->by_args( $this->setup_repository_args( $this->context->alter( [
			'eventDisplay' => 'list',
			'paged'        => $page,
		] ) ) );

		if ( $upcoming->count() > 0 ) {
			$url = clone $this->url->add_query_args( array_filter( [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'list',
				'eventDate'    => $default_date === $date ? '' : $date,
				$this->page_key        => $page,
				'tribe-bar-search' => $this->context->get('keyword'),
			] ) );

			if ( ! $canonical ) {
				return (string) $url;
			}

			return tribe( 'events.rewrite' )->get_clean_url( (string) $url );
		}

		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( \Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$common_args = parent::setup_repository_args( $context );

		/*
		 * The View not care where the context comes from: from the View point of view the context is the only
		 * source of truth.
		 * The context might come from the main query, from a widget, a shortcode or a REST request.
		 */
		$context_arr = $context->to_array();

		/*
		 * Depending on the context contents let's set up the arguments to fetch the events.
		 */
		$args = array_merge( $common_args, [
			'posts_per_page' => $context_arr['posts_per_page'],
			'paged'          => max( Arr::get_first_set( $context_arr, [ 'paged', 'page' ], 1 ), 1 ),
		] );

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

	/**
	 * Sets up the List View template variables.
	 *
	 * @since TBD
	 *
	 * @return array An array of Template variables for the View Template.
	 */
	protected function setup_template_vars() {
		$template_vars = [
			'title'       => wp_title( null, false ),
			'events'      => $this->repository->all(),
			'url'         => $this->get_url( true ),
			'prev_url'    => $this->prev_url( true ),
			'next_url'    => $this->next_url( true ),
			'bar_keyword' => $this->context->get( 'keyword', '' ),
			'bar_date'    => $this->context->get( 'event_date', '' ),
		];

		$template_vars = $this->filter_template_vars( $template_vars );

		return $template_vars;
	}
}
