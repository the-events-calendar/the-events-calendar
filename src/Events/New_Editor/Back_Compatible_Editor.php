<?php
/**
 * An Editor implementation providing the feature detection methods required by existing implementations.
 *
 * This is used by the feature to replace the legacy `editor` binding in the container.
 *
 * @since   TBD
 *
 * @package TEC\Events\New_Editor;
 */

namespace TEC\Events\New_Editor;

/**
 * Class Back_Compatible_Editor.
 *
 * @since   TBD
 *
 * @package TEC\Events\New_Editor;
 */
class Back_Compatible_Editor {
	/**
	 * Returns whether the editor should load Blocks.
	 *
	 * The return value of this function is based on the fact that this method, once Legacy Blocks
	 * support is removed in other parts of this feature code, will only be called to know whether
	 * to bail out of Classic Editor logic.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Block Editor is being used.
	 */
	public function should_load_blocks(): bool {
		return true;
	}

	/**
	 * Hard-coded negative return value to bail out of any existing legacy Block Editor logic
	 * that might load when the post is an Event.
	 *
	 * @since TBD
	 *
	 * @return bool Hard-coded false to mean "never load legacy blocks on Events".
	 */
	public function is_events_using_blocks():bool{
		return true;
	}
}
