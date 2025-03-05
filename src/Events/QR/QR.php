<?php
/**
 * A Facade for the QR code generator.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\phpqrcode\QRcode;

/**
 * A Facade for the QR code generator.
 *
 * @since   TBD
 *
 * @package TEC\Events\QR
 */
class QR {
	/**
	 * The level of the QR code.
	 *
	 * @since TBD
	 *
	 * @var int What type of error correction will be used on the QR code.
	 */
	protected $level = TEC_COMMON_QR_ECLEVEL_L;

	/**
	 * The size of the QR code.
	 *
	 * @since TBD
	 *
	 * @var int Stores the size of the QR code.
	 */
	protected $size = 3;

	/**
	 * The margin of the QR code.
	 *
	 * @since TBD
	 *
	 * @var int Stores the margin used to generate the QR code.
	 */
	protected $margin = 4;

	/**
	 * Change the level of Error Correction will be used on the QR code.
	 *
	 * @since TBD
	 *
	 * @param int $value What value will be set on level.
	 *
	 * @return $this
	 */
	public function level( int $value ): self {
		$this->level = $value;
		return $this;
	}

	/**
	 * Change the size of the QR code image.
	 *
	 * @since TBD
	 *
	 * @param int $value What value will be set on size.
	 *
	 * @return $this
	 */
	public function size( int $value ): self {
		$this->size = $value;
		return $this;
	}

	/**
	 * Change the margin of the QR code image.
	 *
	 * @since TBD
	 *
	 * @param int $value What value will be set on margin.
	 *
	 * @return $this
	 */
	public function margin( int $value ): self {
		$this->margin = $value;
		return $this;
	}

	/**
	 * Get the EC level of the QR code.
	 *
	 * @since TBD
	 *
	 * @return int Type of QR code used.
	 */
	protected function get_level(): int {
		return $this->level;
	}

	/**
	 * Get the size of the QR code.
	 *
	 * @since TBD
	 *
	 * @return int Size of the QR code.
	 */
	protected function get_size(): int {
		return $this->size;
	}

	/**
	 * Get the margin of the QR code.
	 *
	 * @since TBD
	 *
	 * @return int Margin used to be included in the QR code, helps with readability.
	 */
	protected function get_margin(): int {
		return $this->margin;
	}

	/**
	 * Get the QR code as a string.
	 *
	 * @since TBD
	 *
	 * @param string $data String used to generate the QR code.
	 *
	 * @return string The QR code as a string, not an actual readable string, it's a binary.
	 */
	public function get_png_as_string( string $data ): string {
		ob_start();
		QRcode::png( $data, false, $this->get_level(), $this->get_size(), $this->get_margin() );

		return ob_get_clean();
	}

	/**
	 * Get the QR code as a PNG base64 image, helpful to use when uploading the file would create duplicates.
	 *
	 * @since TBD
	 *
	 * @param string $data String used to generate the QR code.
	 *
	 * @return string QR Code as an embeddable Base64 image.
	 */
	public function get_png_as_base64( string $data ): string {
		$src = base64_encode( $this->get_png_as_string( $data ) );

		return 'data:image/png;base64,' . $src;
	}

	/**
	 * Get the QR code as a file uploaded to WordPress.
	 *
	 * @since TBD
	 *
	 * @param string $data String used to generate the QR code.
	 * @param string $name File name without the extension.
	 * @param string $folder Which folder under WP_CONTENT_DIR/uploads/ will be used to store the file.
	 *
	 * @return array{file: string, url: string, type: string, error: string|false} The QR uploaded file information.
	 */
	public function get_png_as_file( string $data, string $name, string $folder = 'tec-events-qr' ): array {
		$folder     = '/' . ltrim( $folder, '/' );
		$png_string = $this->get_png_as_string( $data );

		// Filters the upload directory but still use `wp_upload_bits` to create the file.
		$upload_bits_filter = static function ( $arr ) use ( $folder ) {
			$arr['url']    = str_replace( $arr['subdir'], $folder, $arr['url'] );
			$arr['path']   = str_replace( $arr['subdir'], $folder, $arr['path'] );
			$arr['subdir'] = $folder;
			return $arr;
		};

		add_filter( 'upload_dir', $upload_bits_filter );

		$filename    = sanitize_file_name( $name ) . '.png';
		$file_upload = wp_upload_bits( $filename, null, $png_string );

		remove_filter( 'upload_dir', $upload_bits_filter );

		return $file_upload;
	}
}
