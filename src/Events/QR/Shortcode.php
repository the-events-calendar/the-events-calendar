<?php
/**
 * The Shortcode class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

/**
 * Class Shortcode
 *
 * @since   TBD
 *
 * @package TEC\Events\QR
 */
class Shortcode {
	/**
	 * The shortcode tag.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	private $shortcode_tag = 'tec_event_qr';

	/**
	 * The callback to render the shortcode.
	 *
	 * @since TBD
	 *
	 * @param array $atts The shortcode attributes.
	 *
	 * @return string The shortcode output.
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			[
				'mode' => 'current',
				'id'   => '',
				'size' => 150,
			],
			$atts,
			$this->shortcode_tag
		);

		$mode = in_array( $atts['mode'], [ 'current', 'next', 'id', 'series_next' ], true ) ? $atts['mode'] : 'current';
		$id   = absint( $atts['id'] );
		$size = absint( $atts['size'] );

		return '@TODO_ get the QR_' . $mode . ' of event with the ID: ' . $id . ' and size: ' . $size;
	}
}
