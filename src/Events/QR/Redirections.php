<?php
/**
 * The Redirections class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller;
use TEC\Events\QR\Routes;

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
	 *
	 * @return void
	 */
	public function do_register(): void {
		$this->add_hooks();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->remove_hooks();
	}

	/**
	 * Adds the actions required by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function add_hooks(): void {
		add_action( 'template_include', [ $this, 'handle_qr_redirect' ] );
	}

	/**
	 * Removes the actions required by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function remove_hooks(): void {
		remove_action( 'template_include', [ $this, 'handle_qr_redirect' ] );
	}

	/**
	 * Handle QR code redirection.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function handle_qr_redirect(): void {
		$hash = get_query_var( 'tec_qr_hash' );
		if ( ! $hash ) {
			return;
		}

		$routes = tribe( Routes::class );

		try {
			$data = $routes->decode_qr_url( home_url( add_query_arg( [], $GLOBALS['wp']->request ) ) );
		} catch ( \InvalidArgumentException $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		$post_id = $data['post_id'];
		$qr_type = $data['qr_type'];

		// Get the post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_die( esc_html__( 'Event not found.', 'the-events-calendar' ) );
		}

		// Handle different QR types.
		switch ( $qr_type ) {
			case 'current':
				// @TODO: Implement current event logic.
				wp_redirect( get_permalink( $post_id ) );
				exit;
			case 'upcoming':
				// @TODO: Implement upcoming event logic.
				wp_redirect( get_permalink( $post_id ) );
				exit;
			case 'specific':
				// @TODO: Implement specific event logic.
				wp_redirect( get_permalink( $post_id ) );
				exit;
			case 'next':
				// @TODO: Implement next event logic.
				wp_redirect( get_permalink( $post_id ) );
				exit;
			default:
				wp_die( esc_html__( 'Invalid QR code type.', 'the-events-calendar' ) );
		}
	}
}
