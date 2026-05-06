<?php
/**
 * Manages the legacy view removal and messaging.
 *
 * @since 6.10.2
 *
 * @package TEC\Events\SEO
 */

namespace TEC\Events\SEO\Headers;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\SEO\Settings;
use Tribe__Events__Main as TEC;

/**
 * Class Controller
 *
 * @since 6.10.2
 *
 * @package TEC\Events\SEO\Headers
 */
class Controller extends Controller_Contract {

	/**
	 * Register actions.
	 *
	 * @since 6.10.2
	 * @since 6.15.20
	 */
	public function do_register(): void {
		$this->container->singleton( static::class, $this );
		add_action( 'send_headers', [ $this, 'filter_headers' ] );
		add_filter( 'pre_handle_404', [ $this, 'prevent_list_view_paged_404' ], 10, 2 );
	}

	/**
	 * Unregister actions.
	 *
	 * @since 6.10.2
	 * @since 6.15.20
	 */
	public function unregister(): void {
		remove_action( 'send_headers', [ $this, 'filter_headers' ] );
		remove_filter( 'pre_handle_404', [ $this, 'prevent_list_view_paged_404' ], 10 );
	}

	/**
	 * Prevent WordPress from issuing a 404 for paginated TEC list view requests.
	 *
	 * On MariaDB, the Custom Tables query can return 0 rows for paged list view
	 * requests due to GROUP BY occurrence_id + LIMIT offset optimiser differences
	 * versus MySQL. WordPress's handle_404() unconditionally sets 404 when
	 * is_paged() = true and $wp_query->posts is empty, so this filter intercepts
	 * before that logic runs and lets TEC render the view with its own repository.
	 *
	 * @since 6.15.20
	 *
	 * @param bool      $preempt  Whether to short-circuit handle_404().
	 * @param \WP_Query $wp_query The main query object.
	 *
	 * @return bool True to prevent the 404, otherwise the original value.
	 */
	public function prevent_list_view_paged_404( bool $preempt, \WP_Query $wp_query ): bool {
		if ( $preempt ) {
			return $preempt;
		}

		if (
			! $wp_query->is_main_query()
			|| ! isset( $wp_query->query['post_type'] )
			|| $wp_query->query['post_type'] !== TEC::POSTTYPE
			|| ! isset( $wp_query->query['eventDisplay'] )
			|| $wp_query->query['eventDisplay'] !== 'list'
			|| empty( $wp_query->query['paged'] )
			|| (int) $wp_query->query['paged'] <= 1
		) {
			return $preempt;
		}

		$enabled_views = tribe_get_option( 'tribeEnableViews', [] );
		if ( ! in_array( 'list', $enabled_views, true ) ) {
			return $preempt;
		}

		return true;
	}

	/**
	 * Filter the headers based on the query.
	 *
	 * @since 6.10.2
	 */
	public function filter_headers() {
		global $wp_query;

		if (
			! isset( $wp_query->query['post_type'] )
			|| $wp_query->query['post_type'] !== TEC::POSTTYPE
			|| ! isset( $wp_query->query['eventDisplay'] )
		) {
			return;
		}

		$enabled_views = tribe_get_option( 'tribeEnableViews', [] );
		$event_display = $wp_query->query['eventDisplay'];

		// 'past' is a List view display-mode modifier, not a view slug — normalize it so
		// the enabled-view guard and per-view dispatch both treat it as 'list'.
		$effective_display = 'past' === $event_display ? 'list' : $event_display;

		// 404 for any view that is currently disabled, regardless of how the URL was built.
		// Site owners can disable this guard via Settings > Display > SEO & URL Handling.
		if ( ! in_array( $effective_display, $enabled_views, true ) ) {
			if ( tribe_get_option( Settings::OPT_DISABLED_VIEW_404, true ) ) {
				$this->set_404( $wp_query );
			}
			// Either way, skip per-view date checks for a disabled view.
			return;
		}

		// Day/Month views carry the date as a WP query var (set by URL rewrite rules).
		// List view keeps the date as a raw GET parameter (?tribe-bar-date) instead.
		$has_pretty_date = isset( $wp_query->query['eventDate'] );
		$has_list_date   = 'list' === $effective_display && ! empty( tribe_get_request_var( 'tribe-bar-date' ) );

		if ( ! $has_pretty_date && ! $has_list_date ) {
			return;
		}

		if ( 'day' === $effective_display ) {
			$this->check_day_view( $wp_query, $enabled_views );
		} elseif ( 'month' === $effective_display ) {
			$this->check_month_view( $wp_query, $enabled_views );
		} elseif ( 'list' === $effective_display ) {
			$this->check_list_view( $wp_query, $enabled_views );
		}
	}

	/**
	 * Prepare common date variables for view checks.
	 *
	 * This method collects variables needed for both day and month view checks.
	 * For day view, it expects $date_format = 'Y-m-d' (and computes the event's month).
	 * For month view, it expects $date_format = 'Y-m' (where eventDate is already in "Y-m" format).
	 *
	 * @param object $wp_query    The global WP_Query object.
	 * @param string $date_format Format to use for tribe_events_* functions.
	 *                            Use 'Y-m-d' for day view and 'Y-m' for month view.
	 *
	 * @return array An array with the following keys:
	 *               - event_date_str: The raw event date from the query.
	 *               - event_timestamp: (For day view) The event date as a timestamp.
	 *               - event_month: The event's month in "Y-m" format.
	 *               - current_month: The current month in "Y-m" format.
	 *               - earliest_date_str: The earliest event date (in the specified format).
	 *               - latest_date_str: The latest event date (in the specified format).
	 */
	private function prepare_date_check( object $wp_query, string $date_format ): array {
		$event_date_str = $wp_query->query['eventDate'];
		$current_month  = static::get_current_month();

		if ( 'Y-m-d' === $date_format ) {
			$event_timestamp = strtotime( $event_date_str );
			$event_month     = gmdate( 'Y-m', $event_timestamp );
		} else {
			$event_timestamp = null;
			$event_month     = $event_date_str;
		}

		$earliest_date_str = tribe_events_earliest_date( $date_format );
		$latest_date_str   = tribe_events_latest_date( $date_format );

		return [
			'event_date_str'    => $event_date_str,
			'event_timestamp'   => $event_timestamp,
			'current_month'     => $current_month,
			'event_month'       => $event_month,
			'earliest_date_str' => $earliest_date_str,
			'latest_date_str'   => $latest_date_str,
		];
	}

	/**
	 * Check the conditions for the day view.
	 *
	 * If either tribe_events_earliest_date() or tribe_events_latest_date() returns false/empty
	 * and the event's month is the current month, do not set a 404.
	 *
	 * @param object $wp_query      The global WP_Query object.
	 * @param array  $enabled_views An array of the enabled views.
	 */
	private function check_day_view( object $wp_query, array $enabled_views ) {
		if ( ! in_array( 'day', $enabled_views, true ) ) {
			$this->set_404( $wp_query );

			return;
		}

		$data = $this->prepare_date_check( $wp_query, 'Y-m-d' );

		// If either date is false/empty and the event's month matches the current month, skip further checks.
		if ( ( ! $data['earliest_date_str'] || ! $data['latest_date_str'] ) && ( $data['event_month'] === $data['current_month'] ) ) {
			return;
		}

		if ( strtotime( $data['earliest_date_str'] ) > $data['event_timestamp'] ) {
			$this->set_404( $wp_query );

			return;
		}

		if ( strtotime( $data['latest_date_str'] ) < $data['event_timestamp'] ) {
			$this->set_404( $wp_query );

			return;
		}
	}

	/**
	 * Check the conditions for the month view.
	 *
	 * If either tribe_events_earliest_date() or tribe_events_latest_date() returns false/empty
	 * and the eventDate equals the current month, do not set a 404.
	 *
	 * @param object $wp_query      The global WP_Query object.
	 * @param array  $enabled_views An array of the enabled views.
	 */
	private function check_month_view( object $wp_query, array $enabled_views ) {
		if ( ! in_array( 'month', $enabled_views, true ) ) {
			$this->set_404( $wp_query );

			return;
		}

		$data = $this->prepare_date_check( $wp_query, 'Y-m' );
		// For month view, eventDate is expected to be in "Y-m" format.

		// If either date is false/empty and eventDate equals the current month, skip further checks.
		if ( ( ! $data['earliest_date_str'] || ! $data['latest_date_str'] ) && ( $data['event_date_str'] === $data['current_month'] ) ) {
			return;
		}

		if ( $data['earliest_date_str'] > $data['event_date_str'] ) {
			$this->set_404( $wp_query );

			return;
		}

		if ( $data['latest_date_str'] < $data['event_date_str'] ) {
			$this->set_404( $wp_query );

			return;
		}
	}

	/**
	 * Check the conditions for the list view.
	 *
	 * Returns a 404 (or noindex, depending on the tec_seo_out_of_range_behavior setting) when:
	 * - List view is disabled in TEC settings.
	 * - The ?tribe-bar-date parameter refers to a date before the earliest event on record.
	 * - The ?tribe-bar-date parameter refers to a date after the latest event on record.
	 *
	 * Mirrors the grace logic used in check_day_view(): if the site has no events yet and
	 * the requested date is in the current month, validation is skipped so the live list
	 * view is not erroneously suppressed.
	 *
	 * @since TBD
	 *
	 * @param object $wp_query      The global WP_Query object.
	 * @param array  $enabled_views An array of the enabled view slugs.
	 */
	private function check_list_view( object $wp_query, array $enabled_views ): void {
		if ( ! in_array( 'list', $enabled_views, true ) ) {
			$this->set_404( $wp_query );
			return;
		}

		// List view stores the date in ?tribe-bar-date (a raw GET param, not a WP query var).
		$tribe_bar_date = tribe_get_request_var( 'tribe-bar-date', '' );

		if ( empty( $tribe_bar_date ) ) {
			return;
		}

		$event_timestamp = strtotime( $tribe_bar_date );

		// Bail on malformed date strings.
		if ( false === $event_timestamp ) {
			return;
		}

		$event_month   = gmdate( 'Y-m', $event_timestamp );
		$current_month = static::get_current_month();

		$earliest_date_str = tribe_events_earliest_date( 'Y-m-d' );
		$latest_date_str   = tribe_events_latest_date( 'Y-m-d' );

		// Skip validation when no events exist yet but the date is in the current month.
		if ( ( ! $earliest_date_str || ! $latest_date_str ) && $event_month === $current_month ) {
			return;
		}

		if ( $earliest_date_str && strtotime( $earliest_date_str ) > $event_timestamp ) {
			$this->handle_out_of_range( $wp_query );
			return;
		}

		if ( $latest_date_str && strtotime( $latest_date_str ) < $event_timestamp ) {
			$this->handle_out_of_range( $wp_query );
		}
	}

	/**
	 * Respond to an out-of-range date request.
	 *
	 * By default (setting: 'hard_404'), the WP_Query is set to a 404 so WordPress
	 * returns an HTTP 404 Not Found response — the strongest SEO signal.
	 *
	 * When the site owner has chosen 'soft_noindex' in Settings > Display >
	 * SEO & URL Handling, the page is still served (HTTP 200) but a noindex
	 * robots directive is injected via the wp_robots filter, so search engines
	 * will not index the URL without surfacing a 404 error page to visitors.
	 *
	 * @since TBD
	 *
	 * @param object $wp_query The global WP_Query object.
	 */
	private function handle_out_of_range( object $wp_query ): void {
		$behavior = tribe_get_option( Settings::OPT_OUT_OF_RANGE_BEHAVIOR, 'hard_404' );

		if ( 'soft_noindex' === $behavior ) {
			// Add noindex at the wp_robots filter stage instead of returning a 404.
			add_filter(
				'wp_robots',
				static function ( array $robots ): array {
					$robots['noindex'] = true;
					return $robots;
				}
			);
			return;
		}

		$this->set_404( $wp_query );
	}

	/**
	 * Set the query to 404 and send the HTTP 404 status header.
	 *
	 * @param \WP_Query $wp_query The query to mark as 404.
	 */
	private function set_404( \WP_Query $wp_query ): void {
		$wp_query->set_404();
		status_header( 404 );
	}

	/**
	 * Get the current month.
	 *
	 * This method is used to determine the current month and can be overridden in tests.
	 *
	 * @return string The current month in "Y-m" format.
	 */
	protected static function get_current_month() {
		return gmdate( 'Y-m' );
	}
}
