<?php

namespace Tribe\Events\Editor;

use Tribe__Events__Main;
use Tribe__Date_Utils as Dates;

/**
 * Events block editor hooks.
 *
 * @since 5.12.0
 */
class Hooks extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.12.0
	 */
	public function register() {
		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( static::class, $this );

		$this->add_actions();
	}

	/**
	 * Adds the actions required by each Views v2 component.
	 *
	 * @since 5.12.0
	 */
	protected function add_actions() {
		add_action( 'current_screen', [ $this, 'add_widget_resources' ] );
		add_action( 'save_post_' . Tribe__Events__Main::POSTTYPE, [ $this, 'calculate_duration' ], 20 );
	}

	public function calculate_duration( $post_id ) {
		$post_meta             = get_post_meta( $post_id );
		$duration_value        = (int) isset( $post_meta['_EventDuration'][0] ) ? $post_meta['_EventDuration'][0] : null;
		$start_date_utc        = isset( $post_meta['_EventStartDateUTC'][0] ) ? $post_meta['_EventStartDateUTC'][0] : null;
		$end_date_utc          = isset( $post_meta['_EventEndDateUTC'][0] ) ? $post_meta['_EventEndDateUTC'][0] : null;
		$utc_timezone          = new \DateTimezone( 'UTC' );
		$start_date_utc_object = Dates::immutable( $start_date_utc, $utc_timezone );
		$end_date_utc_object   = Dates::immutable( $end_date_utc, $utc_timezone );
		$duration              = $end_date_utc_object->getTimestamp() - $start_date_utc_object->getTimestamp();

		if ( is_null( $duration_value ) || (int) $duration_value !== (int) $duration ) {
			update_post_meta( $post_id, '_EventDuration', $duration );
		}
	}

	/**
	 * Adds the editor styles required by the block editor.
	 *
	 * We trigger this action so that we only need to do the is_block_editor() check once.
	 *
	 * @since 5.12.0
	 */
	public function add_widget_resources() {
		if ( ! get_current_screen()->is_block_editor() ) {
			return;
		}

		add_action( 'admin_print_styles', [ $this, 'admin_print_widget_styles' ] );
		add_action( 'admin_print_scripts', [ $this, 'admin_print_widget_scripts' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'admin_print_footer_widget_scripts' ] );
		add_action( 'admin_footer', [ $this, 'admin_footer_widgets' ] );
	}

	/**
	 * Triggers the print styles action for widgets in the block editor.
	 *
	 * @since 5.12.0
	 */
	public function admin_print_widget_styles() {
		if ( did_action( 'admin_print_styles-widgets.php' ) ) {
			return;
		}

		do_action( 'admin_print_styles-widgets.php' );
	}

	/**
	 * Triggers the print scripts action for widgets in the block editor.
	 *
	 * @since 5.12.0
	 */
	public function admin_print_widget_scripts() {
		if ( ! did_action( 'load-widgets.php' ) ) {
			do_action( 'load-widgets.php' );
		}

		if ( ! did_action( 'widgets.php' ) ) {
			do_action( 'widgets.php' );
		}

		if ( ! did_action( 'sidebar_admin_setup' ) ) {
			do_action( 'sidebar_admin_setup' );
		}

		if ( ! did_action( 'admin_print_scripts-widgets.php' ) ) {
			do_action( 'admin_print_scripts-widgets.php' );
		}
	}

	/**
	 * Triggers the print footer scripts action for widgets in the block editor.
	 *
	 * @since 5.12.0
	 */
	public function admin_print_footer_widget_scripts() {
		if ( did_action( 'admin_print_footer_scripts-widgets.php' ) ) {
			return;
		}

		do_action( 'admin_print_footer_scripts-widgets.php' );
	}

	/**
	 * Triggers the admin footer widgets action in the block editor.
	 *
	 * @since 5.12.0
	 */
	public function admin_footer_widgets() {
		if ( did_action( 'admin_footer-widgets.php' ) ) {
			return;
		}

		do_action( 'admin_footer-widgets.php' );
	}
}
