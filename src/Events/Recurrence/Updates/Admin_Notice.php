<?php
/**
 * The per-user admin notice of the Recurrence feature.
 *
 * Several flows (moving a single Occurrence, refusing a frozen date write, converting a
 * rule-based Event) leave the current user one message to read on the next admin
 * screen. The message lives in a short-lived per-user transient: the Classic Editor
 * renders it on `admin_notices`, the Block Editor pulls it into its editor config.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Updates;

/**
 * Class Admin_Notice.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */
class Admin_Notice {
	/**
	 * The transient prefix of the per-user notice; the user ID completes it.
	 *
	 * @since TBD
	 */
	public const TRANSIENT = 'tec_events_recurrence_occurrence_notice_';

	/**
	 * Registers the notice rendering.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! has_action( 'admin_notices', [ $this, 'render' ] ) ) {
			add_action( 'admin_notices', [ $this, 'render' ] );
		}
	}

	/**
	 * Removes the hooks added by the notice.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'admin_notices', [ $this, 'render' ] );
	}

	/**
	 * Stores the notice the next admin screen of the current user renders.
	 *
	 * Block Editor (REST) saves do not reload a screen: no notice is stored for them.
	 *
	 * @since TBD
	 *
	 * @param string $type    The notice type: `success`, `error`, `warning` or `info`.
	 * @param string $message The notice message; links and basic formatting are allowed.
	 *
	 * @return void
	 */
	public function set( string $type, string $message ): void {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		set_transient(
			self::TRANSIENT . get_current_user_id(),
			[
				'type'    => $type,
				'message' => $message,
			],
			MINUTE_IN_SECONDS
		);
	}

	/**
	 * Returns, and forgets, the notice stored for the current user.
	 *
	 * @since TBD
	 *
	 * @return array{type: string, message: string}|null The notice, or `null` when none is stored.
	 */
	public function pull(): ?array {
		$key    = self::TRANSIENT . get_current_user_id();
		$notice = get_transient( $key );

		if ( ! is_array( $notice ) || empty( $notice['message'] ) ) {
			return null;
		}

		delete_transient( $key );

		return [
			'type'    => in_array( $notice['type'] ?? '', [ 'success', 'error', 'warning', 'info' ], true ) ? (string) $notice['type'] : 'error',
			'message' => (string) $notice['message'],
		];
	}

	/**
	 * Renders, once, the notice stored for the current user.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function render(): void {
		$notice = $this->pull();

		if ( null === $notice ) {
			return;
		}

		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $notice['type'] ),
			wp_kses( $notice['message'], self::allowed_html() )
		);
	}

	/**
	 * Returns the HTML a notice message may carry.
	 *
	 * @since TBD
	 *
	 * @return array<string,array<string,bool>> The allowed tags and attributes.
	 */
	public static function allowed_html(): array {
		return [
			'a'      => [
				'href'   => true,
				'target' => true,
				'rel'    => true,
			],
			'strong' => [],
			'em'     => [],
			'br'     => [],
		];
	}
}
