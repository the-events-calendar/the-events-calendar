<?php

namespace TEC\Events\Views\Modifiers;

use WP_Post;

/**
 * Class Visibility_Modifier_Abstract.
 *
 * @since 6.4.1
 *
 * @package TEC\Events\Views\Modifiers
 */
abstract class Visibility_Modifier_Abstract {

	/**
	 * @since 6.4.1
	 *
	 * @var array The options to be used for processing visibility operations.
	 */
	protected array $options = [];

	/**
	 * @since 6.4.1
	 *
	 * @param array $options The options to be used for processing visibility operations.
	 */
	public function __construct( array $options ) {
		$this->set_options( $options );
	}

	/**
	 * Should validate and store the options to be used for processing visibility operations.
	 *
	 * @since 6.4.1
	 *
	 * @param array $options The options to be used for processing visibility operations.
	 */
	public function set_options( array $options ) {
		// Merge the passed options with our defaults.
		$this->options = array_merge( $this->get_defaults(), $options );
	}


	/**
	 * Get the list of valid options for visibility.
	 *
	 * @since 6.4.1
	 *
	 * @return array The visibility options.
	 */
	public function get_options(): array {
		return $this->options;
	}

	/**
	 * Get the default options.
	 *
	 * @since 6.4.1
	 *
	 * @return array The default options.
	 */
	abstract public function get_defaults(): array;

	/**
	 * Check if the visibility modifier's linked data (i.e. phone number) is visible on a certain page.
	 * This method should be implemented by the extending class.
	 *
	 * @since 6.4.1
	 *
	 * @param string                  $area The area for this visibility modifier.
	 * @param null|int|string|WP_Post $post The post to check for this visibility modifier.
	 *
	 * @return bool True if the linked data is visible on the given page, false otherwise.
	 */
	abstract public function check_visibility( string $area, $post = null ): bool;

	/**
	 * The key for this visibility implementation, mostly used for hook prefixing.
	 *
	 * @since 6.4.1
	 *
	 * @return string Get the slug for this visibility implementation.
	 */
	abstract public function get_slug(): string;

	/**
	 * Check if the visibility modifier's linked data (i.e. phone number) is visible on a certain page.
	 * Runs through filters to allow for more granular control.
	 *
	 * @since 6.4.1
	 *
	 * @param string                  $area The area for this visibility modifier.
	 * @param null|int|string|WP_Post $post The post to check for this visibility modifier.
	 *
	 * @return bool True if the linked data is visible on the given page, false otherwise.
	 */
	public function is_visible( string $area, $post = null ): bool {
		$is_visible = $this->check_visibility( $area, $post );
		$slug       = $this->get_slug();

		/**
		 * Filter the value of the visibility modifier for all areas, it will also pass the post for context and area for granularity.
		 *
		 * @since 6.4.1
		 *
		 * @param bool                    $is_visible True if  this visibility modifier is visible in the given area, false otherwise.
		 * @param string                  $area       The area to check for this visibility modifier.
		 * @param null|int|string|WP_Post $post       The post to check for this visibility modifier.
		 */
		$is_visible = (bool) apply_filters( "tec_events_{$slug}_visibility_is_visible", $is_visible, $area, $post );

		/**
		 * Filter the visibility of the visibility modifier specifically for a certain area.
		 *
		 * @since 6.4.1
		 *
		 * @param bool                    $is_visible True if this visibility modifier is visible in the given area, false otherwise.
		 * @param string                  $area       The area to check for this visibility modifier.
		 * @param null|int|string|WP_Post $post       The post to check for this visibility modifier.
		 */
		$is_visible = (bool) apply_filters( "tec_events_{$slug}_visibility_is_visible:{$area}", $is_visible, $area, $post );

		$post_id = $post instanceof WP_Post ? $post->ID : $post;
		/**
		 * Filter the visibility of the visibility modifier specifically for a post in a certain area.
		 *
		 * @since 6.4.1
		 *
		 * @param bool                    $is_visible True if this visibility modifier is visible in the given area, false otherwise.
		 * @param string                  $area       The area to check for this visibility modifier.
		 * @param null|int|string|WP_Post $post       The post to check for this visibility modifier.
		 */
		return (bool) apply_filters( "tec_events_{$slug}_visibility_is_visible:{$area}:{$post_id}", $is_visible, $area, $post );
	}
}
