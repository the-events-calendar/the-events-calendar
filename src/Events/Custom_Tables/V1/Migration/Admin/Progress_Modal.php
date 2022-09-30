<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use Tribe\Events\Test\WP_Screen;

/**
 * Class Modal
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Admin
 *
 * @since 6.0.0
 */
class Progress_Modal {

	/**
	 * Modal ID.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public static $modal_id = 'tec-recurrence-migration__progress-dialog';

	/**
	 * Modal target.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public static $modal_target = 'tec-recurrence-migration__progress-dialog';

	/**
	 * Check if we should render the modal.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean Whether we should render the modal.
	 */
	public function should_render() {
		$screen = get_current_screen();

		/**
		 * A filter to override when the migration maintenance modal should display and lock the screen.
		 *
		 * @since 6.0.0
		 *
		 * @param bool|null $should_render A bool flag to override when a maintenance modal should render.
		 * @param WP_Screen $screen        The current WP_Screen instance.
		 */
		$should_render = apply_filters( 'tec_events_custom_tables_v1_should_render_maintenance_modal', null, $screen );

		// Did we override?
		if ( $should_render !== null ) {
			return $should_render;
		}

		$post_type           = \Tribe__Events__Main::POSTTYPE;
		$page_ids_to_disable = [
			"edit-{$post_type}",
			$post_type,
			'tribe_events_page_aggregator',
			'tribe_events_page_tribe-admin-manager',
			'edit-tribe_events_cat',
			'edit-post_tag',
			'edit-tribe_venue',
			'edit-tribe_organizer'
		];

		// Are we on one of the pages that might have a maintenance modal lock?
		if ( ! in_array( $screen->id, $page_ids_to_disable, true ) ) {
			return false;
		}

		// If normal tag page, don't render.
		if ( $screen->id === 'edit-post_tag' && $screen->post_type !== $post_type ) {
			return false;
		}

		// We are on the right page, lets see if we are in the right state?
		return tribe( State::class )->should_lock_for_maintenance();
	}

	/**
	 * Outputs the maintenance modal markup.
	 *
	 * @since 6.0.0
	 */
	public function render_modal() {
		if ( ! $this->should_render() ) {
			return;
		}

		// Render the modal contents.
		echo $this->get_modal_content();
	}

	/**
	 * Get the default modal args.
	 *
	 * @since 6.0.0
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
			'content_wrapper_classes' => 'tribe-dialog__wrapper tec-ct1-dialog-wrapper tec-upgrade-recurrence--' . $phase,
			'title'                   => '',
			'overlay_click_closes'    => false,
		];

		return wp_parse_args( $args, $default_args );
	}

	/**
	 * Get the default modal contents.
	 *
	 * @since 6.0.0
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return string The modal content.
	 */
	public function get_modal_content( $args = [] ) {
		$template_path = tribe( 'tec.main' )->plugin_path . 'src/Events/Custom_Tables/V1/admin-views/migration';
		$dialog_view   = tribe( 'dialog.view' );
		$args          = $this->get_modal_args( $args );
		$text          = tribe( String_Dictionary::class );

		ob_start();
		include $template_path.'/upgrade-box.php';
		$content = ob_get_clean();

		ob_start();
		$dialog_view->render_modal( $content, $args, static::$modal_id );
		$modal_content = ob_get_clean();

		$modal  = '<div class="tribe-common tec-ct1-dialog-wrapper tec-upgrade-recurrence__modal-container--progress-dialog">';
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
