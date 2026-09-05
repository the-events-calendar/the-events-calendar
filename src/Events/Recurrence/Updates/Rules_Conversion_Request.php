<?php
/**
 * Handles the admin request converting a rule-based Event into individual dates.
 *
 * Both editors post the same small form to `admin-post.php`: the Event ID, a per-Event
 * nonce and the user's acknowledgment of what the conversion does. The request converts
 * the Event and sends the user back to its edit screen with a notice.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;

/**
 * Class Rules_Conversion_Request.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */
class Rules_Conversion_Request {
	/**
	 * The `admin-post.php` action.
	 *
	 * @since TBD
	 */
	public const ACTION = 'tec_events_recurrence_convert';

	/**
	 * The nonce action prefix; the Event post ID completes it.
	 *
	 * @since TBD
	 */
	public const NONCE_ACTION = 'tec_events_recurrence_convert_';

	/**
	 * The name of the posted Event ID field.
	 *
	 * @since TBD
	 */
	public const POST_FIELD = 'post_id';

	/**
	 * The name of the posted acknowledgment field.
	 *
	 * @since TBD
	 */
	public const ACK_FIELD = 'tec_events_recurrence_convert_ack';

	/**
	 * The DOM ID of the conversion form.
	 *
	 * @since TBD
	 */
	public const FORM_ID = 'tec-events-recurrence-convert';

	/**
	 * Registers the request handler.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! has_action( 'admin_post_' . self::ACTION, [ $this, 'handle' ] ) ) {
			add_action( 'admin_post_' . self::ACTION, [ $this, 'handle' ] );
		}
	}

	/**
	 * Removes the hooks added by the handler.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'admin_post_' . self::ACTION, [ $this, 'handle' ] );
	}

	/**
	 * Returns the URL the conversion form posts to.
	 *
	 * @since TBD
	 *
	 * @return string The `admin-post.php` URL.
	 */
	public static function get_action_url(): string {
		return admin_url( 'admin-post.php' );
	}

	/**
	 * Returns the hidden fields of the conversion form, by name.
	 *
	 * The acknowledgment field is not among them: the user ticks it.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return array<string,string> The hidden fields.
	 */
	public static function get_form_fields( int $post_id ): array {
		return [
			'action'         => self::ACTION,
			self::POST_FIELD => (string) $post_id,
			'_wpnonce'       => wp_create_nonce( self::NONCE_ACTION . $post_id ),
		];
	}

	/**
	 * Converts the posted Event and redirects to its edit screen.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function handle(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'The conversion must be requested with a POST request.', 'the-events-calendar' ), '', [ 'response' => 405 ] );
		}

		$post_id = Occurrence::normalize_id( absint( tribe_get_request_var( self::POST_FIELD, 0 ) ) );

		if ( $post_id <= 0 || TEC::POSTTYPE !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You are not allowed to convert this event.', 'the-events-calendar' ), '', [ 'response' => 403 ] );
		}

		$notice = tribe( Admin_Notice::class );

		if ( ! wp_verify_nonce( (string) tribe_get_request_var( '_wpnonce', '' ), self::NONCE_ACTION . $post_id ) ) {
			// The editor tab may have been open long enough for the nonce to age: no hard stop.
			$notice->set( 'error', esc_html__( 'The event could not be converted: the security check failed. Reload the page and try again.', 'the-events-calendar' ) );
			$this->redirect( $post_id );

			return;
		}

		if ( ! tribe_is_truthy( tribe_get_request_var( self::ACK_FIELD, '' ) ) ) {
			$notice->set( 'error', esc_html__( 'Confirm that you understand the recurrence rules will be removed before converting the event.', 'the-events-calendar' ) );
			$this->redirect( $post_id );

			return;
		}

		$result = tribe( Rules_Conversion::class )->convert( $post_id );

		if ( is_wp_error( $result ) ) {
			$notice->set( 'tec_events_recurrence_not_rule_based' === $result->get_error_code() ? 'info' : 'error', esc_html( $result->get_error_message() ) );
			$this->redirect( $post_id );

			return;
		}

		$message = sprintf(
			/* translators: %d: the number of dates the converted event has. */
			esc_html( _n( 'This event now uses individual dates: its %d scheduled date was kept and can be edited below. The Events Calendar Pro recurrence rules were removed.', 'This event now uses individual dates: its %d scheduled dates were kept and can be edited one by one below. The Events Calendar Pro recurrence rules were removed.', $result['count'], 'the-events-calendar' ) ),
			$result['count']
		);

		if ( $result['detached'] > 0 ) {
			$message .= ' ' . esc_html__( 'It was also removed from its Series.', 'the-events-calendar' );
		}

		if ( $result['start_moved'] ) {
			$message .= ' ' . esc_html__( 'Its start date now follows its earliest scheduled date.', 'the-events-calendar' );
		}

		$notice->set( 'success', $message );
		$this->redirect( $post_id );
	}

	/**
	 * Redirects to the edit screen of the real Event and ends the request.
	 *
	 * The posted ID may have been a provisional Occurrence one and the referer anything:
	 * the destination is always the Event.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return void
	 */
	private function redirect( int $post_id ): void {
		// Built directly: link filters would rewrite the Event link to an Occurrence one.
		wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
		tribe_exit();
	}
}
