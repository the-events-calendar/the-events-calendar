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
	 * The QR codes upload directory path.
	 *
	 * @since TBD
	 * @var string
	 */
	private $qr_dir;

	/**
	 * The QR codes upload directory URL.
	 *
	 * @since TBD
	 * @var string
	 */
	private $qr_url;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 */
	public function __construct(QR $qr, Routes $routes) {
		$this->qr_code = $qr;
		$this->routes  = $routes;

		$upload_dir   = wp_upload_dir();
		$this->qr_dir = $upload_dir['basedir'] . '/tec-qr-codes/';
		$this->qr_url = $upload_dir['baseurl'] . '/tec-qr-codes/';
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

		$label = $this->qr_code_exists( $post->ID )
			? esc_html__( 'View QR Code', 'the-events-calendar' )
			: esc_html__( 'Generate QR Code', 'the-events-calendar' );

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
			$label
		);

		return $actions;
	}

	/**
	 * Checks if QR code images exist for a given post ID.
	 *
	 * @since TBD
	 * @param int $post_id The post ID to check.
	 * @return bool Whether QR code images exist.
	 */
	private function qr_code_exists( int $post_id ): bool {
		$file_name = 'qr_' . $post_id . '_140';
		$file_path = $this->qr_dir . $file_name . '.png';
		return file_exists( $file_path );
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

		$qr_images = [];
		for ( $i = 4; $i <= 28; $i += 4 ) {
			$uploaded        = $this->generate_qr_image( $post->ID, $qr_url, $i );
			$qr_images[ $i ] = $uploaded['error'] ? '' : $uploaded['url'];
		}

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
			'title'       => get_the_title( $post ),
			'placeholder' => $this->qr_code->level( 1 )->size( 6 )->margin( 1 )->get_png_as_base64( $qr_url ),
			'qr_images'   => $qr_images,
			'qr_url'      => $qr_url,
			'atts'        => ( new Element_Attributes( $attributes ) )->get_attributes(),
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

	/**
	 * Generates the QR image for a given link and stores it in /wp-content/uploads.
	 * Returns the uploaded image information.
	 *
	 * @param int    $post_id The event/series ID.
	 * @param string $link The QR link.
	 * @param int    $size The size of the QR image.
	 *
	 * @return ?array {
	 *     @type string $file The path to the QR code image file.
	 *     @type string $url The URL to the QR code image file.
	 *     @type string $type The MIME type of the QR code image file.
	 *     @type string $error The error message if the QR code image file could not be generated.
	 * }
	 */
	public function generate_qr_image( int $post_id, string $link, int $size = 6 ): ?array {
		if ( empty( $link ) ) {
			return null;
		}

		$file_name = 'qr_' . $post_id . '_' . (int) $size * 35;
		$file_path = $this->qr_dir . $file_name . '.png';
		$file_url  = $this->qr_url . $file_name . '.png';

		if ( file_exists( $file_path ) ) {
			return [
				'file'  => $file_path,
				'url'   => $file_url,
				'type'  => 'image/png',
				'error' => '',
			];
		}

		return $this->qr_code->level( 1 )->size( $size )->margin( 1 )->get_png_as_file( $link, $file_name );
	}
}
