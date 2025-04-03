<?php
/**
 * The Redirections class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller;
use TEC\Events\QR\Routes;
use TEC\Events\QR\Settings;
use Tribe__Events__Main as TEC;

/**
 * Class Redirections.
 *
 * @since TBD
 *
 * @package TEC\Events\QR
 */
class Redirections extends Controller {
	/**
	 * Register the controller.
	 *
	 * @since TBD
	 * @return void
	 */
	public function do_register(): void {
		add_action( 'template_redirect', [ $this, 'handle_qr_redirect' ] );
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'template_redirect', [ $this, 'handle_qr_redirect' ] );
	}

	/**
	 * Get the fallback URL for redirections.
	 *
	 * @since TBD
	 * @return string The fallback URL.
	 */
	public function get_fallback_url(): string {
		return (string) ( tribe_get_option( Settings::get_option_slugs()['fallback'] ) ?: home_url() );
	}

	/**
	 * Get the URL for the current event or next upcoming event.
	 *
	 * @since TBD
	 * @return string The URL to redirect to, either an event permalink or fallback URL.
	 */
	public function get_current_event_url(): string {
		$args   = [
			'posts_per_page' => 1,
			'post_type'      => TEC::POSTTYPE,
			'post_status'    => 'publish',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'     => [
				[
					'key'     => '_EventEndDate',
					'value'   => current_time( 'mysql' ),
					'compare' => '>',
					'type'    => 'DATETIME',
				],
			],
			'orderby'        => 'meta_value',
			'meta_key'       => '_EventStartDate',
			'order'          => 'ASC',
		];
		$events = tribe_get_events( $args );

		$url = empty( $events ) ? $this->get_fallback_url() : get_permalink( $events[0]->ID );

		/**
		 * Filters the URL for the current event redirection.
		 *
		 * @since TBD
		 *
		 * @param string $url     The URL to redirect to.
		 * @param array  $events  The events found by the query.
		 * @param self   $context The Redirections instance.
		 */
		return apply_filters( 'tec_events_qr_current_event_url', $url, $events, $this );
	}

	/**
	 * Get the URL for the next upcoming event that hasn't started yet.
	 *
	 * @since TBD
	 * @return string The URL to redirect to, either an event permalink or fallback URL.
	 */
	public function get_upcoming_event_url(): string {
		$args   = [
			'posts_per_page' => 1,
			'post_type'      => TEC::POSTTYPE,
			'post_status'    => 'publish',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'     => [
				[
					'key'     => '_EventStartDate',
					'value'   => current_time( 'mysql' ),
					'compare' => '>',
					'type'    => 'DATETIME',
				],
			],
			'orderby'        => 'meta_value',
			'meta_key'       => '_EventStartDate',
			'order'          => 'ASC',
		];
		$events = tribe_get_events( $args );

		$url = empty( $events ) ? $this->get_fallback_url() : get_permalink( $events[0]->ID );

		/**
		 * Filters the URL for the upcoming event redirection.
		 *
		 * @since TBD
		 *
		 * @param string $url     The URL to redirect to.
		 * @param array  $events  The events found by the query.
		 * @param self   $context The Redirections instance.
		 */
		return apply_filters( 'tec_events_qr_upcoming_event_url', $url, $events, $this );
	}

	/**
	 * Get the URL for a specific event.
	 *
	 * @since TBD
	 * @param int $post_id The post ID of the event.
	 * @return string The URL to redirect to, either an event permalink or fallback URL.
	 */
	public function get_specific_event_url( int $post_id ): string {
		$post_type = get_post_type( $post_id );
		if ( ! $post_type || TEC::POSTTYPE !== $post_type ) {
			return $this->get_fallback_url();
		}

		$url = get_permalink( $post_id );

		/**
		 * Filters the URL for the specific event redirection.
		 *
		 * @since TBD
		 *
		 * @param string $url     The URL to redirect to.
		 * @param int    $post_id The post ID of the event.
		 * @param self   $context The Redirections instance.
		 */
		return apply_filters( 'tec_events_qr_specific_event_url', $url, $post_id, $this );
	}

	/**
	 * Get the URL for the next event in a series.
	 *
	 * @since TBD
	 * @param int $post_id The post ID of the series.
	 * @return string The URL to redirect to, either an event permalink or fallback URL.
	 */
	public function get_next_series_event_url( int $post_id ): string {
		// If we don't have the Pro version, return the fallback URL.
		if ( ! has_action( 'tribe_common_loaded', 'tribe_register_pro' ) ) {
			return $this->get_fallback_url();
		}

		// Get the next event in the series using the Series_Relationship class.
		$next_event = \TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship::next( $post_id );

		$url = $next_event ? get_permalink( $next_event->post_id ) : $this->get_fallback_url();

		/**
		 * Filters the URL for the next event in a series redirection.
		 *
		 * @since TBD
		 *
		 * @param string $url     The URL to redirect to.
		 * @param array  $events  The events found by the query.
		 * @param int    $post_id The post ID of the series.
		 * @param self   $context The Redirections instance.
		 */
		return apply_filters( 'tec_events_qr_next_series_event_url', $url, [ $next_event ], $post_id, $this );
	}

	/**
	 * Handle QR code redirections.
	 *
	 * @since TBD
	 * @return void
	 */
	public function handle_qr_redirect(): void {
		$hash = get_query_var( 'tec_qr_hash' );

		if ( ! $hash ) {
			return;
		}

		$routes = tribe( Routes::class );

		try {
			$data = $routes->decode_qr_hash( $hash );
		} catch ( \InvalidArgumentException $e ) {
			// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			wp_redirect( esc_url( $this->get_fallback_url() ) );
			tribe_exit();
		}

		switch ( $data['qr_type'] ) {
			// phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
			case 'current':
				// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect( esc_url( $this->get_current_event_url() ) );
				tribe_exit();
			// phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
			case 'upcoming':
				// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect( esc_url( $this->get_upcoming_event_url() ) );
				tribe_exit();
			// phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
			case 'specific':
				// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect( esc_url( $this->get_specific_event_url( $data['post_id'] ) ) );
				tribe_exit();
			// phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
			case 'next':
				// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect( esc_url( $this->get_next_series_event_url( $data['post_id'] ) ) );
				tribe_exit();
			// phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
			default:
			// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect( esc_url( $this->get_fallback_url() ) );
				tribe_exit();
		}
	}
}
