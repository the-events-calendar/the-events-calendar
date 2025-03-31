<?php
/**
 * The QR Code class for handling QR code generation and display.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\QR\QR;
use TEC\Events\QR\Routes;
use Tribe\Utils\Element_Attributes;
use Tribe__Events__Main as TEC;

/**
 * Class QR_Code
 *
 * @since TBD
 *
 * @package TEC\Events\QR
 */
class QR_Code {
	/**
	 * The QR code instance.
	 *
	 * @since TBD
	 * @var QR
	 */
	private $qr_code;

	/**
	 * The routes instance.
	 *
	 * @since TBD
	 * @var Routes
	 */
	private $routes;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 */
	public function __construct() {
		$this->qr_code = tribe( QR::class );
		$this->routes  = tribe( Routes::class );
	}

	/**
	 * Adds the QR code to the admin table.
	 *
	 * @since TBD
	 * @param array  $actions An array of actions.
	 * @param object $post The post object.
	 * @return array
	 */
	public function add_admin_table_action( $actions, $post ) {
		if ( $post->post_type !== 'tribe_events' ) {
			return $actions;
		}

		$url = add_query_arg(
			[
				'action'   => 'tec_qr_code_modal',
				'post_id'  => $post->ID,
				'width'    => '542',
				'height'   => '308',
				'_wpnonce' => wp_create_nonce( 'tec_qr_code_modal' ),
			],
			admin_url( 'admin-ajax.php' )
		);

		$actions['custom_action'] = sprintf(
			'<a href="%s" class="thickbox" title="%s">%s</a>',
			esc_url( $url ),
			esc_attr__( 'QR Code', 'the-events-calendar' ),
			esc_html__( 'Generate QR Code', 'the-events-calendar' )
		);

		return $actions;
	}

	/**
	 * Renders the QR code modal.
	 *
	 * @since TBD
	 * @return void
	 */
	public function render_modal(): void {
		$post = get_post( tec_get_request_var( 'post_id' ) );
		if ( ! $post || ! tribe_is_event( $post ) ) {
			wp_die( esc_html__( 'Invalid event.', 'the-events-calendar' ) );
		}

		if ( is_wp_error( $this->qr_code ) ) {
			wp_die( esc_html__( 'Error generating QR code.', 'the-events-calendar' ) );
		}

		$qr_url = $this->routes->get_qr_url( $post->ID, 'specific' );
		$qr_img = $this->qr_code->size( 6 )->margin( 1 )->get_png_as_base64( $qr_url );

		$attributes = [
			'alt'      => sprintf(
				/* translators: %s: The event title or type of QR code */
				esc_attr__( 'QR Code for %s', 'the-events-calendar' ),
				get_the_title( $post )
			),
			'class'    => 'tec-events-qr-code__image',
			'data-url' => esc_url( $qr_url ),
		];

		$template_vars = [
			'title'      => get_the_title( $post ),
			'qr_img'     => $qr_img,
			'qr_url'     => $qr_url,
			'atts'       => ( new Element_Attributes( $attributes ) )->get_attributes(),
			'stylesheet' => TEC::instance()->plugin_url . 'src/resources/css/qr-code.min.css',
		];

		/**
		 * Filters the template variables for the QR code modal.
		 *
		 * @since TBD
		 *
		 * @param array $template_vars The template variables.
		 * @param WP_Post $post The post object.
		 */
		$template_vars = apply_filters( 'tec_events_qr_code_modal_vars', $template_vars, $post );

		$template = new \Tribe__Template();
		$template->set_template_origin( TEC::instance() );
		$template->set_template_folder( 'src/admin-views' );
		$template->set_template_folder_lookup( true );
		$template->set_template_context_extract( true );

		$template->template( 'qr-code-modal', $template_vars );
		wp_die();
	}
}
