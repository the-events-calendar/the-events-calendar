<?php
/**
 * Class to handle the Elementor template content for events.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template;

use WP_Error;
use WP_Post;

use Elementor\Plugin;
use Tribe__Events__Main as TEC;

/**
 * Class Content
 *
 * @todo    Rename this file to something that makes more sense, this used to have multiple purposes we reduced scope.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */
class Content {
	/**
	 * Checks if the current event needs an Elementor template override.
	 * If we have the template set to our template, use the internal blank post template
	 *
	 * @since TBD
	 *
	 * @param mixed $post_id The post ID to check. If null will use the current post.
	 *
	 * @return bool
	 */
	public function is_override( $post_id = null ): bool {
		$template = tribe( Importer::class )->get_template();

		// Ensure we have a template to use.
		if ( null === $template ) {
			return false;
		}

		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( $post->post_type !== TEC::POSTTYPE ) {
			return false;
		}

		$document = Plugin::$instance->documents->get( $post->ID );

		if ( ! $document ) {
			return false;
		}

		return $document->is_built_with_elementor();
	}

	/**
	 * Saves the existing Post Content as Post Content Filtered.
	 *
	 * @since TBD
	 *
	 * @param int|array|WP_Post|string $post         Which post to save the content for.
	 * @param bool                     $force_update Whether to force the update or not. Default: false.
	 *
	 * @return int|WP_Error
	 */
	public function save_post_content_as_filtered_content( $post, $force_update = false ) {
		if ( is_numeric( $post ) || is_array( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof WP_Post ) {
			return new WP_Error( 'tec-events-integration-elementor-post-content-invalid-post' );
		}

		if ( $post->post_type !== TEC::POSTTYPE ) {
			return new WP_Error( 'tec-events-integration-elementor-post-content-invalid-post-type' );
		}

		if ( ! $force_update && ! empty( $post->post_content_filtered ) ) {
			return new WP_Error( 'tec-events-integration-elementor-post-content-filtered-exists' );
		}

		return wp_update_post(
			[
				'ID'                    => $post->ID,
				'post_content_filtered' => $post->post_content,
			]
		);
	}
}
