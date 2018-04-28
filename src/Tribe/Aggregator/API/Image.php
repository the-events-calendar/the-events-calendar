<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__API__Image extends Tribe__Events__Aggregator__API__Abstract {
	/**
	 * Fetches an image from the service and saves it to the filesystem if needed
	 *
	 * @param  string                                      $image_id  EA Image ID
	 * @param  Tribe__Events__Aggregator__Record__Abstract $record    Record Object
	 *
	 * @return WP_Error|stdClass {
	 *     @type int        $post_id      Attachment ID on WP
	 *     @type string     $filename     Name of the image file
	 *     @type string     $path         Absolute path of the image
	 *     @type string     $extension    Extension of the image
	 * }
	 *
	 */
	public function get( $image_id, $record = false ) {
		$tribe_aggregator_meta_key = 'tribe_aggregator_image_id';

		// Prevent Possible duplicated includes
		if ( ! function_exists( 'wp_upload_dir' ) || ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$query = new WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',

			// Fetch the first only
			'posts_per_page' => 1,

			// We only need the ID
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => $tribe_aggregator_meta_key,
					'value' => $image_id,
				),
			),
		) );

		$upload_dir = wp_upload_dir();
		$file = new stdClass;

		// if the file has already been added to the filesystem, don't create a duplicate...re-use it
		if ( $query->have_posts() ) {
			$attachment = reset( $query->posts );
			$attachment_meta = wp_get_attachment_metadata( $attachment );

			$file->post_id   = (int) $attachment;
			$file->filename  = basename( $attachment_meta['file'] );
			$file->path      = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $attachment_meta['file'];

			// Fetch the Extension for this filename
			$filetype = wp_check_filetype( $file->filename, null );
			$file->extension = $filetype['ext'];
			$file->status    = 'skipped';

			return $file;
		}

		// fetch an image
		$response = $this->service->get_image( $image_id, $record );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Get Headers and Body
		$headers = wp_remote_retrieve_headers( $response );
		$body = wp_remote_retrieve_body( $response );

		$is_invalid_image = empty( $headers['content-type'] ) && empty( $headers['content-disposition'] ) ;

		// Baild if we don't have Content type or Disposition
		if ( $is_invalid_image ) {
			return new WP_Error( 'invalid-file-headers', $body );
		}

		// if the response isn't an image then we need to bail
		if ( ! preg_match( '/image/', $headers['content-type'] ) ) {
			/**
			 * @todo  See a way for Tribe__Errors to handle overwriting
			 */
			return new WP_Error( 'invalid-image', $body );
		}

		// Fetch the Extension (it's safe because it comes from our service)
		$extension = str_replace( 'image/', '', $headers['content-type'] );

		// Removed Query String
		if ( false !== strpos( $extension, '?' ) ) {
			$parts = explode( '?', $extension );
			$extension = reset( $parts );
		}

		if (
			preg_match( '/filename="([^"]+)"/', $headers['content-disposition'], $matches )
			&& ! empty( $matches[1] )
		) {
			$filename = $matches[1];
			// Removed Query String
			if ( false !== strpos( $filename, '?' ) ) {
				$parts = explode( '?', $filename );
				$filename = reset( $parts );
			}
		} else {
			$filename = md5( $body ) . '.' . $extension;
		}

		// Clean the Filename
		$filename = sanitize_file_name( $filename );

		// get the file type
		$filetype = wp_check_filetype( basename( $filename ), null );

		// save the file to the filesystem in the upload directory somewhere
		$upload_results = wp_upload_bits( $filename, null, $body );

		// if the file path isn't set, all hope is lost
		if ( empty( $upload_results['file'] ) ) {
			return tribe_error( 'core:aggregator:invalid-image-path' );
		}

		// create attachment args
		$attachment = array(
			'guid'           => $upload_dir['url'] . '/' . $filename,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => $filetype['type'],
		);

		// insert the attachment
		if ( ! $attachment_id = wp_insert_attachment( $attachment, $upload_results['file'] ) ) {
			return tribe_error( 'core:aggregator:attachment-error' );
		}

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Generate attachment metadata
		$attachment_meta = wp_generate_attachment_metadata( $attachment_id, $upload_results['file'] );
		wp_update_attachment_metadata( $attachment_id, $attachment_meta );

		// add our own custom meta field so the image is findable
		update_post_meta( $attachment_id, $tribe_aggregator_meta_key, $image_id );

		$file->post_id   = (int) $attachment_id;
		$file->filename  = $filename;
		$file->path      = $upload_results['file'];
		$file->extension = $extension;
		$file->status    = 'created';

		return $file;
	}
}
