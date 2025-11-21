<?php
/**
 * The QR Code class for handling QR code generation and display.
 *
 * @since 6.12.0
 */

namespace TEC\Events\QR;

use TEC\Common\QR\QR;
use TEC\Events\QR\Routes;
use Tribe__Events__Main as TEC;

/**
 * Class QR_Code
 *
 * @since 6.12.0
 *
 * @package TEC\Events\QR
 */
class QR_Code {
	/**
	 * The QR code instance.
	 *
	 * @since 6.12.0
	 * @var QR
	 */
	private $qr_code;

	/**
	 * The routes instance.
	 *
	 * @since 6.12.0
	 * @var Routes
	 */
	private $routes;

	/**
	 * The QR codes upload directory path.
	 *
	 * @since 6.12.0
	 * @var string
	 */
	private $qr_dir;

	/**
	 * The QR codes upload directory URL.
	 *
	 * @since 6.12.0
	 * @var string
	 */
	private $qr_url;

	/**
	 * Constructor.
	 *
	 * @since 6.12.0
	 *
	 * @param QR     $qr      The QR code instance.
	 * @param Routes $routes  The routes instance.
	 */
	public function __construct( QR $qr, Routes $routes ) {
		$this->qr_code = $qr;
		$this->routes  = $routes;

		$upload_dir   = wp_upload_dir();
		$this->qr_dir = $upload_dir['basedir'] . '/tec-qr-codes/';
		$this->qr_url = $upload_dir['baseurl'] . '/tec-qr-codes/';
	}

	/**
	 * Adds the QR code to the admin table.
	 *
	 * @since 6.12.0
	 * @param array  $actions An array of actions.
	 * @param object $post The post object.
	 * @return array
	 */
	public function add_admin_table_action( $actions, $post ) {

		$supported = [ TEC::POSTTYPE ];

		/**
		 * Filter the post types that support QR codes.
		 *
		 * @since 6.12.0
		 *
		 * @param array $supported Array of supported post types.
		 */
		$supported = apply_filters( 'tec_events_qr_code_supported_post_types', $supported );

		if ( ! in_array( $post->post_type, $supported ) ) {
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

		$actions['tec_qr_code_modal'] = sprintf(
			'<a href="%s" class="thickbox" title="%s">%s</a>',
			esc_url( $url ),
			esc_attr__( 'QR Code', 'the-events-calendar' ),
			$label
		);

		return $actions;
	}

	/**
	 * Adds the QR code meta box.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	public function add_qr_code_meta_box(): void {
		$screen = get_current_screen();
		if ( $screen && 'add' === $screen->action ) {
			return;
		}

		add_meta_box(
			'tec-events-qr-code',
			esc_html__( 'QR Code', 'the-events-calendar' ),
			[ $this, 'render_qr_code_meta_box' ],
			TEC::POSTTYPE,
			'side',
			'default'
		);
	}

	/**
	 * Renders the QR code meta box.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	public function render_qr_code_meta_box(): void {
		$label = $this->qr_code_exists( get_the_ID() ) ? esc_html__( 'View QR Code', 'the-events-calendar' ) : esc_html__( 'Generate QR Code', 'the-events-calendar' );

		$url = add_query_arg(
			[
				'action'   => 'tec_qr_code_modal',
				'post_id'  => get_the_ID(),
				'width'    => '572',
				'height'   => '350',
				'_wpnonce' => wp_create_nonce( 'tec_qr_code_modal' ),
			],
			admin_url( 'admin-ajax.php' )
		);

		$template_vars = [
			'url'   => $url,
			'label' => $label,
		];

		$template = new \Tribe__Template();
		$template->set_template_origin( TEC::instance() );
		$template->set_template_folder( 'src/admin-views' );
		$template->set_template_folder_lookup( true );
		$template->set_template_context_extract( true );

		$template->template( 'qr-code-metabox', $template_vars );
	}

	/**
	 * Checks if QR code images exist for a given post ID.
	 *
	 * @since 6.12.0
	 * @param int $post_id The post ID to check.
	 * @return bool Whether QR code images exist.
	 */
	private function qr_code_exists( int $post_id ): bool {
		$file_name = 'qr_' . $post_id . '_140';
		$file_path = $this->qr_dir . $file_name . '.png';
		return file_exists( $file_path );
	}

	/**
	 * Deletes a QR code image for a given post ID and size.
	 *
	 * @since 6.12.0
	 * @param int $post_id The post ID.
	 * @param int $size The size of the QR code image.
	 * @return void
	 */
	private function delete_qr_image( int $post_id, int $size ): void {
		$file_name = 'qr_' . $post_id . '_' . (int) $size * 35;
		$file_path = $this->qr_dir . $file_name . '.png';
		if ( file_exists( $file_path ) ) {
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
			unlink( $file_path );
		}
	}

	/**
	 * Renders the QR code modal.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	public function render_modal(): void {
		// Verify nonce for CSRF protection.
		check_ajax_referer( 'tec_qr_code_modal', '_wpnonce' );

		$post = get_post( tec_get_request_var( 'post_id' ) );
		if ( ! $post ) {
			wp_die( esc_html__( 'No post found.', 'the-events-calendar' ) );
		}

		// Verify user has permission to edit this post.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			wp_die( esc_html__( 'You do not have permission to access this content.', 'the-events-calendar' ), 403 );
		}

		$allowed_types = [ TEC::POSTTYPE ];

		/**
		 * Filters the post types that support QR codes.
		 *
		 * @since 6.12.0
		 *
		 * @param array $allowed_types Array of allowed post types.
		 */
		$allowed_types = apply_filters( 'tec_events_qr_code_post_types', $allowed_types );

		if ( ! in_array( $post->post_type, $allowed_types ) ) {
			wp_die( esc_html__( 'Not a supported post type.', 'the-events-calendar' ) );
		}

		if ( is_wp_error( $this->qr_code ) ) {
			wp_die( esc_html__( 'Error generating QR code.', 'the-events-calendar' ) );
		}

		/**
		 * Filters the redirection type for QR codes.
		 *
		 * @since 6.12.0
		 *
		 * @param string $redirection The redirection type ('specific' or 'next').
		 * @param WP_Post $post The post object.
		 */
		$redirection = apply_filters( 'tec_events_qr_code_redirection_type', 'specific', $post );

		$qr_url = $this->routes->get_qr_url( $post->ID, $redirection );

		$qr_images = [];
		for ( $i = 4; $i <= 28; $i += 4 ) {
			$uploaded        = $this->generate_qr_image( $post->ID, $qr_url, $i );
			$qr_images[ $i ] = $uploaded['error'] ? '' : $uploaded['url'];
		}

		$template_vars = [
			'title'       => get_the_title( $post ),
			'placeholder' => $this->qr_code->level( 1 )->size( 6 )->margin( 1 )->get_png_as_base64( $qr_url ),
			'qr_images'   => $qr_images,
			'qr_url'      => $qr_url,
			'alt'         => sprintf(
				/* translators: %s: The event title or type of QR code */
				esc_attr__( 'QR Code for %s', 'the-events-calendar' ),
				get_the_title( $post )
			),
		];

		/**
		 * Filters the template variables for the QR code modal.
		 *
		 * @since 6.12.0
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
		if ( empty( $link ) || ! tribe( Controller::class )->is_active() ) {
			return null;
		}

		$file_name = 'qr_' . $post_id . '_' . (int) $size * 35;
		$file_path = $this->qr_dir . $file_name . '.png';
		$file_url  = $this->qr_url . $file_name . '.png';

		/**
		 * Filters whether to regenerate the QR code image.
		 *
		 * @since 6.12.0
		 *
		 * @param bool $regenerate Whether to regenerate the QR code image.
		 * @param int  $post_id    The post ID.
		 * @param int  $size       The size of the QR code image.
		 */
		$regenerate = apply_filters( 'tec_events_qr_code_regenerate', false, $post_id, $size );

		if ( file_exists( $file_path ) && ! $regenerate ) {
			return [
				'file'  => $file_path,
				'url'   => $file_url,
				'type'  => 'image/png',
				'error' => '',
			];
		} elseif ( $regenerate ) {
			$this->delete_qr_image( $post_id, $size );
		}

		return $this->qr_code->level( 1 )->size( $size )->margin( 1 )->get_png_as_file( $link, $file_name );
	}
}
