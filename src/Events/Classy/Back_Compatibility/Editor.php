<?php
/**
 * An Editor implementation providing the feature detection methods required by existing implementations.
 *
 * This is used by the feature to replace the legacy `editor`, `events.editor` and `events.editor.compatibility`
 * bindings in the container.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy\Back_Compatibility;

/**
 * Class Back_Compatible_Editor.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */
class Editor {
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
		return false;
	}

	/**
	 * Hard-coded negative return value to bail out of any existing legacy Block Editor logic
	 * that might load when the post is an Event.
	 *
	 * @since TBD
	 *
	 * @return bool Hard-coded false to mean "never load legacy blocks on Events".
	 */
	public function is_events_using_blocks(): bool {
		return false;
	}

	/**
	 * Checks if the Block Editor toggle is enabled for the current context.
	 *
	 * This method determines whether the Block Editor has been toggled on, which could be based
	 * on user settings or other conditions. In this implementation, it always returns false,
	 * indicating that the Block Editor is not toggled on.
	 *
	 * @since TBD
	 *
	 * @return bool True if the Block Editor is toggled on, false otherwise.
	 */
	public function is_blocks_editor_toggled_on(): bool {
		return false;
	}

	/**
	 * Checks if the current post type is an Event.
	 *
	 * This method determines whether the post being edited or viewed is of the Events post type.
	 * In this implementation, it always returns false, indicating that the current post is not
	 * an Event.
	 *
	 * @since TBD
	 *
	 * @return bool True if the current post is an Event, false otherwise.
	 */
	public function is_events_post_type(): bool {
		return false;
	}
}
