<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\State;

/**
 * Class V2_Disable_Modal
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Admin
 *
 * @since TBD
 */
class V2_Disable_Modal {

	/**
	 * Modal ID.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $modal_id = 'tec-recurrence-migration__v2-disable-dialog';

	/**
	 * Modal target.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $modal_target = 'tec-recurrence-migration__v2-disable-dialog';

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
			"tribe_events_page_tribe-common" !== $screen->id
			&& $post_type !== $screen->id
		) {
			return false;
		}

		$state  = tribe( State::class );
		$phase  = $state->get_phase();

		return State::PHASE_MIGRATION_RUNNING === $phase || State::PHASE_MIGRATION_COMPLETE === $phase;
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
			'close_button_classes'    => 'tribe-dialog__close-button tribe-modal__close-button',
			'trigger'                 => 'trigger-dialog-tec-recurrence-migration__v2-disable-dialog',
			'title'                   => __( 'Heads up!', 'ical-tec' ),
			'overlay_click_closes'    => true,
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
		$state   = tribe( State::class );
		$content = __( 'Switching to the legacy calendar views may impact recurring events and Series.', 'ical-tec' );

		$args = $this->get_modal_args( $args );

		$dialog_view = tribe( 'dialog.view' );

		ob_start();
		$dialog_view->render_alert( $content, $args, static::$modal_id );
		$modal_content = ob_get_clean();

		$modal  = '<div class="tribe-common tec-upgrade-recurrence__modal-container tec-upgrade-recurrence__modal-container--v2-disable-dialog">';
		$modal .= '<span id="' . esc_attr( static::$modal_target ) . '" data-js="trigger-dialog-tec-recurrence-migration__v2-disable-dialog" data-content="dialog-content-tec-recurrence-migration__v2-disable-dialog"></span>';
		$modal .= $modal_content;
		$modal .= '</div>';

		return $modal;
	}
}
