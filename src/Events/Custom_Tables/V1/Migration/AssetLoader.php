<?php

namespace TEC\Events\Custom_Tables\V1\Migration;
use tad_DI52_ServiceProvider as Service_Provider;
use Tribe__Events__Main as TEC;

/**
 * Handles loading our main assets for the migration UI.
 */
class AssetLoader extends Service_Provider {

	/**
	 * Register our loader hooks.
	 *
	 * @since TBD
	 */
	public function register() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 10 );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
		}
	}

	/**
	 * Enqueues the scripts required by the service provider.
	 *
	 * @since TBD
	 */
	public function enqueue_scripts() {
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		if ( $_GET['page'] !== tribe( 'settings' )->adminSlug ) {
			return;
		}

		wp_enqueue_style( 'tec-ct1-upgrade-admin-css' );
		wp_enqueue_script( 'tec-ct1-upgrade-admin-js' );
		wp_localize_script( 'tec-ct1-upgrade-admin-js',
			'tecCt1Upgrade',
			[
				'ajaxUrl'      => admin_url() . 'admin-ajax.php',
				'pollInterval' => 5000,
				'actions'      => [
					'get_report'       => str_replace( 'wp_ajax_', '', Ajax::ACTION_REPORT ),
					'start_migration'  => str_replace( 'wp_ajax_', '', Ajax::ACTION_START ),
					'cancel_migration' => str_replace( 'wp_ajax_', '', Ajax::ACTION_CANCEL ),
					'undo_migration'   => str_replace( 'wp_ajax_', '', Ajax::ACTION_UNDO ),
				]
			]
		);
	}

	/**
	 * Registers the scripts required by the service provider.
	 *
	 * @since TBD
	 */
	public function register_scripts() {
		wp_register_style(
			'tec-ct1-upgrade-admin-css',
			TEC::instance()->plugin_url . 'src/resources/css/custom-tables-v1/ct1-upgrade.css'
		);
		wp_register_script(
			'tec-ct1-upgrade-admin-js',
			TEC::instance()->plugin_url . 'src/resources/js/custom-tables-v1/ct1-upgrade-remake.js'
		);

		// @todo Add in a centralized manner? String match of handle? Something else? Gotta be better way
		add_filter( 'script_loader_tag', [ $this, 'register_module_scripts' ], 10, 3 );
	}

	/**
	 * Parse the script tags that have ES6 module syntax.
	 *
	 * @since TBD
	 *
	 * @param $tag
	 * @param $handle
	 * @param $src
	 *
	 * @return string
	 */
	public function register_module_scripts( $tag, $handle, $src ) {
		if ( $handle !== 'tec-ct1-upgrade-admin-js' ) {
			return $tag;
		}

		// Add our type flag so we can use ES6 module syntax
		return str_replace( "<script ", "<script type='module' ", $tag );
	}
}