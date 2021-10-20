<?php
/**
 * Handles the binding and control of anything that is feedback related.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Feedback
 */

namespace TEC\Events\Custom_Tables\V1\Feedback;

/**
 * Class ServiceProvider
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Feedback
 */
class Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Register the feedback related implementations and hooks.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->container->singleton( Feedback_Interface::class, Google_Form_Feedback::class );

		add_action(
			'wp_after_admin_bar_render',
			$this->container->callback( Feedback_Interface::class, 'render_classic_editor_version' )
		);

		add_filter(
			'tribe_editor_config',
			$this->container->callback( Feedback_Interface::class, 'filter_editor_config' )
		);
	}
}
