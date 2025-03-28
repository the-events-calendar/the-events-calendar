<?php
/**
 * The Shortcode class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use Tribe\Shortcode\Shortcode_Abstract;
use TEC\Common\QR\QR;
use TEC\Events\QR\Routes;

/**
 * Class Shortcode
 *
 * @since TBD
 *
 * @package TEC\Events\QR
 */
class Shortcode extends Shortcode_Abstract {
	/**
	 * The shortcode tag.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Default arguments to be merged into final arguments of the shortcode.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $default_arguments = [
		'id'   => '',
		'mode' => 'current',
		'size' => 4,
	];

	/**
	 * Array of callbacks for arguments validation.
	 *
	 * @var array
	 */
	public $validate_arguments_map = [
		'id'   => 'tribe_post_exists',
		'mode' => 'sanitize_title_with_dashes',
		'size' => 'absint',
	];

	/**
	 * Returns a shortcode's HTML.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_html() {
		$routes  = tribe( Routes::class );
		$qr_code = tribe( QR::class );
		$args    = $this->get_arguments();
		$mode    = $args['mode'] ?? 'current';
		$id      = $args['id'] ?? '';
		$size    = $args['size'] ?? 4;

		if ( is_wp_error( $qr_code ) ) {
			return $qr_code;
		}

		$qr_url = $routes->get_qr_url( $id, $mode );

		$qr_img = $qr_code->size( $size )->margin( 1 )->get_png_as_base64( $qr_url );

		// @TODO Add filters to allow for customizing the QR code image.
		// @TODO Add proper alt text to the image.

		return '<img alt="qr_code_image" src="' . $qr_img . '">';
	}
}
