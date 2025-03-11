<?php
/**
 * The Notices class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

/**
 * Class Notices
 *
 * @since   TBD
 *
 * @package TEC\Events\QR
 */
class Notices {

	/**
	 * Registers the notices for the QR code handling.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_admin_notices(): void {
		tribe_notice(
			'tec-events-qr-dependency-notice',
			[ $this, 'get_dependency_notice_contents' ],
			[
				'type'    => 'warning',
				'dismiss' => 1,
				'wrap'    => 'p',
			],
			[ $this, 'should_display_dependency_notice' ]
		);
	}

	/**
	 * Determines if the Notice for QR code dependencies should be visible
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function should_display_dependency_notice(): bool {
		// Only attempt to check the page if the user can't use the QR codes.
		if ( tribe( Controller::class )->can_use() ) {
			return false;
		}

		$active_page = tribe_get_request_var( 'page' );

		if ( $active_page ) {
			$valid_pages = [
				'tec-events-settings',
				'tec-events-help-hub',
				'tec-troubleshooting',
			];

			if ( in_array( $active_page, $valid_pages, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the notice for the QR code dependency.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_dependency_notice_contents(): string {
		$html  = '<h2>' . esc_html__( 'QR codes for Events not available.', 'the-events-calendar' ) . '</h2>';
		$html .= esc_html__( 'In order to have QR codes for your tickets you will need to have both the `php_gd2` and `gzuncompress` PHP extensions installed on your server. Please contact your hosting provider.', 'the-events-calendar' );
		$html .= ' <a target="_blank" href="https://evnt.is/event-tickets-qr-support">' . esc_html__( 'Learn more.', 'the-events-calendar' ) . '</a>';

		return $html;
	}
}
