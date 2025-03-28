<?php
/**
 * The Shortcode class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use Tribe\Shortcode\Shortcode_Abstract;
use TEC\Common\QR\QR;

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
	protected $slug = 'tec_event_qr';

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
		'size' => 6,
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

		// @TODO This is a temporary solution to ensure the shortcode is working.

		$args = $this->get_arguments();

		$mode = in_array( $args['mode'], [ 'current', 'next', 'id', 'series_next' ], true ) ? $args['mode'] : 'current';
		$id   = absint( $args['id'] );
		$size = absint( $args['size'] );

		$qr_code = tribe( QR::class );

		if ( is_wp_error( $qr_code ) ) {
			return $qr_code;
		}

		$qr_img = $qr_code->size( $size )->get_png_as_base64( wp_json_encode( get_permalink( $id ) ) );

		// @TODO Add filters to allow for customizing the QR code image.
		// @TODO Add proper alt text to the image.

		return '<img alt="qr_code_image" src="' . $qr_img . '">';
	}
}
