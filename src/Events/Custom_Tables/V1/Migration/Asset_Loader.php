<?php
/**
 * Handles loading our main assets for the migration UI.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Admin\Progress_Modal;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report_Categories;
use Tribe__Events__Main as TEC;
use Tribe\Events\Admin\Settings as Plugin_Settings;
/**
 * Class Asset_Loader.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Asset_Loader {
	/**
	 * Whether the scripts and styles were registered with WordPress or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $did_register_scripts = false;

	/**
	 * The handle of the main JS file we want loaded as a `module`.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	private $module_handle = 'tec-ct1-upgrade-admin-js';

	/**
	 * Enqueues the scripts required by the migration front-end component and
	 * the required localized data.
	 *
	 * @since 6.0.0
	 */
	public function enqueue_scripts() {
		$on_settings_page         = tribe( Plugin_Settings::class )->is_tec_events_settings();
		$on_maintenance_mode_page = tribe( Progress_Modal::class )->should_render();


		if ( ! $on_settings_page && ! $on_maintenance_mode_page ) {
			return;
		}

		$this->register_scripts();

		$text = tribe( String_Dictionary::class );
		wp_enqueue_style( 'tec-ct1-upgrade-admin-css' );
		wp_enqueue_script( 'tec-ct1-upgrade-admin-js' );
		wp_localize_script( 'tec-ct1-upgrade-admin-js',
			'tecCt1Upgrade',
			[
				'ajaxUrl'           => admin_url() . 'admin-ajax.php',
				'nonce'             => wp_create_nonce( Ajax::NONCE_ACTION ),
				'pollInterval'      => 5000,
				'text_dictionary'   => [
					'confirm_cancel_migration'               => $text->get( 'confirm_cancel_migration' ),
					'confirm_revert_migration'               => $text->get( 'confirm_revert_migration' ),
					'migration_prompt_plugin_state_addendum' => $text->get( 'migration-prompt-plugin-state-addendum' ),
					'migration_in_progress_paragraph'        => sprintf(
						$text->get( 'migration-in-progress-paragraph' ),
						'',
						''
					)
				],
				'actions'           => [
					'paginateEvents'  => str_replace( 'wp_ajax_', '', Ajax::ACTION_PAGINATE_EVENTS ),
					'getReport'       => str_replace( 'wp_ajax_', '', Ajax::ACTION_REPORT ),
					'startMigration'  => str_replace( 'wp_ajax_', '', Ajax::ACTION_START ),
					'cancelMigration' => str_replace( 'wp_ajax_', '', Ajax::ACTION_CANCEL ),
					'revertMigration' => str_replace( 'wp_ajax_', '', Ajax::ACTION_REVERT ),
				],
				'event_categories'  => tribe( Event_Report_Categories::class )->get_categories(),
				'forcePolling'      => $on_maintenance_mode_page,
				'isMaintenanceMode' => $on_maintenance_mode_page
			]
		);
	}

	/**
	 * Registers the scripts required by the service provider.
	 *
	 * The method is idem-potent and will not register the scripts
	 * a second time if already registered.
	 *
	 * @since 6.0.0
	 */
	public function register_scripts() {
		if ( $this->did_register_scripts ) {
			return;
		}

		// @todo use asset facility here
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_style(
			'tec-ct1-upgrade-admin-css',
			TEC::instance()->plugin_url . "src/resources/css/custom-tables-v1/ct1-upgrade{$min}.css"
		);
		wp_register_script(
			'tec-ct1-upgrade-admin-js',
			TEC::instance()->plugin_url . "src/resources/js/custom-tables-v1/ct1-upgrade{$min}.js"
		);

		// We want to make sure the main JS file will be loaded as an ES6 module.
		add_filter( 'script_loader_tag', [ $this, 'register_module_scripts' ], 10, 2 );

		$this->did_register_scripts = true;
	}

	/**
	 * Filters the `script` HTML tag that will be used to print the `script`
	 * tags on the page to make sure the main asset is loaded as an ES6 module.
	 *
	 * Once the filtering is applied, this method will remove itself from the filtering.
	 *
	 * @since 6.0.0
	 *
	 * @param string $tag    The HTML that is being produced for the script.
	 * @param string $handle The handle of the script the HTML is being filtered for.
	 *
	 * @return string
	 */
	public function register_module_scripts( $tag, $handle ) {
		if ( $this->module_handle !== $handle ) {
			return $tag;
		}

		// Since the filter did date effect, we can unhook now.
		remove_filter( 'script_loader_tag', [ $this, 'register_module_scripts' ] );

		// Add our type flag so we can use ES6 module syntax
		return str_replace( "<script ", "<script type='module' ", $tag );
	}
}