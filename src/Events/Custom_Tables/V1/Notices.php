<?php
/**
 * The high-level class that will capture errors and exceptions raised by the Custom
 * Tables implementation and dispatch them.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1
 */

namespace TEC\Events\Custom_Tables\V1;

/**
 * Class Notices
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1
 */
class Notices {
	/**
	 * Handles an error or exception raised at any stage of the Custom Tables implementation
	 * flow.
	 *
	 * @since 6.0.0
	 *
	 * @param \Throwable|\Exception $error A reference to the thrown Throwable (on PHP 7.0+)
	 *                                     or Exception (on PHP 5.6) that should be handled.
	 */
	public function on_error( $error ) {
		try {
			if ( defined( 'WP_CLI' ) && class_exists( '\\WP_CLI' ) ) {
				$this->wpcli_error( $error );

				return;
			}

			$this->admin_notice( $error );
		} catch ( \Exception $e ) {
			// Ok, we tried and failed.
		}
	}

	/**
	 * In wp-cli context, dispatch the current error to the CLI.
	 *
	 * @since 6.0.0
	 *
	 * @param \Throwable|\Exception $error The error to dispatch to the CLI.
	 *
	 * @throws \WP_CLI\ExitException If WP_CLI is set to capture exits and not
	 *                               exit directly.
	 */
	private function wpcli_error( $error ) {
		\WP_CLI::print_value( $error->getTraceAsString() );
		\WP_CLI::error( $error->getMessage() );
	}

	/**
	 * In admin context, show the error as an admin notice.
	 *
	 * @since 6.0.0
	 *
	 * @param \Throwable|\Exception $error The error to dispatch to the CLI.
	 */
	private function admin_notice( $error ) {
		add_action( 'admin_notices', static function () use ( $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<h4><?php esc_html_e( 'The Events Calendar: Custom Tables v1 - error', 'the-events-calendar' ); ?></h4>

				<p><?php _e( $error->getMessage(), 'the-events-calendar' ); ?></p>

				<p>
				<pre><?php esc_html_e( trim( $error->getTraceAsString() ) ); ?></pre>
				</p>
			</div>
			<?php
		} );
	}
}
