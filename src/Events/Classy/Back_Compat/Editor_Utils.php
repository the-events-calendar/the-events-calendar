<?php
/**
 * Editor_Utils implementation that provides compatibility with the legacy editor.
 *
 * @since TBD
 * @package TEC\Events\Classy\Back_Compat
 */

declare( strict_types=1 );

namespace TEC\Events\Classy\Back_Compat;

/**
 * Class Editor_Utils
 *
 * @see Tribe__Editor__Utils
 *
 * @since TBD
 */
class Editor_Utils {

	public function exclude_tribe_blocks( string $content ): string {
		return $content;
	}
}
