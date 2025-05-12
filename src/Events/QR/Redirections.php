<?php
/**
 * The Redirections class for the QR module.
 *
 * @since 6.12.0
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller;
use TEC\Events\QR\Routes;
use Tribe__Events__Main as TEC;

/**
 * Class Redirections.
 *
 * @since 6.12.0
 *
 * @package TEC\Events\QR
 */
class Redirections extends Controller {
	/**
	 * Register the controller.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	public function do_register(): void {
		add_action( 'template_redirect', [ $this, 'handle_qr_redirect' ] );
	}

	/**
	 * Unregister the controller.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'template_redirect', [ $this, 'handle_qr_redirect' ] );
	}

	/**
	 * Get the fallback URL for redirections.
	 *
	 * @since 6.12.0
	 * @return string The fallback URL.
	 */
	public function get_fallback_url(): string {
		return (string) tribe_events_get_url();
	}

	/**
	 * Get the URL for the current event or next upcoming event.
	 *
	 * @since 6.12.0
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

		$url = ! empty( $events ) ? get_permalink( $events[0]->ID ) : $this->get_fallback_url();

		/**
		 * Filters the URL for the current event redirection.
		 *
		 * @since 6.12.0
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
	 * @since 6.12.0
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

		$url = ! empty( $events ) ? get_permalink( $events[0]->ID ) : $this->get_fallback_url();

		/**
		 * Filters the URL for the upcoming event redirection.
		 *
		 * @since 6.12.0
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
	 * @since 6.12.0
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
		 * @since 6.12.0
		 *
		 * @param string $url     The URL to redirect to.
		 * @param int    $post_id The post ID of the event.
		 * @param self   $context The Redirections instance.
		 */
		return apply_filters( 'tec_events_qr_specific_event_url', $url, $post_id, $this );
	}

	/**
	 * Handle QR code redirections.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	public function handle_qr_redirect(): void {
		$hash = get_query_var( 'tec_qr_hash' );

		if ( ! $hash ) {
			return;
		}

		$routes = tribe( Routes::class );

		// phpcs:disable PSR2.ControlStructures.SwitchDeclaration.TerminatingComment, WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect

		try {
			$data = $routes->decode_qr_hash( $hash );
		} catch ( \InvalidArgumentException $e ) {
			wp_redirect( esc_url( $this->get_fallback_url() ) );
			tribe_exit();
		}

		switch ( $data['qr_type'] ) {
			case 'current':
				$target = $this->get_current_event_url();
				break;
			case 'upcoming':
				$target = $this->get_upcoming_event_url();
				break;
			case 'specific':
				$target = $this->get_specific_event_url( $data['post_id'] );
				break;
			default:
				$target = $this->get_fallback_url();
		}

		/**
		 * Filters the target URL for the QR code redirection.
		 *
		 * @since 6.12.0
		 *
		 * @param string $target The target URL.
		 * @param int    $post_id The post ID of the event/series.
		 * @param self   $context The Redirections instance.
		 */
		$target = apply_filters( 'tec_events_qr_redirection_url', $target, $data, $this );

		wp_redirect( esc_url( $target ) );
		tribe_exit();

		// phpcs:enable PSR2.ControlStructures.SwitchDeclaration.TerminatingComment, WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	}
}
