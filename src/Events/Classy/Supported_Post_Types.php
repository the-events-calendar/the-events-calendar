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
	 * @since TBD
	 *
	 * @return array<string> The list of supported post types.
	 */
	private function get_supported_post_types(): array {
		return [
			TEC::POSTTYPE,
		];
	}
}
