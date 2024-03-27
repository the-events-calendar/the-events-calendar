<?php
/**
 * Elementor Template Meta Courier.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template;

use TEC\Events\Integrations\Plugins\Elementor\Template\Documents\Event_Single;
use WP_Post;
use WP_Error;

/**
 * Class Meta Courier.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */
class Meta_Courier {

	/**
	 * The meta key for the Elementor template type.
	 *
	 * @since TBD
	 *
	 * @var string The meta key for the Elementor template type.
	 */
	protected string $meta_elementor_template_type = '_elementor_template_type';

	/**
	 * The meta key for the Elementor data.
	 *
	 * @since TBD
	 *
	 * @var string The meta key for the Elementor data.
	 */
	protected string $meta_elementor_data = '_elementor_data';

	/**
	 * Which post we will copy to.
	 *
	 * @since TBD
	 *
	 * @var WP_Post The post to copy the meta to.
	 */
	protected WP_Post $post;

	/**
	 * Which template post to copy the meta from.
	 *
	 * @since TBD
	 *
	 * @var WP_Post The post to copy the meta from.
	 */
	protected WP_Post $template_post;

	/**
	 * Protected constructor to prevent creating a new instance of the courier without the factory method.
	 *
	 * @since TBD
	 */
	protected function __construct() {
		// Intentionally left empty.
	}

	/**
	 * Retrieves an instance of the Meta_Courier to enable
	 *
	 * @since TBD
	 *
	 * @param int|string|array|WP_Post $post The post to copy the meta to.
	 *
	 * @return WP_Error|static
	 */
	public static function to_post( $post ) {
		if ( is_numeric( $post ) || is_array( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof WP_Post ) {
			return new WP_Error( 'tec-events-integration-elementor-meta-courier-invalid-post' );
		}

		$courier  = new static();
		$set_post = $courier->set_post( $post );

		if ( $set_post instanceof WP_Error ) {
			return $set_post;
		}

		return $courier;
	}

	/**
	 * Get all the meta keys used by the courier.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_meta_keys_map(): array {
		return [
			$this->meta_elementor_data          => 'carry_elementor_data',
			$this->meta_elementor_template_type => 'carry_elementor_template_type',
		];
	}

	/**
	 * Determines if we should carry the meta key from template to post.
	 *
	 * @since TBD
	 *
	 * @param string $key Which key we are talking about.
	 *
	 * @return bool
	 */
	public function should_carry( string $key ): bool {
		return array_key_exists( $key, $this->get_meta_keys_map() );
	}

	/**
	 * Gets our base template for which we are copying the meta from.
	 *
	 * @since TBD
	 *
	 * @return WP_Post|null
	 */
	protected function get_template_post(): ?WP_Post {
		if ( ! isset( $this->template_post ) ) {
			$this->template_post = tribe( Importer::class )->get_template();
		}

		return $this->template_post;
	}

	/**
	 * Get the valid post types for the courier to copy into.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_valid_post_types(): array {
		return [ \Tribe__Events__Main::POSTTYPE ];
	}

	/**
	 * Set the post for the courier.
	 *
	 * @since TBD
	 *
	 * @param WP_Post $post The post to set.
	 *
	 * @return WP_Error|bool
	 */
	protected function set_post( WP_Post $post ) {
		if ( ! in_array( $post->post_type, $this->get_valid_post_types(), true ) ) {
			return new WP_Error( 'tec-events-integration-elementor-meta-courier-invalid-post-type' );
		}

		$this->post = $post;

		return true;
	}

	/**
	 * Get the post which we will copy into for this courier.
	 *
	 * @since TBD
	 *
	 * @return WP_Post
	 */
	public function get_post(): WP_Post {
		return $this->post;
	}

	/**
	 * Carry the meta from the template to the post.
	 *
	 * @since TBD
	 *
	 * @uses carry_elementor_data
	 * @uses carry_elementor_template_type
	 *
	 * @param string $key The key to carry.
	 *
	 * @return WP_Error|bool|int
	 */
	public function carry( string $key ) {
		if ( ! $this->should_carry( $key ) ) {
			return new WP_Error( 'tec-events-integration-elementor-meta-courier-invalid-key' );
		}

		$callbacks = $this->get_meta_keys_map();
		$method    = $callbacks[ $key ] ?? null;

		if ( ! method_exists( $this, $method ) ) {
			return new WP_Error( 'tec-events-integration-elementor-meta-courier-invalid-method' );
		}

		return $this->{$method}();
	}

	/**
	 * Copy the Elementor template data from the template post to the current post.
	 *
	 * @since TBD
	 *
	 * @param bool $force_delivery Whether to force the delivery of the meta.
	 *
	 * @return bool|int|WP_Error
	 */
	protected function carry_elementor_data( bool $force_delivery = false ) {
		$template_post = $this->get_template_post();
		if ( ! $template_post ) {
			return new WP_Error( 'tec-events-integration-elementor-meta-courier-no-template' );
		}

		$elementor_data_raw = get_post_meta( $template_post->ID, $this->meta_elementor_data, true );
		$elementor_data     = json_decode( $elementor_data_raw, true );

		if ( ! $elementor_data_raw || ! is_array( $elementor_data ) ) {
			return new WP_Error( 'tec-events-integration-elementor-meta-courier-no-elementor-data' );
		}

		$post = $this->get_post();
		if ( ! $force_delivery && metadata_exists( 'post', $post->ID, $this->meta_elementor_data ) ) {
			return new WP_Error( 'tec-events-integration-elementor-meta-courier-elementor-data-exists' );
		}

		// If we are not forcing delivery, we will add the meta.
		if ( ! $force_delivery ) {
			return add_post_meta( $post->ID, $this->meta_elementor_data, $elementor_data_raw, true );
		}

		// In case of force delivery, we will update the meta and not care about the existing one.
		return update_post_meta( $post->ID, $this->meta_elementor_data, $elementor_data_raw );
	}

	/**
	 * Copy the Elementor template type from the template post to the current post.
	 *
	 * @since TBD
	 *
	 * @param bool $force_delivery Whether to force the delivery of the meta.
	 *
	 * @return bool|int|WP_Error
	 */
	protected function carry_elementor_template_type( bool $force_delivery = false ) {
		$post = $this->get_post();
		if ( ! $force_delivery && metadata_exists( 'post', $post->ID, $this->meta_elementor_template_type ) ) {
			return new WP_Error( 'tec-events-integration-elementor-meta-courier-elementor-template-type-exists' );
		}

		$value = Event_Single::get_type();

		// If we are not forcing delivery, we will add the meta.
		if ( ! $force_delivery ) {
			return add_post_meta( $post->ID, $this->meta_elementor_template_type, $value, true );
		}

		// In case of force delivery, we will update the meta and not care about the existing one.
		return update_post_meta( $post->ID, $this->meta_elementor_template_type, $value );
	}
}
