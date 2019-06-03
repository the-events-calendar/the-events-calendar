<?php
/**
 * The List View.
 *
 * @package Tribe\Events\Views\V2\Views
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe__Events__Rewrite as Rewrite;
use Tribe__Utils__Array as Arr;

class List_View extends View {
	/**
	 * Slug for this view
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $slug = 'list';


	/**
	 * Get HTML method
	 *
	 * @since TBD
	 *
	 */
	public function get_html() {
		$args = $this->setup_repository_args();

		$this->setup_the_loop( $args );

		/*
		 * Here we pass to the template a trimmed down version of the View render context and we set it as global to
		 * make it available to any view using the template.
		 * Ideally one that contains only the variables the template will need to render.
		 */
		$template_vars = [
			'title'    => wp_title( null, false ),
			'events'   => $this->repository->all(),
			'url'      => $this->get_url( true ),
			'prev_url' => $this->prev_url( true ),
			'next_url' => $this->next_url( true ),
		];

		$template_vars = $this->filter_template_vars( $template_vars );

		$this->template->set_values( $template_vars, false );

		$html = $this->template->render();

		$this->restore_the_loop();

		return $html;
	}

	public function prev_url( $canonical = false ) {
		$current_page = (int) $this->context->get( 'paged', 1 );
		$display = $this->context->get( 'event_display_mode', $this->context->get( 'eventDisplay' , 'list') );

		if ( 'past' === $display ) {
			$url = parent::next_url();
		} else if ( $current_page > 1 ) {
			$url = parent::prev_url();
		} else {
			$url = $this->get_past_url( $canonical );
		}

		if ( ! empty( $url ) && $canonical ) {
			$url = Rewrite::instance()->get_canonical_url( $url );
		}

		$url = $this->filter_prev_url( $canonical, $url );

		return $url;
	}

	public function next_url( $canonical = false ) {
		$current_page = (int) $this->context->get( 'paged', 1 );
		$display = $this->context->get( 'event_display_mode', $this->context->get( 'eventDisplay', 'list' ) );

		if ( 'list' === $display ) {
			$url = parent::next_url();
		} else if ( $current_page > 1 ) {
			$url = parent::prev_url();
		} else {
			$url = $this->get_upcoming_url();
		}

		if ( ! empty( $url ) && $canonical ) {
			$url = Rewrite::instance()->get_canonical_url( $url );
		}

		$url = $this->filter_next_url( $canonical, $url );

		return $url;
	}

	protected function get_past_url( $page = 1 ) {
		$default_date = 'now';
		$date         = $this->context->get( 'event_date', $default_date );

		$past = tribe_events()->by_args( $this->setup_repository_args( $this->context->alter( [
			'eventDisplay' => 'past',
			'paged'        => $page,
		] ) ) );

		if ( $past->count() > 0 ) {
			$url = clone $this->url->add_query_args( array_filter( [
				'eventDisplay' => 'past',
				'eventDate'    => $default_date === $date ? '' : $date,
				'paged'        => $page,
			] ) );

			return (string) $url;
		}

		return '';
	}

	protected function get_upcoming_url($page = 1) {
		$default_date = 'now';
		$date         = $this->context->get( 'event_date', $default_date );

		$upcoming = tribe_events()->by_args( $this->setup_repository_args( $this->context->alter( [
			'eventDisplay' => 'list',
			'paged'        => $page,
		] ) ) );

		if ( $upcoming->count() > 0 ) {
			$url = clone $this->url->add_query_args( array_filter( [
				'eventDisplay' => 'list',
				'eventDate'    => $default_date === $date ? '' : $date,
				'paged'        => $page,
			] ) );

			return (string) $url;
		}

		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( \Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		/*
		 * The View not care where the context comes from: from the View point of view the context is the only
		 * source of truth.
		 * The context might come from the main query, from a widget, a shortcode or a REST request.
		 */
		$context_arr = $context->to_array();

		/*
		 * Depending on the context contents let's set up the arguments to fetch the events.
		 */
		$args = [
			'posts_per_page' => $context_arr['posts_per_page'],
			'paged'          => max( Arr::get( $context_arr, 'paged', 1 ), 1 ),
		];

		$date = Arr::get( $context_arr, 'event_date', 'now' );
		$event_display = Arr::get( $context_arr, 'event_display_mode', Arr::get( $context_arr, 'event_display' ), 'current' );

		if ( 'past' !== $event_display ) {
			$args['ends_after'] = $date;
		} else {
			$args['ends_before'] = $date;
		}

		return $args;
	}
}
