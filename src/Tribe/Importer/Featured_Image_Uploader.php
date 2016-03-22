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
	 * @var bool|array
	 */
	protected $_attachment_guids = false;

	/**
	 * @var bool|array
	 */
	protected $_original_urls = false;

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
			$existing = $this->get_attachment_ID_from_url( $this->featured_image );
			$id       = $existing ? $existing : $this->upload_file( $this->featured_image );
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
		update_post_meta( $id, '_tribe_importer_original_url', $file_url );

		return $id;
	}

	protected function get_attachment_ID_from_url( $featured_image ) {
		$this->maybe_fetch_all_attachment_guids();
		$this->maybe_fetch_all_attacment_original_urls();

		/** @var \wpdb $wpdb */
		global $wpdb;

		if ( isset( $this->_attachment_guids[ $featured_image ] ) ) {
			return $this->_attachment_guids[ $featured_image ];
		} elseif ( isset( $this->_original_urls[ $featured_image ] ) ) {
			return $this->_original_urls[ $featured_image ];
		}

		$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", $featured_image ) );

		return empty( $id ) ? false : $id;
	}

	protected function maybe_fetch_all_attachment_guids() {
		if ( $this->_attachment_guids === false ) {
			/** @var \wpdb $wpdb */
			global $wpdb;
			$guids = $wpdb->get_results( "SELECT ID, guid FROM $wpdb->posts where post_type = 'attachment'" );

			$this->_attachment_guids = $guids ? array_combine( wp_list_pluck( $guids, 'guid' ), wp_list_pluck( $guids, 'ID' ) ) : array();
		}
	}

	protected function maybe_fetch_all_attacment_original_urls() {
		if ( $this->_original_urls === false ) {
			/** @var \wpdb $wpdb */
			global $wpdb;
			$original_urls = $wpdb->get_results( "SELECT p.ID, pm.meta_value FROM $wpdb->posts p
					JOIN $wpdb->postmeta pm
					ON p.ID = pm.post_id
					WHERE p.post_type = 'attachment' AND pm.meta_key = '_tribe_importer_original_url'" );

			$this->_original_urls = $original_urls ? array_combine( wp_list_pluck( $original_urls, 'meta_value' ), wp_list_pluck( $original_urls, 'ID' ) ) : array();
		}
	}

}