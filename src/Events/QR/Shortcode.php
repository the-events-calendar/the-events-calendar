<?php
/**
 * The Shortcode class for the QR module.
 *
 * @since 6.12.0
 */

namespace TEC\Events\QR;

use Tribe\Shortcode\Shortcode_Abstract;
use TEC\Common\QR\QR;
use TEC\Events\QR\Routes;
use Tribe\Utils\Element_Attributes;

/**
 * Class Shortcode
 *
 * @since 6.12.0
 *
 * @package TEC\Events\QR
 */
class Shortcode extends Shortcode_Abstract {
	/**
	 * The shortcode tag.
	 *
	 * @since 6.12.0
	 * @var string
	 */
	protected $slug;

	/**
	 * Default arguments to be merged into final arguments of the shortcode.
	 *
	 * @since 6.12.0
	 * @var array
	 */
	protected $default_arguments = [
		'id'   => '',
		'mode' => '',
		'size' => '',
	];

	/**
	 * Array of callbacks for arguments validation.
	 *
	 * @since 6.12.0
	 * @var array
	 */
	public $validate_arguments_map = [
		'id'   => 'absint',
		'mode' => 'sanitize_title_with_dashes',
		'size' => 'absint',
	];

	/**
	 * Returns a shortcode's HTML.
	 *
	 * @since 6.12.0
	 * @return string
	 */
	public function get_html() {
		$args = $this->get_arguments();
		$id   = $args['id'] ?: 0;
		$mode = $args['mode'] ?: 'current';
		$size = $args['size'] ?: 4;

		$routes  = tribe( Routes::class );
		$qr_code = tribe( QR::class );

		if ( is_wp_error( $qr_code ) ) {
			return $qr_code;
		}

		$qr_url = $routes->get_qr_url( (int) $id, $mode );

		$qr_img = $qr_code->level( 1 )->size( $size )->margin( 1 )->get_png_as_base64( $qr_url );

		/**
		 * Filters the QR code image HTML attributes.
		 *
		 * @since 6.12.0
		 *
		 * @param array $attributes The HTML attributes for the QR code image.
		 * @param array $args       The shortcode arguments.
		 * @param self  $context    The Shortcode instance.
		 */
		$attributes = apply_filters(
			'tec_events_qr_code_image_attributes',
			[
				'alt'      => sprintf(
					/* translators: %s: The event title or type of QR code */
					esc_attr__( 'QR Code for %s', 'the-events-calendar' ),
					$this->get_qr_code_alt_text( (int) $id, $args['mode'] )
				),
				'class'    => 'tec-events-qr-code__image',
				'data-url' => esc_url( $qr_url ),
			],
			$args,
			$this
		);

		$html = '<img src="' . $qr_img . '" ' . ( new Element_Attributes( $attributes ) )->get_attributes() . '>';

		/**
		 * Filters the complete QR code HTML output.
		 *
		 * @since 6.12.0
		 *
		 * @param string $html    The complete HTML output for the QR code.
		 * @param array  $args    The shortcode arguments.
		 * @param self   $context The Shortcode instance.
		 */
		return apply_filters(
			'tec_events_qr_code_html',
			$html,
			$args,
			$this
		);
	}

	/**
	 * Get a descriptive text for the QR code based on its type and event.
	 *
	 * @since 6.12.0
	 * @param int    $post_id The post ID.
	 * @param string $mode    The QR code mode.
	 * @return string The descriptive text for the QR code.
	 */
	private function get_qr_code_alt_text( int $post_id, string $mode ): string {
		switch ( $mode ) {
			case 'current':
				return esc_html__( 'current event', 'the-events-calendar' );
			case 'upcoming':
				return esc_html__( 'next upcoming event', 'the-events-calendar' );
			case 'specific':
				$title = get_the_title( $post_id );
				return $title ?: esc_html__( 'specific event', 'the-events-calendar' );
			case 'next':
				return esc_html__( 'next event in series', 'the-events-calendar' );
			default:
				return esc_html__( 'event', 'the-events-calendar' );
		}
	}
}
