<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\State;

/**
 * Class Modal
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Admin
 *
 * @since TBD
 */
class Progress_Modal {

	/**
	 * Modal ID.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $modal_id = 'tec-recurrence-migration__progress-dialog';

	/**
	 * Modal target.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $modal_target = 'tec-recurrence-migration__progress-dialog';

	/**
	 * Check if we should render the modal.
	 *
	 * @since TBD
	 *
	 * @return boolean Whether we should render the modal.
	 */
	public function should_render() {
		$screen = get_current_screen();

		$post_type = \Tribe__Events__Main::POSTTYPE;

		if (
			"edit-{$post_type}" !== $screen->id
			&& $post_type !== $screen->id
			&& 'tribe_events_page_aggregator' !== $screen->id
		) {
			return false;
		}

		$state  = tribe( State::class );
		$phase  = $state->get_phase();

		return State::PHASE_CANCELLATION_RUNNING === $phase;
	}

	/**
	 * Render the Manual Attendees modal.
	 *
	 * @since TBD
	 */
	public function render_modal() {
		if ( ! $this->should_render() ) {
			return;
		}

		wp_enqueue_style( 'tec-recurrence-upgrade-admin-css' );

		// Render the modal contents.
		echo $this->get_modal_content();
	}

	/**
	 * Get the default modal args.
	 *
	 * @since TBD
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return array The default modal args.
	 */
	public function get_modal_args( $args = [] ) {
		$state = tribe( State::class );
		$phase = $state->get_phase();

		$default_args = [
			'append_target'           => '#' . static::$modal_target,
			'trigger'                 => 'trigger-dialog-tec-recurrence-migration__progress-dialog',
			'button_display'          => false,
			'content_wrapper_classes' => 'tribe-dialog__wrapper tec-upgrade-recurrence tec-upgrade-recurrence--' . $phase,
			'title'                   => '',
			'overlay_click_closes'    => false,
		];

		return wp_parse_args( $args, $default_args );
	}

	/**
	 * Get the default modal contents.
	 *
	 * @since TBD
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return string The modal content.
	 */
	public function get_modal_content( $args = [] ) {
		$state = tribe( State::class );
		$phase = $state->get_phase();
		$path  = EVENTS_CALENDAR_PRO_DIR . '/src/admin-views/custom-tables-v1/recurrence/migration';

		ob_start();
		include_once $path . '/upgrade-logo.php';
		$logo = ob_get_clean();

		ob_start();
		include $path . '/phase/' . $phase . '.php';
		$content = ob_get_clean();

		$args = $this->get_modal_args( $args );

		$dialog_view = tribe( 'dialog.view' );

		ob_start();
		$dialog_view->render_modal( $content, $args, static::$modal_id );
		$modal_content = ob_get_clean();

		$modal  = '<div class="tribe-common tec-upgrade-recurrence__modal-container tec-upgrade-recurrence__modal-container--progress-dialog">';
		$modal .= '<span id="' . esc_attr( static::$modal_target ) . '" data-js="trigger-dialog-tec-recurrence-migration__progress-dialog" data-content="dialog-content-tec-recurrence-migration__progress-dialog"></span>';
		$modal .= $modal_content;
		$modal .= '</div>';

		return $modal;
	}

	/**
	 * Gets the script tag that auto-opens the modal.
	 *
	 * @return string
	 */
	public function get_modal_auto_trigger() {
		return '<script>jQuery( function() { jQuery( "#' . static::$modal_target . '" ).click(); } );</script>';
	}
}
