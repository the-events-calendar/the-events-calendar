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
	 */
	public function do_register(): void {
		$this->container->singleton( static::class, $this );
		add_action( 'send_headers', [ $this, 'filter_headers' ] );
	}

	/**
	 * Unregister actions.
	 *
	 * @since 6.10.2
	 */
	public function unregister(): void {
		remove_action( 'send_headers', [ $this, 'filter_headers' ] );
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
			|| ! isset( $wp_query->query['eventDate'] )
		) {
			return;
		}

		$enabled_views = tribe_get_option( 'tribeEnableViews' );
		$event_display = $wp_query->query['eventDisplay'];

		if ( 'day' === $event_display ) {
			$this->check_day_view( $wp_query, $enabled_views );
		} elseif ( 'month' === $event_display ) {
			$this->check_month_view( $wp_query, $enabled_views );
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
			$wp_query->set_404();

			return;
		}

		$data = $this->prepare_date_check( $wp_query, 'Y-m-d' );

		// If either date is false/empty and the event's month matches the current month, skip further checks.
		if ( ( ! $data['earliest_date_str'] || ! $data['latest_date_str'] ) && ( $data['event_month'] === $data['current_month'] ) ) {
			return;
		}

		if ( strtotime( $data['earliest_date_str'] ) > $data['event_timestamp'] ) {
			$wp_query->set_404();

			return;
		}

		if ( strtotime( $data['latest_date_str'] ) < $data['event_timestamp'] ) {
			$wp_query->set_404();

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
			$wp_query->set_404();

			return;
		}

		$data = $this->prepare_date_check( $wp_query, 'Y-m' );
		// For month view, eventDate is expected to be in "Y-m" format.

		// If either date is false/empty and eventDate equals the current month, skip further checks.
		if ( ( ! $data['earliest_date_str'] || ! $data['latest_date_str'] ) && ( $data['event_date_str'] === $data['current_month'] ) ) {
			return;
		}

		if ( $data['earliest_date_str'] > $data['event_date_str'] ) {
			$wp_query->set_404();

			return;
		}

		if ( $data['latest_date_str'] < $data['event_date_str'] ) {
			$wp_query->set_404();

			return;
		}
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
