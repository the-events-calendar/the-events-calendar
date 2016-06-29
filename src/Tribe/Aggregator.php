<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// bail if already defined
if ( class_exists( 'Tribe__Events__Aggregator' ) ) {
	return;
}

class Tribe__Events__Aggregator {
	/**
	 * @var Tribe__Events__Aggregator Event Aggregator bootstrap class
	 */
	protected static $instance;

	/**
	 * @var Tribe__Events__Aggregator__Page Event Aggregator page root object
	 */
	public $page;

	/**
	 * @var Tribe__Events__Aggregator__Service Event Aggregator service object
	 */
	public $service;

	/**
	 * @var Tribe__PUE__Checker PUE Checker object
	 */
	public $pue_checker;

	/**
	 * @var string Event Aggregator cache key prefix
	 */
	public $cache_group = 'tribe_ea';

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Aggregator
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor!
	 */
	public function __construct() {
		$this->page        = Tribe__Events__Aggregator__Page::instance();
		$this->service     = Tribe__Events__Aggregator__Service::instance();
		$this->pue_checker = new Tribe__PUE__Checker( 'http://tri.be/', 'event-aggregator' );
	}

	/**
	 * Get event-aggregator origins
	 *
	 * @return array
	 */
	public function get_origins() {
		$origins = get_transient( "{$this->cache_group}_origins" );

		if ( ! $origins ) {
			$origins = $this->service->get_origins();

			if ( is_wp_error( $origins ) ) {
				return $origins;
			}

			set_transient( "{$this->cache_group}_origins", $origins, 6 * HOUR_IN_SECONDS );
		}

		// Let's build out the translated text based on the names that come back from the EA service
		foreach ( $origins as &$origin ) {
			$origin->text = __( $origin->name, 'the-events-calendar' );
		}

		return apply_filters( 'tribe_ea_origins', $origins );
	}

	/**
	 * Fetches an image from the service and saves it to the filesystem if needed
	 *
	 * @param string $image_id EA Image ID
	 *
	 * @return stdClass {
	 *     @type int        $post_id      Attachment ID on WP
	 *     @type string     $filename     Name of the image file
	 *     @type string     $path         Absolute path of the image
	 *     @type string     $extension    Extension of the image
	 * }
	 */
	public function get_image( $image_id ) {
		$tribe_ea_meta_key = 'tribe_ea_image_id';

		// Prevent Possible duplicated includes
		if ( ! function_exists( 'wp_upload_dir' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$query = new WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',

			// Fetch the first only
			'posts_per_page' => 1,

			// We only need the ID
			'fields'         => 'ids',
			'meta_key'       => $tribe_ea_meta_key,
			'meta_value'     => $image_id,
		) );

		$upload_dir = wp_upload_dir();
		$file = new stdClass;

		// if the file has already been added to the filesystem, don't create a duplicate...re-use it
		if ( $query->have_posts() ) {
			$attachment = reset( $query->posts );
			$attachment_meta = wp_get_attachment_meta( $attachment );

			$file->post_id   = (int) $attachment;
			$file->filename  = basename( $attachment_meta['file'] );
			$file->path      = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $attachment_meta['file'];

			// Fetch teh Extension for this filename
			$filetype = wp_check_filetype( $file->filename, null );
			$file->extension = $filetype['ext'];

			return $file;
		}

		// fetch an image
		$response = $this->service->get_image( $image_id );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// if the reponse isn't an image then we need to bail
		if ( ! preg_match( '/image/', $response['headers']['content-type'] ) ) {
			return $response;
		}

		// Fetch the Extension (it's safe because it comes from our service)
		$extension = str_replace( 'image/', '', $response['headers']['content-type'] );

		if (
			preg_match( '/filename="([^"]+)"/', $response['headers']['content-disposition'], $matches )
			&& ! empty( $matches[1] )
		) {
			$filename = $matches[1];
		} else {
			$filename = md5( $response['body'] ) . '.' . $extension;
		}

		// Clean the Filename
		$filename = sanitize_file_name( $filename );

		// get the file type
		$filetype = wp_check_filetype( basename( $filename ), null );

		// save the file to the filesystem in the upload directory somewhere
		$upload_results = wp_upload_bits( $filename, null, $response['body'] );

		// create attachment args
		$attachment = array(
			'guid'           => $upload_dir['url'] . '/' . $filename,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => $filetype,
		);

		// insert the attachment
		if ( ! $attachment_id = wp_insert_attachment( $attachment, $upload_results['file'] ) ) {
			return new WP_Error( 'tribe-ea-attachment-error', __( 'Unable to create an attachment post for the imported Event Aggregator image', 'the-events-calendar' ) );
		}

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Generate attachment metadata
		$attachment_meta = wp_generate_attachment_metadata( $attachment_id, $upload_results['file'] );
		wp_update_attachment_metadata( $attachment_id, $attachment_meta );

		// add our own custom meta field so the image is findable
		update_post_meta( $attachment_id, $tribe_ea_meta_key, $filename );

		$file->post_id   = (int) $attachment_id;
		$file->filename  = $filename;
		$file->path      = $upload_results['file'];
		$file->extension = $extension;

		return $file;
	}
}
