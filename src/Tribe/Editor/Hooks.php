<?php

namespace Tribe\Events\Editor;

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