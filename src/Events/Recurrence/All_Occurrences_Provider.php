<?php
/**
 * Registers the `/all/` Occurrences archive view, the `all` Event links and the dateless
 * single Event URL redirect.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Links\Links;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events\Recurrence\Views\All_View;
use Tribe\Events\Views\V2\View;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

/**
 * Class All_Occurrences_Provider.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class All_Occurrences_Provider extends Service_Provider {
	/**
	 * Registers the Occurrences archive view, links and redirect handling.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		if ( class_exists( 'Tribe__Events__Pro__Main', false ) ) {
			/*
			 * Events Calendar Pro registers its own, Series-aware, version of the All view,
			 * of the `all` link building and of the dateless single Event redirect (to the
			 * Series page). Registering the free versions too would double the link `all`
			 * fragment and race Pro's redirect: Pro owns the front end when active.
			 */
			return;
		}

		if ( ! has_filter( 'tribe_events_views', [ $this, 'add_views' ] ) ) {
			add_filter( 'tribe_events_views', [ $this, 'add_views' ] );
		}

		if ( ! has_filter( 'tribe_events_views_v2_view_all_breadcrumbs', [ $this, 'filter_all_view_breadcrumbs' ] ) ) {
			add_filter( 'tribe_events_views_v2_view_all_breadcrumbs', [ $this, 'filter_all_view_breadcrumbs' ], 10, 2 );
		}

		if ( ! has_filter( 'tribe_events_views_v2_view_all_title', [ $this, 'filter_all_view_title' ] ) ) {
			add_filter( 'tribe_events_views_v2_view_all_title', [ $this, 'filter_all_view_title' ], 10, 2 );
		}

		if ( ! has_filter( 'tribe_events_get_link', [ $this, 'filter_event_link' ] ) ) {
			add_filter( 'tribe_events_get_link', [ $this, 'filter_event_link' ], 10, 3 );
		}

		if ( ! has_filter( 'tribe_events_ugly_link', [ $this, 'filter_event_ugly_link' ] ) ) {
			add_filter( 'tribe_events_ugly_link', [ $this, 'filter_event_ugly_link' ], 10, 3 );
		}

		if ( ! has_action( 'template_redirect', [ $this, 'redirect_dateless_request' ] ) ) {
			add_action( 'template_redirect', [ $this, 'redirect_dateless_request' ] );
		}

		if ( ! has_filter( 'the_posts', [ $this, 'collapse_dateless_singular' ] ) ) {
			add_filter( 'the_posts', [ $this, 'collapse_dateless_singular' ], 10, 2 );
		}

		if ( ! has_filter( 'pre_handle_404', [ $this, 'prevent_all_view_paged_404' ] ) ) {
			add_filter( 'pre_handle_404', [ $this, 'prevent_all_view_paged_404' ], 10, 2 );
		}
	}

	/**
	 * Unregisters the hooks added by the provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'tribe_events_views', [ $this, 'add_views' ] );
		remove_filter( 'tribe_events_views_v2_view_all_breadcrumbs', [ $this, 'filter_all_view_breadcrumbs' ] );
		remove_filter( 'tribe_events_views_v2_view_all_title', [ $this, 'filter_all_view_title' ] );
		remove_filter( 'tribe_events_get_link', [ $this, 'filter_event_link' ] );
		remove_filter( 'tribe_events_ugly_link', [ $this, 'filter_event_ugly_link' ] );
		remove_action( 'template_redirect', [ $this, 'redirect_dateless_request' ] );
		remove_filter( 'the_posts', [ $this, 'collapse_dateless_singular' ] );
		remove_filter( 'pre_handle_404', [ $this, 'prevent_all_view_paged_404' ] );
	}

	/**
	 * Registers the All view among the Views v2 ones.
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $views The registered views map.
	 *
	 * @return array<string,string> The registered views, including the All one.
	 */
	public function add_views( $views ): array {
		$views                              = (array) $views;
		$views[ All_View::get_view_slug() ] = All_View::class;

		return $views;
	}

	/**
	 * Sets up the All view breadcrumbs.
	 *
	 * @since TBD
	 *
	 * @param array<array<string,string>> $breadcrumbs The current breadcrumbs.
	 * @param View                        $view        The view being rendered.
	 *
	 * @return array<array<string,string>> The filtered breadcrumbs.
	 */
	public function filter_all_view_breadcrumbs( $breadcrumbs, $view ) {
		if ( ! $view instanceof All_View ) {
			return $breadcrumbs;
		}

		return $view->setup_breadcrumbs( $breadcrumbs, $view );
	}

	/**
	 * Sets up the All view title.
	 *
	 * @since TBD
	 *
	 * @param string $title The current view title.
	 * @param View   $view  The view being rendered.
	 *
	 * @return string The filtered view title.
	 */
	public function filter_all_view_title( $title, $view ) {
		if ( ! $view instanceof All_View ) {
			return $title;
		}

		return $view->setup_title( $title );
	}

	/**
	 * Builds the `all` Event link, e.g. `/event/some-event/all/`.
	 *
	 * The Events Calendar main class has no `all` case in its link building: the URL
	 * reaching this filter for the `all` type is the plain Events archive one.
	 *
	 * @since TBD
	 *
	 * @param string          $event_url The link as built by `Tribe__Events__Main::getLink()`.
	 * @param string          $type      The link type being built.
	 * @param int|string|bool $secondary The secondary link argument; the Event post ID for the `all` type.
	 *
	 * @return string The link, replaced with the Occurrences archive one for the `all` type.
	 */
	public function filter_event_link( $event_url, $type, $secondary = false ) {
		if ( 'all' !== $type ) {
			return $event_url;
		}

		$post_id = $this->resolve_event_post_id( $secondary );

		if ( ! $post_id ) {
			return $event_url;
		}

		$permalink = (string) get_permalink( $post_id );

		if ( false !== strpos( $permalink, '?' ) ) {
			/*
			 * A query-string permalink cannot take a path fragment. Note a missing
			 * permalink structure option is `false`, not an empty string: this request
			 * would not have taken the dedicated plain-permalink link building path.
			 */
			return add_query_arg( 'eventDisplay', TEC::instance()->all_slug, $permalink );
		}

		return tribe_append_path( $permalink, TEC::instance()->all_slug );
	}

	/**
	 * Builds the `all` Event link when pretty permalinks are not available.
	 *
	 * @since TBD
	 *
	 * @param string          $event_url The link as built by `Tribe__Events__Main::uglyLink()`.
	 * @param string          $type      The link type being built.
	 * @param int|string|bool $secondary The secondary link argument; the Event post ID for the `all` type.
	 *
	 * @return string The link, replaced with the Occurrences archive one for the `all` type.
	 */
	public function filter_event_ugly_link( $event_url, $type, $secondary = false ) {
		if ( 'all' !== $type ) {
			return $event_url;
		}

		$post_id = $this->resolve_event_post_id( $secondary );

		if ( ! $post_id ) {
			return $event_url;
		}

		return add_query_arg( 'eventDisplay', TEC::instance()->all_slug, get_permalink( $post_id ) );
	}

	/**
	 * Redirects the dateless URL of an Event with multiple Occurrences to its next
	 * upcoming Occurrence date URL, or to the Occurrences archive when all dates are past.
	 *
	 * Without the redirect the request would render the single Event template once per
	 * Occurrence: the Custom Tables query legitimately returns one result per Occurrence
	 * for the Event post name.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function redirect_dateless_request(): void {
		global $wp_query;

		if ( ! $wp_query instanceof WP_Query || ! $this->is_dateless_single_request( $wp_query ) ) {
			return;
		}

		$post = $wp_query->get_queried_object();

		if ( ! $post instanceof WP_Post || TEC::POSTTYPE !== $post->post_type ) {
			return;
		}

		$post_id = Occurrence::normalize_id( (int) $post->ID );

		if ( ! $post_id || Occurrence::where( 'post_id', $post_id )->count() < 2 ) {
			return;
		}

		$url = $this->get_dateless_redirect_url( $post_id );

		/**
		 * Filters the URL an Event with multiple Occurrences redirects its dateless single
		 * URL to.
		 *
		 * Returning an empty string will prevent the redirect.
		 *
		 * @since TBD
		 *
		 * @param string $url     The redirect URL: the next upcoming Occurrence date URL, or
		 *                        the Occurrences archive URL when all Occurrences are past.
		 * @param int    $post_id The Event post ID.
		 */
		$url = (string) apply_filters( 'tec_events_recurrence_dateless_redirect_url', $url, $post_id );

		if ( '' === $url ) {
			return;
		}

		wp_safe_redirect( $url );
		tribe_exit();
	}

	/**
	 * Reduces the results of a dateless single Event query to the next upcoming Occurrence.
	 *
	 * The safety net behind the redirect: it covers the surfaces the redirect deliberately
	 * skips, feeds and embeds, where rendering the single Event template once per
	 * Occurrence would be equally wrong.
	 *
	 * @since TBD
	 *
	 * @param array<WP_Post> $posts The posts found by the query.
	 * @param WP_Query|null  $query The query being resolved.
	 *
	 * @return array<WP_Post> The posts, reduced to the next upcoming Occurrence when required.
	 */
	public function collapse_dateless_singular( $posts, $query = null ) {
		if ( ! is_array( $posts ) || count( $posts ) < 2 ) {
			return $posts;
		}

		if ( ! $query instanceof WP_Query || ! $query->is_main_query() || ! $query->is_single() ) {
			return $posts;
		}

		if ( ! $this->is_dateless_single_request( $query, false ) ) {
			return $posts;
		}

		$post_names = array_unique( array_filter( wp_list_pluck( $posts, 'post_name' ) ) );
		$post_types = array_unique( wp_list_pluck( $posts, 'post_type' ) );

		if ( 1 !== count( $post_names ) || [ TEC::POSTTYPE ] !== $post_types ) {
			return $posts;
		}

		$provisional = tribe( Provisional_Post::class );
		$upcoming    = null;
		$last        = null;

		foreach ( $posts as $post ) {
			if ( ! $provisional->is_provisional_post_id( (int) $post->ID ) ) {
				continue;
			}

			$occurrence = Occurrence::find(
				$provisional->normalize_provisional_post_id( (int) $post->ID ),
				'occurrence_id'
			);

			if ( ! $occurrence instanceof Occurrence ) {
				continue;
			}

			$is_upcoming = strtotime( (string) $occurrence->end_date_utc ) > time();

			if (
				$is_upcoming
				&& (
					null === $upcoming
					|| $occurrence->start_date < $upcoming[1]->start_date
				)
			) {
				$upcoming = [ $post, $occurrence ];
			}

			if ( null === $last || $occurrence->start_date > $last[1]->start_date ) {
				$last = [ $post, $occurrence ];
			}
		}

		$keep = $upcoming ?? $last;

		if ( null === $keep ) {
			return $posts;
		}

		return [ $keep[0] ];
	}

	/**
	 * Prevents WordPress from returning a 404 for paged Occurrences archive requests.
	 *
	 * The `/event/{slug}/all/page/2/` URL resolves to a singular query with a `page`
	 * variable: since WordPress 5.5 `WP::handle_404()` would 404 it because the Event post
	 * content has no `<!--nextpage-->` pagination.
	 *
	 * @since TBD
	 *
	 * @param bool     $preempt  Whether to short-circuit the 404 handling.
	 * @param WP_Query $wp_query The main query object.
	 *
	 * @return bool True to prevent the 404 for paged Occurrences archive requests.
	 */
	public function prevent_all_view_paged_404( $preempt, $wp_query ) {
		if ( $preempt ) {
			return $preempt;
		}

		if ( ! $wp_query instanceof WP_Query || ! $wp_query->is_main_query() ) {
			return $preempt;
		}

		if ( empty( $wp_query->query_vars['tribe_recurrence_list'] ) ) {
			return $preempt;
		}

		if ( (int) $wp_query->get( 'page' ) <= 1 ) {
			return $preempt;
		}

		return true;
	}

	/**
	 * Whether the current query is a dateless single Event request, i.e. one that should
	 * resolve to a single Occurrence but carries no Occurrence date.
	 *
	 * The check is query-variable based on purpose: the request renders in a state where
	 * the Views v2 context is not reliably flagging single Event requests.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query           The query to check.
	 * @param bool     $check_surfaces  Whether to exclude feed, embed, 404 and preview requests too;
	 *                                  the redirect excludes them, the results collapse
	 *                                  covers them.
	 *
	 * @return bool Whether the query is a dateless single Event request.
	 */
	private function is_dateless_single_request( WP_Query $query, bool $check_surfaces = true ): bool {
		if ( '' === (string) $query->get( TEC::POSTTYPE ) && '' === (string) $query->get( 'name' ) ) {
			return false;
		}

		if ( ! isset( $query->query_vars['post_type'] ) ) {
			return false;
		}

		if ( '' !== (string) $query->get( 'eventDate' ) ) {
			return false;
		}

		// The Occurrences archive URLs match the dateless conditions too: never touch them.
		if ( 'all' === $query->get( 'eventDisplay' ) || ! empty( $query->query_vars['tribe_recurrence_list'] ) ) {
			return false;
		}

		if ( $check_surfaces && ( $query->is_feed() || $query->is_embed() || $query->is_404() || $query->is_preview() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the default URL the dateless single Event URL redirects to.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return string The next upcoming Occurrence date URL, the Occurrences archive URL
	 *                when all Occurrences are past, or an empty string on failure.
	 */
	private function get_dateless_redirect_url( int $post_id ): string {
		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			return '';
		}

		$occurrence = tribe( Links::class )->get_next_occurrence( $event );

		if (
			$occurrence instanceof Occurrence
			&& strtotime( (string) $occurrence->end_date_utc ) > time()
		) {
			$provisional_id = tribe( ID_Generator::class )->current() + (int) $occurrence->occurrence_id;

			// The provisional post must be in the posts cache for `get_permalink()` to resolve it.
			tribe( Provisional_Post::class )->hydrate_caches( [ $provisional_id ] );

			$url = get_permalink( $provisional_id );

			if ( is_string( $url ) && '' !== $url ) {
				return $url;
			}
		}

		// All Occurrences are past: the archive of all dates is the best landing page.
		return (string) TEC::instance()->getLink( 'all', $post_id );
	}

	/**
	 * Resolves the real Event post ID from a link building secondary argument.
	 *
	 * @since TBD
	 *
	 * @param int|string|bool $secondary The secondary link argument, an Event post ID or
	 *                                   a provisional Occurrence post ID.
	 *
	 * @return int The real Event post ID, or `0` when it cannot be resolved.
	 */
	private function resolve_event_post_id( $secondary ): int {
		$post_id = is_numeric( $secondary ) ? (int) $secondary : (int) get_the_ID();

		if ( ! $post_id ) {
			return 0;
		}

		return (int) Occurrence::normalize_id( $post_id );
	}
}
