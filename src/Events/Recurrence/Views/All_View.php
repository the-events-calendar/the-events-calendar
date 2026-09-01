<?php
/**
 * Renders all the Occurrences of an Event in a list-like layout.
 *
 * The view mirrors, minus the Series coupling, the "All" view Events Calendar Pro renders
 * for the same URLs: it will only register when Events Calendar Pro is not active.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Views
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Views;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Views\V2\Utils;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;

/**
 * Class All_View.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Views
 */
class All_View extends List_View {
	/**
	 * Statically accessible slug for this view.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static $view_slug = 'all';

	/**
	 * Differently from other archives this view uses the WordPress page-in-post
	 * pagination mechanism, matching the `/event/{slug}/all/page/2/` rewrite rule.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $page_key = 'page';

	/**
	 * The Event post name the view is scoped to.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $post_name = '';

	/**
	 * The Event post ID the view is scoped to.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	protected $post_id = 0;

	/**
	 * Visibility for this view: it renders for its own URLs only and never shows in the
	 * view switcher.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected static $publicly_visible = false;

	/**
	 * Whether the View should display the events bar or not.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected $display_events_bar = false;

	/**
	 * Returns the Event post ID the view is scoped to.
	 *
	 * @since TBD
	 *
	 * @return int The Event post ID the view is scoped to, or `0` if not resolved yet.
	 */
	public function get_target_post_id(): int {
		return (int) $this->post_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		/*
		 * The view has historically been rendered with the `list` templates: allow
		 * developers to define templates for the `all` view, but fall back on the
		 * `list` ones when not found.
		 */
		if ( $this->template->get_base_template_file() === $this->template->get_template_file() ) {
			$this->template_slug = List_View::get_view_slug();
		}

		return parent::get_html();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_past_url( $canonical = false, $page = 1 ) {
		$event_display_key = Utils\View::get_past_event_display_key();
		$query_args        = [ $event_display_key => 'past' ];

		if ( $page > 1 ) {
			$query_args['paged'] = $page;
		}

		return add_query_arg( $query_args, $this->get_url( $canonical ) );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( ?Context $context = null ) {
		$args = parent::setup_repository_args( $context );

		$context = $context ?? $this->context;

		$post_name = $context->get( 'name', false );

		if ( false === $post_name || '' === $post_name ) {
			// This is weird but let's show the user events anyway.
			return $args;
		}

		$post_id = tribe_events()->where( 'name', $post_name )->fields( 'ids' )->first();

		if ( empty( $post_id ) ) {
			// This is weirder but let's show the user events anyway.
			return $args;
		}

		/*
		 * Scoping by post name is all the Custom Tables query requires to return one
		 * result per Occurrence of the Event: the Occurrences JOIN takes care of the
		 * expansion. No Series relationship is involved.
		 */
		$args['name'] = $post_name;

		$this->post_name = (string) $post_name;
		$this->post_id   = Occurrence::normalize_id( (int) $post_id );

		return $args;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_url( $canonical = false, $force = false ) {
		$query_args = [
			TEC::POSTTYPE           => $this->post_name,
			'post_type'             => TEC::POSTTYPE,
			'eventDisplay'          => static::$view_slug,
			'tribe_recurrence_list' => true,
		];

		$page = $this->url->get_current_page();

		if ( $page > 1 ) {
			$query_args[ $this->page_key ] = $page;
		}

		$url = add_query_arg( array_filter( $query_args ), home_url() );

		if ( $canonical ) {
			$url = tribe( 'events.rewrite' )->get_clean_url( $url, $force );
		}

		$event_display_key  = Utils\View::get_past_event_display_key();
		$event_display_mode = $this->context->get( 'event_display_mode', false );

		if ( 'past' === $event_display_mode ) {
			$url = add_query_arg( [ $event_display_key => $event_display_mode ], $url );
		}

		$event_date = $this->context->get( 'event_date', false );

		if ( ! empty( $event_date ) ) {
			// If there's a date set, then add it as a query argument.
			$url = add_query_arg( [ 'tribe-bar-date' => $event_date ], $url );
		}

		return $this->filter_view_url( $canonical, $url );
	}

	/**
	 * Sets up the breadcrumbs for the view: the Events archive followed by the Event title.
	 *
	 * @since TBD
	 *
	 * @param array<array<string,string>> $breadcrumbs The current breadcrumbs.
	 * @param View                        $view        The view being rendered.
	 *
	 * @return array<array<string,string>> The filtered breadcrumbs.
	 */
	public function setup_breadcrumbs( $breadcrumbs, $view ) {
		$breadcrumbs   = (array) $breadcrumbs;
		$breadcrumbs[] = [
			'link'  => tribe_get_events_link(),
			'label' => tribe_get_event_label_plural(),
		];

		$post_id = $view instanceof self ? $view->get_target_post_id() : 0;

		if ( $post_id ) {
			$breadcrumbs[] = [
				'link'  => '',
				'label' => get_the_title( $post_id ),
			];
		}

		return $breadcrumbs;
	}

	/**
	 * Sets up the title for the view: "All events for {Event title}".
	 *
	 * @since TBD
	 *
	 * @param string $title The current view title.
	 *
	 * @return string The filtered view title.
	 */
	public function setup_title( $title ) {
		if ( ! $this->post_id ) {
			return (string) $title;
		}

		return sprintf(
			/* translators: %1$s: the lowercase plural Events label, %2$s: the Event title. */
			__( 'All %1$s for %2$s', 'the-events-calendar' ),
			tribe_get_event_label_plural_lowercase(),
			get_the_title( $this->post_id )
		);
	}
}
