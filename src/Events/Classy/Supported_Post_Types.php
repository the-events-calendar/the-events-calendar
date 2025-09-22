<?php
/**
 * Classy Supported_Post_Types Trait
 *
 * @since TBD
 */

declare( strict_types=1 );

namespace TEC\Events\Classy;

use Tribe__Events__Main as TEC;

/**
 * Trait Supported_Post_Types
 *
 * @since TBD
 */
trait Supported_Post_Types {
	/**
	 * Returns the list of post types that this controller supports.
	 *
	 * Note: this method is directly used by the Common Classy controller as an early detection
	 * of supported post types. Any change to the visibility or name of this method should update
	 * the Common Classy controller accordingly.
	 *
	 * @since TBD
	 *
	 * @return array<string> The list of supported post types.
	 *
	 * @see   Controller::get_supported_post_types() for the method that is using this trait.
	 *
	 */
	public function get_supported_post_types(): array {
		return [
			TEC::POSTTYPE,
		];
	}
}
