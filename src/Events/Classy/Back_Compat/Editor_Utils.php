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

	/**
	 * Placeholder for the Editor_Utils instance.
	 *
	 * This just returns the content as-is.
	 *
	 * @since TBD
	 *
	 * @param string $content The content to be processed.
	 *
	 * @return string The content as-is.
	 */
	public function exclude_tribe_blocks( string $content ): string {
		return $content;
	}
}
