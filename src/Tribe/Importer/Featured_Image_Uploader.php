<?php


class Tribe__Events__Importer__Featured_Image_Uploader {

	/**
	 * @var array A single importing file row.
	 */
	protected $record;

	/**
	 * @var string|int Either an absolute URL to an image file or a media attachment post ID.
	 */
	protected $featured_image;

	/**
	 * Tribe__Events__Importer__Featured_Image_Uploader constructor.
	 *
	 * @var array A single importing file row.
	 */
	public function __construct( $featured_image = null ) {

		$this->featured_image = $featured_image;
	}

	/**
	 * Uploads a file and creates the media attachment or simply returns the attachment ID if existing.
	 *
	 * @return int|bool The attachment post ID if the uploading and attachment is successful or the ID refers to an attachment;
	 *                  `false` otherwise.
	 */
	public function upload_and_get_attachment() {
		if ( empty( $this->featured_image ) ) {
			return false;
		}

		if ( is_string( $this->featured_image ) && ! is_numeric( $this->featured_image ) ) {
			$id = $this->upload_file( $this->featured_image );
		} elseif ( $post = get_post( $this->featured_image ) ) {
			$id = $post && $post->post_type === 'attachment' ? $this->featured_image : false;
		} else {
			$id = false;
		}

		return $id;
	}

	/**
	 * @param strin $file_url
	 *
	 * @return int
	 */
	private function upload_file( $file_url ) {
		if ( ! filter_var( $file_url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$contents = @file_get_contents( $file_url );
		if ( $contents === false ) {
			return false;
		}

		$upload = wp_upload_bits( basename( $file_url ), null, $contents );

		if ( isset( $upload['error'] ) && $upload['error'] ) {
			return false;
		}

		$type = '';
		if ( ! empty( $upload['type'] ) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ( $mime ) {
				$type = $mime['type'];
			}
		}

		$attachment = array(
			'post_title'     => basename( $upload['file'] ),
			'post_content'   => '',
			'post_type'      => 'attachment',
			'post_mime_type' => $type,
			'guid'           => $upload['url'],
		);

		$id = wp_insert_attachment( $attachment, $upload['file'] );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		return $id;
	}

}