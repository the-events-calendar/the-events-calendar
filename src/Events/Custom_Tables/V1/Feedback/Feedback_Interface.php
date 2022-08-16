<?php
/**
 * The API provided by the plugin feedback channels.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Feedback
 */

namespace TEC\Events\Custom_Tables\V1\Feedback;

/**
 * Interface Feedback_Interface
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Feedback
 */
interface Feedback_Interface {

	/**
	 * Filters whole editor configuration, to localize information
	 * the Blocks Editor will be able to use.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $config A map of the current editor configuration, for all plugins.
	 *
	 * @return array<string,mixed> The modified plugin configuration.
	 */
	public function filter_editor_config( array $config = [] );

	/**
	 * Renders the feedback prompt in any context, but the Blocks Editor one.
	 *
	 * @since TBD
	 */
	public function render_classic_editor_version();

	/**
	 * Returns the HTML contents of the notice.
     *
     * Note: the content MUST be wrapped in an HTML to correctly render in the Blocks Editor context.
	 *
	 * @since TBD
	 *
	 * @return string The notice HTML contents.
	 */
	public function get_notice_contents();
}
